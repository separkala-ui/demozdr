from __future__ import annotations

import logging
from dataclasses import dataclass
from typing import Dict, Iterable, List, Optional

from .config import settings

logger = logging.getLogger(__name__)

try:
    from transformers import AutoModelForTokenClassification, AutoTokenizer, pipeline
except Exception:  # pragma: no cover - optional dependency
    AutoModelForTokenClassification = None
    AutoTokenizer = None
    pipeline = None


@dataclass
class Entity:
    text: str
    label: str
    score: float


class PersianInvoiceNer:
    """Named entity extractor for Persian invoice fields."""

    def __init__(self):
        self._pipeline = None

    def _load_pipeline(self):
        if pipeline is None or AutoModelForTokenClassification is None:
            logger.warning("transformers is not installed; falling back to rules.")
            return None

        if self._pipeline:
            return self._pipeline

        model_name = settings.ner_model_path or "HooshvareLab/bert-base-parsbert-uncased"
        logger.info("Loading NER model: %s", model_name)
        tokenizer = AutoTokenizer.from_pretrained(model_name)
        model = AutoModelForTokenClassification.from_pretrained(model_name)
        self._pipeline = pipeline("token-classification", model=model, tokenizer=tokenizer, aggregation_strategy="simple")
        return self._pipeline

    def extract(self, text: str) -> List[Entity]:
        if not text.strip():
            return []

        nlp = self._load_pipeline()

        if not nlp:
            return self._fallback_rules(text)

        results = nlp(text)
        entities = [
            Entity(text=item["word"], label=item["entity_group"], score=float(item["score"]))
            for item in results
            if float(item["score"]) >= settings.ner_confidence_threshold
        ]

        if not entities:
            return self._fallback_rules(text)

        return entities

    def _fallback_rules(self, text: str) -> List[Entity]:
        """Simple rule-based extraction for environments without transformers."""
        heuristics = {
            "TOTAL": ["جمع کل", "مبلغ کل", "مبلغ پرداختی"],
            "DATE": ["تاریخ", "زمان"],
            "VENDOR": ["فروشنده", "نام فروشگاه", "صادرکننده"],
            "CUSTOMER": ["خریدار", "مشتری"],
            "REFERENCE": ["سریال", "شماره پیگیری", "شماره فاکتور", "رسید"],
        }

        entities: List[Entity] = []
        lowered = text.replace("ي", "ی").replace("ك", "ک")

        for label, keywords in heuristics.items():
            for keyword in keywords:
                if keyword in lowered:
                    entities.append(Entity(text=keyword, label=label, score=0.5))

        return entities
