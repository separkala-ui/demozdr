"""
Pettycash AI toolkit.

This package powers the smart invoice automation pipeline that
extracts and analyses key fields from Persian petty-cash documents.
"""

from .config import settings  # noqa: F401
from .pipeline import InvoiceProcessingPipeline  # noqa: F401
