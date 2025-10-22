from __future__ import annotations

import logging
from typing import Optional

from fastapi import Depends, FastAPI, File, HTTPException, UploadFile
from fastapi.responses import JSONResponse
from pydantic import BaseModel

from .analytics import BehaviourAnalyser
from .config import Settings, get_settings
from .pipeline import InvoiceProcessingPipeline

logger = logging.getLogger("pettycash_ai")
logging.basicConfig(level=logging.INFO)

app = FastAPI(title="Pettycash Smart Invoice API", version="0.1.0")
pipeline = InvoiceProcessingPipeline()
analyser = BehaviourAnalyser()


class ExtractionResponse(BaseModel):
    total_amount: Optional[float]
    currency: Optional[str]
    tax_amount: Optional[float]
    invoice_date: Optional[str]
    reference_number: Optional[str]
    payment_reference: Optional[str]
    vendor_name: Optional[str]
    customer_name: Optional[str]
    line_items: list
    analytics: dict
    confidence: float
    ocr_score: float
    raw_text: str
    segments: list
    entities: list


@app.post("/extract", response_model=ExtractionResponse)
async def extract_invoice(
    invoice: Optional[UploadFile] = File(default=None),
    receipt: Optional[UploadFile] = File(default=None),
    ledger_id: Optional[str] = None,
    metadata: Optional[str] = None,
    settings: Settings = Depends(get_settings),
):
    if not invoice and not receipt:
        raise HTTPException(status_code=400, detail="At least one file must be uploaded.")

    invoice_bytes = await invoice.read() if invoice else None
    receipt_bytes = await receipt.read() if receipt else None

    if invoice_bytes and len(invoice_bytes) > settings.max_image_bytes:
        raise HTTPException(status_code=413, detail="Invoice file is too large.")

    if receipt_bytes and len(receipt_bytes) > settings.max_image_bytes:
        raise HTTPException(status_code=413, detail="Receipt file is too large.")

    try:
        result = pipeline.process(
            invoice_bytes=invoice_bytes,
            receipt_bytes=receipt_bytes,
            metadata={
                "ledger_id": ledger_id,
                "metadata": metadata,
            },
        )
    except Exception as exc:
        logger.exception("Extraction failed.")
        raise HTTPException(status_code=500, detail=str(exc)) from exc

    return ExtractionResponse(
        total_amount=result.total_amount,
        currency=result.currency,
        tax_amount=result.tax_amount,
        invoice_date=result.invoice_date.isoformat() if result.invoice_date else None,
        reference_number=result.reference_number,
        payment_reference=result.payment_reference,
        vendor_name=result.vendor_name,
        customer_name=result.customer_name,
        line_items=[item.__dict__ for item in result.line_items],
        analytics=result.analytics,
        confidence=result.confidence,
        ocr_score=result.ocr_score,
        raw_text=result.raw_text,
        segments=[segment.__dict__ for segment in pipeline.ocr_engine.run(preprocess_image(invoice_bytes), context="invoice")] if invoice_bytes else [],
        entities=[entity.__dict__ for entity in pipeline.extractor.ner.extract(result.raw_text)],
    )


class AnalyticsRequest(BaseModel):
    records: list


@app.post("/analyze")
async def analyze(records: AnalyticsRequest):
    try:
        payload = analyser.summarise(records.records)
    except Exception as exc:
        logger.exception("Analytics computation failed.")
        raise HTTPException(status_code=500, detail=str(exc)) from exc

    return payload
