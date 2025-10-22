from __future__ import annotations

import logging
import subprocess
import sys
from dataclasses import dataclass
from typing import List, Optional, Sequence, Tuple

import numpy as np
import requests

from .config import settings
from .preprocessing import to_png_bytes

logger = logging.getLogger(__name__)

try:
    import easyocr
except Exception:  # pragma: no cover - optional dependency
    easyocr = None


@dataclass
class TextSegment:
    text: str
    confidence: float
    box: Optional[Sequence[Tuple[float, float]]] = None


class InvoiceOcrEngine:
    """Wrapper around EasyOCR with optional external HTTP fallback."""

    def __init__(self):
        self._reader: Optional["easyocr.Reader"] = None
        self._easyocr_ready = False

    def _ensure_easyocr(self):
        global easyocr

        if easyocr is not None:
            self._easyocr_ready = True
            return

        logger.warning("EasyOCR not detected. Attempting automatic installation...")
        try:
            subprocess.check_call(
                [
                    sys.executable,
                    "-m",
                    "pip",
                    "install",
                    "easyocr",
                    "opencv-python",
                ],
                stdout=subprocess.DEVNULL,
                stderr=subprocess.DEVNULL,
            )
        except Exception as exc:  # pragma: no cover - best effort
            logger.error("Automatic EasyOCR installation failed: %s", exc)
            raise RuntimeError(
                "EasyOCR is not installed. Please pip install easyocr or configure SMART_INVOICE_OCR_ENDPOINT."
            ) from exc

        try:
            import easyocr as easyocr_module  # type: ignore
        except Exception as exc:  # pragma: no cover - best effort
            logger.error("EasyOCR import still failing after installation: %s", exc)
            raise RuntimeError(
                "EasyOCR is not installed. Please pip install easyocr or configure SMART_INVOICE_OCR_ENDPOINT."
            ) from exc

        easyocr = easyocr_module
        self._easyocr_ready = True

    @property
    def reader(self):
        if self._reader is not None or easyocr is None:
            return self._reader

        logger.info("Initialising EasyOCR reader with languages %s", settings.easyocr_languages)
        self._reader = easyocr.Reader(settings.easyocr_languages, gpu=settings.easyocr_gpu)
        return self._reader

    def run(self, image: np.ndarray, context: Optional[str] = None) -> List[TextSegment]:
        if settings.use_external_ocr:
            return self._run_external(image, context)

        if easyocr is None:
            self._ensure_easyocr()

        if self.reader is None:
            raise RuntimeError("EasyOCR is not installed. Please `pip install easyocr` or configure SMART_INVOICE_OCR_ENDPOINT.")

        results = self.reader.readtext(image, detail=1, paragraph=True)
        segments = []
        for entry in results:
            text = entry[1].strip()
            if not text:
                continue

            confidence = float(entry[2]) if entry[2] is not None else 0.0
            segments.append(TextSegment(text=text, confidence=confidence, box=entry[0]))
        logger.debug("OCR extracted %d segments via EasyOCR", len(segments))
        return segments

    def _run_external(self, image: np.ndarray, context: Optional[str]) -> List[TextSegment]:
        payload = {"context": context or "invoice"}
        headers = {"Accept": "application/json"}

        if settings.external_ocr_api_key:
            headers["Authorization"] = f"Bearer {settings.external_ocr_api_key}"

        files = {"file": ("document.png", to_png_bytes(image), "image/png")}

        response = requests.post(
            settings.external_ocr_endpoint,
            data=payload,
            files=files,
            timeout=settings.ocr_timeout,
        )
        response.raise_for_status()
        data = response.json()

        segments = []
        for segment in data.get("segments", []):
            text = (segment.get("text") or "").strip()
            if not text:
                continue
            confidence = float(segment.get("confidence", 0.0) or 0.0)
            segments.append(
                TextSegment(
                    text=text,
                    confidence=confidence,
                    box=segment.get("box"),
                )
            )
        logger.debug("OCR extracted %d segments via external endpoint", len(segments))
        return segments
