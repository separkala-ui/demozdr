from __future__ import annotations

import logging
from typing import Dict, Optional, Sequence

from .config import settings
from .extraction import ExtractionResult, StructuredExtractor
from .ocr import InvoiceOcrEngine, TextSegment
from .preprocessing import preprocess_image

logger = logging.getLogger(__name__)


class InvoiceProcessingPipeline:
    """End-to-end orchestration of preprocessing → OCR → extraction."""

    def __init__(self):
        self.ocr_engine = InvoiceOcrEngine()
        self.extractor = StructuredExtractor()

    def process(
        self,
        invoice_bytes: Optional[bytes],
        receipt_bytes: Optional[bytes] = None,
        metadata: Optional[Dict] = None,
    ) -> ExtractionResult:
        if not invoice_bytes and not receipt_bytes:
            raise ValueError("At least one document must be provided.")

        segments: list[TextSegment] = []

        if invoice_bytes:
            logger.info("Running invoice OCR pipeline.")
            invoice_image = preprocess_image(invoice_bytes)
            segments.extend(self.ocr_engine.run(invoice_image, context="invoice"))

        if receipt_bytes:
            logger.info("Running receipt OCR pipeline.")
            receipt_image = preprocess_image(receipt_bytes)
            segments.extend(self.ocr_engine.run(receipt_image, context="receipt"))

        result = self.extractor.parse(segments, context=metadata)
        logger.info("Extraction finished with confidence %.2f", result.confidence)
        return result
