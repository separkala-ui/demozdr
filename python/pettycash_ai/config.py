from __future__ import annotations

import os
from functools import lru_cache
from typing import List, Optional

from pydantic import Field, field_validator
from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    """Configuration surface for the smart invoice pipeline."""

    service_name: str = "pettycash-smart-invoice"

    # OCR
    easyocr_languages: List[str] = Field(default_factory=lambda: ["fa", "en"])
    easyocr_gpu: bool = Field(default=False)
    external_ocr_endpoint: Optional[str] = Field(default=None, env="SMART_INVOICE_OCR_ENDPOINT")
    external_ocr_api_key: Optional[str] = Field(default=None, env="SMART_INVOICE_OCR_KEY")
    ocr_timeout: int = Field(default=45)

    # NER / NLP
    ner_model_path: Optional[str] = Field(default=None, env="SMART_INVOICE_NER_MODEL")
    ner_confidence_threshold: float = Field(default=0.45)

    # Analytics
    analytics_enabled: bool = Field(default=True, env="SMART_INVOICE_ANALYTICS")
    analytics_history_days: int = Field(default=90)

    # General
    max_image_size_mb: float = Field(default=8.0)
    log_level: str = Field(default="INFO")

    class Config:
        env_prefix = "SMART_INVOICE_"
        case_sensitive = False

    @field_validator("easyocr_languages", mode="before")
    @classmethod
    def parse_languages(cls, value):
        if isinstance(value, str):
            return [item.strip() for item in value.split(",") if item.strip()]
        return value

    @property
    def max_image_bytes(self) -> int:
        return int(self.max_image_size_mb * 1024 * 1024)

    @property
    def use_external_ocr(self) -> bool:
        return bool(self.external_ocr_endpoint)


@lru_cache()
def get_settings() -> Settings:
    """Cache settings to avoid re-parsing env vars."""
    return Settings(_env_file=os.getenv("SMART_INVOICE_ENV_FILE", None))


settings = get_settings()
