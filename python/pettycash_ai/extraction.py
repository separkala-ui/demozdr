from __future__ import annotations

import logging
import re
from dataclasses import dataclass, field
from datetime import datetime
from typing import Dict, List, Optional, Sequence

import pandas as pd

from .ner import Entity, PersianInvoiceNer
from .ocr import TextSegment

logger = logging.getLogger(__name__)


CURRENCY_PATTERN = re.compile(r"(?P<amount>\d[\d,\.]*)\s*(?:ریال|تومان|Rial|IRR)", re.IGNORECASE)
DATE_PATTERN = re.compile(r"(?P<date>\d{2,4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2})")
REFERENCE_PATTERN = re.compile(r"(?:شماره|رسید|پیگیری|فاکتور)\s*(?:[:：\-])?\s*(?P<ref>[0-9]{4,})")
POS_PATTERN = re.compile(r"(?:POS|پوز|پايانه)\s*(?:[:：\-])?\s*(?P<pos>[0-9]{6,})", re.IGNORECASE)

TOTAL_KEYWORDS = [
    "جمع کل",
    "مبلغ کل",
    "مبلغ قابل پرداخت",
    "جمع پرداختی",
    "Total",
    "Payable",
]

CATEGORY_KEYWORDS = {
    "produce": ["تره بار", "میوه", "سبزی", "صیفی"],
    "protein": ["گوشت", "مرغ", "ماهی", "تخم مرغ", "پروتئین"],
    "transport": ["حمل", "راننده", "باربری", "کرایه", "سوخت"],
    "office": ["لوازم التحریر", "کاغذ", "پرینتر", "خودکار"],
    "utilities": ["آب", "برق", "گاز", "اینترنت", "شارژ", "قبض"],
}

PERSIAN_MONTHS = {
    "فروردین": 1,
    "اردیبهشت": 2,
    "خرداد": 3,
    "تیر": 4,
    "مرداد": 5,
    "شهریور": 6,
    "مهر": 7,
    "آبان": 8,
    "آذر": 9,
    "دی": 10,
    "بهمن": 11,
    "اسفند": 12,
}


@dataclass
class LineItem:
    description: str
    quantity: Optional[float] = None
    unit_price: Optional[float] = None
    total_price: Optional[float] = None
    category: Optional[str] = None


@dataclass
class ExtractionResult:
    total_amount: Optional[float] = None
    currency: Optional[str] = "IRR"
    tax_amount: Optional[float] = None
    invoice_date: Optional[datetime] = None
    reference_number: Optional[str] = None
    payment_reference: Optional[str] = None
    vendor_name: Optional[str] = None
    customer_name: Optional[str] = None
    line_items: List[LineItem] = field(default_factory=list)
    raw_text: str = ""
    analytics: Dict[str, object] = field(default_factory=dict)
    confidence: float = 0.0
    ocr_score: float = 0.0


class StructuredExtractor:
    def __init__(self):
        self.ner = PersianInvoiceNer()
        self._digit_translation = str.maketrans({
            "۰": "0",
            "۱": "1",
            "۲": "2",
            "۳": "3",
            "۴": "4",
            "۵": "5",
            "۶": "6",
            "۷": "7",
            "۸": "8",
            "۹": "9",
            "٠": "0",
            "١": "1",
            "٢": "2",
            "٣": "3",
            "٤": "4",
            "٥": "5",
            "٦": "6",
            "٧": "7",
            "٨": "8",
            "٩": "9",
        })
        self._punct_translation = str.maketrans({
            "٬": ",",
            "٫": ".",
            "،": ",",
        })

    def parse(self, text_segments: Sequence[TextSegment], context: Optional[Dict] = None) -> ExtractionResult:
        text_blob = "\n".join(seg.text for seg in text_segments if getattr(seg, "text", None) and seg.text.strip())
        entities = self.ner.extract(text_blob)
        result = ExtractionResult(raw_text=text_blob)

        result.ocr_score = self._aggregate_ocr_confidence(text_segments)

        result.total_amount = self._extract_total(text_blob)
        result.invoice_date = self._extract_date(text_blob)
        result.reference_number = self._extract_reference(text_blob)
        result.payment_reference = self._extract_pos(text_blob)
        result.line_items = self._extract_items(text_segments)
        result.analytics = self._build_analytics(result)
        result.confidence = self._estimate_confidence(result, entities, result.ocr_score)

        for entity in entities:
            if entity.label in ("VENDOR", "ORG") and not result.vendor_name:
                result.vendor_name = entity.text
            elif entity.label in ("CUSTOMER", "PERSON") and not result.customer_name:
                result.customer_name = entity.text
            elif entity.label in ("REFERENCE",) and not result.reference_number:
                result.reference_number = entity.text

        if context and context.get("existing_amount") and not result.total_amount:
            fallback = self._safe_float(self._normalize_amount_text(str(context["existing_amount"])))
            if fallback:
                result.total_amount = fallback

        return result

    def _extract_total(self, text: str) -> Optional[float]:
        normalized = self._normalize_amount_text(text)
        stripped = normalized.replace(",", "")
        matches = CURRENCY_PATTERN.findall(stripped)
        if not matches:
            keyword_pattern = re.compile(
                rf"(?:{'|'.join(map(re.escape, TOTAL_KEYWORDS))})\s*(?:[:：\-])?\s*(?P<amount>\d[\d,\.]*)",
                re.IGNORECASE,
            )
            keyword_matches = keyword_pattern.findall(normalized)
            if keyword_matches:
                matches = keyword_matches
            else:
                number_fallback = re.findall(r"(?<!\d)(\d{4,})(?!\d)", stripped)
                if not number_fallback:
                    return None
                matches = number_fallback

        candidates = [self._safe_float(amount) for amount in matches]
        candidates = [c for c in candidates if c is not None]

        if not candidates:
            return None

        best = max(candidates)
        logger.debug("Detected total amount candidate: %s", best)
        return best

    def _extract_date(self, text: str) -> Optional[datetime]:
        normalized = self._normalize_digits(text)
        match = DATE_PATTERN.search(normalized)
        if match:
            raw = match.group("date").replace(" ", "")
            for fmt in ("%Y/%m/%d", "%Y-%m-%d", "%Y.%m.%d", "%y/%m/%d", "%Y%m%d"):
                try:
                    return datetime.strptime(raw, fmt)
                except ValueError:
                    continue

        fuzzy = self._extract_fuzzy_month_date(normalized)
        if fuzzy:
            return fuzzy

        return None

    def _extract_reference(self, text: str) -> Optional[str]:
        match = REFERENCE_PATTERN.search(text)
        return match.group("ref") if match else None

    def _extract_pos(self, text: str) -> Optional[str]:
        match = POS_PATTERN.search(text)
        return match.group("pos") if match else None

    def _extract_items(self, segments: Sequence[TextSegment]) -> List[LineItem]:
        items: List[LineItem] = []
        for segment in segments:
            text = getattr(segment, "text", None)
            if not text:
                continue

            cleaned = text.strip()
            if not cleaned or len(cleaned) < 3:
                continue

            category = self._categorise(cleaned)
            amount = self._extract_inline_total(cleaned)

            if category or amount:
                items.append(LineItem(description=cleaned, total_price=amount, category=category))

        return items

    def _extract_inline_total(self, text: str) -> Optional[float]:
        normalized = self._normalize_amount_text(text)
        stripped = normalized.replace(",", "")
        match = CURRENCY_PATTERN.search(stripped)
        if not match:
            number_fallback = re.search(r"(?<!\d)(\d{4,})(?!\d)", stripped)
            if not number_fallback:
                return None
            return self._safe_float(number_fallback.group(1))
        return self._safe_float(match.group("amount"))

    def _categorise(self, text: str) -> Optional[str]:
        for category, keywords in CATEGORY_KEYWORDS.items():
            if any(keyword in text for keyword in keywords):
                return category
        return None

    def _build_analytics(self, result: ExtractionResult) -> Dict[str, object]:
        if not result.line_items:
            return {}

        df = pd.DataFrame(
            [
                {
                    "category": item.category or "uncategorised",
                    "total": item.total_price or 0.0,
                }
                for item in result.line_items
            ]
        )
        grouped = df.groupby("category")["total"].sum().sort_values(ascending=False)
        return {
            "top_category": grouped.index[0] if not grouped.empty else None,
            "category_totals": grouped.to_dict(),
            "line_item_count": len(result.line_items),
        }

    def _estimate_confidence(self, result: ExtractionResult, entities: Sequence[Entity], ocr_score: float) -> float:
        score = 0.0
        if result.total_amount:
            score += 0.4
        if result.invoice_date:
            score += 0.2
        if result.reference_number:
            score += 0.2
        if result.vendor_name or result.customer_name:
            score += 0.1
        if result.line_items:
            score += 0.1

        if ocr_score:
            score += min(0.3, ocr_score * 0.3)

        # Average NER scores
        if entities:
            score = min(1.0, score + sum(e.score for e in entities) / (10 * len(entities)))

        return round(score, 3)

    def _aggregate_ocr_confidence(self, segments: Sequence[TextSegment]) -> float:
        confidences = [seg.confidence for seg in segments if getattr(seg, "confidence", None)]
        if not confidences:
            return 0.0
        return round(sum(confidences) / len(confidences), 3)

    def _normalize_digits(self, text: str) -> str:
        return text.translate(self._digit_translation)

    def _normalize_amount_text(self, text: str) -> str:
        normalized = self._normalize_digits(text)
        return normalized.translate(self._punct_translation)

    def _safe_float(self, value: Optional[str]) -> Optional[float]:
        try:
            if value is None:
                return None
            return float(str(value).replace(",", "").replace("٬", "").replace("٫", "."))
        except Exception:
            return None

    def _extract_fuzzy_month_date(self, text: str) -> Optional[datetime]:
        # نمونه: "27 مهر 1404 - 10:54:56"
        month_pattern = re.compile(
            r"(?P<day>\d{1,2})\s+(?P<month>" + "|".join(map(re.escape, PERSIAN_MONTHS.keys())) + r")\s+(?P<year>\d{4})(?:\s*[-–]?\s*(?P<time>\d{1,2}:\d{2}(?::\d{2})?))?",
            re.IGNORECASE,
        )
        match = month_pattern.search(text)
        if not match:
            return None

        day = int(match.group("day"))
        year = int(match.group("year"))
        month_name = match.group("month")
        month = PERSIAN_MONTHS.get(month_name, 0)
        if not month:
            return None

        time_part = match.group("time") or "00:00:00"
        if len(time_part.split(":")) == 2:
            time_part = f"{time_part}:00"

        try:
            return datetime.strptime(f"{year}-{month:02d}-{day:02d} {time_part}", "%Y-%m-%d %H:%M:%S")
        except ValueError:
            return None
