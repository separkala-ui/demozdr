# Smart Invoice Intelligence Architecture

This document captures the high–level architecture for the petty-cash "smart invoice" automation stack.  
Its goal is to make it easy to reason about how images captured inside the Laravel dashboard flow into the Python pipeline, how extracted fields are sent back, and how the data can later power analytics.

## 1. Functional Overview

- **Trigger point:** Inside the petty–cash transaction form each row has a _Smart Fill_ button. A user uploads an invoice image (and optionally the POS receipt) and then presses the button.
- **Livewire action:** The Livewire component streams the temporary upload paths to a dedicated `SmartInvoiceService`.
- **Python service call:** The PHP service posts a multipart request to the locally hosted Python microservice. The request contains the invoice image, receipt image, contextual metadata, and the user/ledger identifiers.
- **Extraction pipeline:** The Python API performs:
  1. OpenCV pre-processing (deskew, denoise, normalization).
  2. OCR (EasyOCR by default). External OCR APIs remain optional and are disabled unless explicitly configured.
  3. Structured field extraction (NER-based entity tagging + fallback regex heuristics).
  4. Category inference and behaviour analytics (Pandas + lightweight keyword classifier).
- **Response:** The Python service returns the extracted totals, dates, reference numbers, vendor/customer names, detected line items, analytics snippets, and a confidence score.
- **Form auto-fill:** Livewire updates the inline row fields, stores the structured payload in `entries[n][meta]`, and shows the confidence/status to the user. Submitting the form persists the transaction as usual with the enriched metadata.

## 2. Deployment Model

```
[Laravel App] --HTTP--> [FastAPI microservice] --(optional GPU/External OCR)-->
```

- **Runtime:** The Python service runs on your own infrastructure (VM, bare metal or container). Start it locally with `uvicorn` or bundle it inside your existing docker-compose stack.
- **Config:** All endpoints and secrets are controlled via `.env` (`SMART_INVOICE_SERVICE_URL`, optional API key, timeout, analytics toggle). PHP falls back gracefully if the service is undefined or unreachable.

## 3. Python Service Composition

| Module | Responsibility | Notes |
| --- | --- | --- |
| `config.py` | Centralises environment configuration via Pydantic settings | Supports toggling GPU, choosing OCR provider, thresholds |
| `preprocessing.py` | OpenCV helpers for noise removal, thresholding, rotation correction | Fallback to PIL if OpenCV is unavailable |
| `ocr.py` | Wraps EasyOCR or an HTTP-based OCR provider, returns text segments with bounding boxes | Lazily instantiates the OCR reader |
| `ner.py` | Token classification pipeline (Transformers / spaCy) for Persian invoices | Loads model path from env; degrades to rule-based tagging |
| `extraction.py` | Combines OCR text + NER entities to produce structured invoice fields | Includes regex fallbacks for total, tax, dates, POS IDs |
| `analytics.py` | Builds Pandas DataFrames, category spending summaries, behavioural insights | Provides data points for future dashboard widgets |
| `main.py` | FastAPI app exposing `/extract` and `/analyze` endpoints | Handles multipart uploads and schema validation |

All modules are pure Python and covered by type hints so they can be unit-tested independently.

## 4. Error Handling Strategy

- Network errors, OCR failures, and parsing issues surface to PHP as `SmartInvoiceException` with user-friendly Persian messages.
- The Python API returns structured error payloads (`status="error"`, `detail`) with HTTP 4xx/5xx codes.
- Livewire displays inline messages on the offending row while leaving manually entered values untouched.

## 5. Data Persistence & Analytics

- Extracted metadata is stored inside the `meta` JSON column of `petty_cash_transactions` under the `smart_invoice` key.
- Each payload includes raw OCR text for auditing, normalised totals, detected items, and analytics snapshots (top category, spend bucket).
- Future dashboards can aggregate the JSON using the existing services or a new scheduled job that exports to a warehouse.

## 6. Security & Privacy

- Large images never leave your infrastructure because EasyOCR runs locally. External OCR providers remain opt-in only; if enabled, HTTPS is enforced and secrets are loaded from env/settings.
- API key authentication between Laravel and Python is optional but recommended in production.
- Uploaded images remain inside Laravel's temporary storage; the Python service reads them via streaming and never persists files unless configured.

## 7. Next Steps

1. Implement the FastAPI server and ship it with a Dockerfile.
2. Add automated tests around extraction heuristics and the PHP service wrapper.
3. Instrument analytics outputs with logging/monitoring to keep track of confidence degradation over time.

## 8. On-Prem Deployment Checklist

- Install Python 3.10+ and system packages required by OpenCV (`ffmpeg`, `libsm6`, `libxext6` on Debian/Ubuntu).
- Create a dedicated virtualenv and install dependencies:  
  `pip install -r python/pettycash_ai/requirements.txt`
- Run the service behind your firewall:  
  `uvicorn python.pettycash_ai.main:app --host 0.0.0.0 --port 8000`
- Set the **Smart Invoice** settings (URL, timeout, optional token) from the admin panel so Laravel can communicate with the service.
