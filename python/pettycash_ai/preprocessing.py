from __future__ import annotations

import io
import logging
from typing import Optional

import numpy as np
from PIL import Image, ImageOps

try:
    import cv2
except Exception:  # pragma: no cover - optional dependency
    cv2 = None

logger = logging.getLogger(__name__)


def load_image_to_array(raw: bytes, grayscale: bool = False) -> np.ndarray:
    """Convert raw bytes into a numpy array suitable for OpenCV routines."""
    image = Image.open(io.BytesIO(raw))

    if grayscale:
        image = ImageOps.grayscale(image)

    return np.array(image)


def enhance_contrast(image: np.ndarray) -> np.ndarray:
    """Apply CLAHE to improve readability."""
    if cv2 is None:
        return image

    if len(image.shape) == 2:
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        return clahe.apply(image)

    lab = cv2.cvtColor(image, cv2.COLOR_RGB2LAB)
    l, a, b = cv2.split(lab)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    cl = clahe.apply(l)
    limg = cv2.merge((cl, a, b))
    return cv2.cvtColor(limg, cv2.COLOR_LAB2RGB)


def denoise(image: np.ndarray) -> np.ndarray:
    """Use bilateral filtering to remove noise while keeping edges."""
    if cv2 is None:
        return image

    if len(image.shape) == 2:
        return cv2.fastNlMeansDenoising(image, None, h=9, templateWindowSize=7, searchWindowSize=21)

    return cv2.bilateralFilter(image, d=9, sigmaColor=75, sigmaSpace=75)


def deskew(image: np.ndarray) -> np.ndarray:
    """Attempt to correct skew using OpenCV moments."""
    if cv2 is None:
        return image

    gray = cv2.cvtColor(image, cv2.COLOR_RGB2GRAY) if len(image.shape) == 3 else image
    gray = cv2.bitwise_not(gray)
    thresh = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY | cv2.THRESH_OTSU)[1]
    coords = np.column_stack(np.where(thresh > 0))

    if coords.size == 0:
        return image

    angle = cv2.minAreaRect(coords)[-1]

    if angle < -45:
        angle = -(90 + angle)
    else:
        angle = -angle

    (h, w) = image.shape[:2]
    center = (w // 2, h // 2)
    matrix = cv2.getRotationMatrix2D(center, angle, 1.0)
    rotated = cv2.warpAffine(image, matrix, (w, h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REPLICATE)
    logger.debug("Deskew applied with angle %.2f degrees", angle)
    return rotated


def preprocess_image(raw: bytes, *, grayscale: bool = True) -> np.ndarray:
    """Full preprocessing chain: load → grayscale → denoise → deskew → enhance."""
    image = load_image_to_array(raw, grayscale=grayscale)
    image = denoise(image)
    image = deskew(image)
    image = enhance_contrast(image)
    return image


def to_png_bytes(image: np.ndarray) -> bytes:
    """Utility to convert numpy array back to PNG bytes."""
    pil_image = Image.fromarray(image)
    buffer = io.BytesIO()
    pil_image.save(buffer, format="PNG")
    return buffer.getvalue()
