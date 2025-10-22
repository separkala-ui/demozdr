# gemini_code.py

import os
from google import genai

# SDK به طور خودکار کلید را از متغیر محیطی GEMINI_API_KEY می‌خواند
try:
    client = genai.Client()
except Exception as e:
    print(f"خطا در اتصال به مشتری: مطمئن شوید GEMINI_API_KEY تنظیم شده است. خطا: {e}")
    exit()

def generate_code_with_gemini(prompt: str):
    """تابعی برای ارسال درخواست به Gemini و دریافت پاسخ."""
    print(f"ارسال درخواست به Gemini با: {prompt}")

    try:
        # استفاده از مدل مناسب برای تولید کد
        response = client.models.generate_content(
            model='gemini-2.5-flash', # یک مدل قدرتمند و سریع
            contents=f"کد پایتون برای این درخواست تولید کن و فقط خود کد را برگردان: {prompt}"
        )
        
        print("\n--- پاسخ Gemini ---")
        print(response.text)
        print("-------------------\n")

    except Exception as e:
        print(f"خطا در تولید محتوا: {e}")

# مثال استفاده
prompt_request = "تابعی برای محاسبه فاکتوریل یک عدد به روش بازگشتی بنویس."
generate_code_with_gemini(prompt_request)
