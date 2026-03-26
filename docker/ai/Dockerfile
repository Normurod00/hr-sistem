FROM python:3.11-slim

# System deps for PDF processing and OCR
RUN apt-get update && apt-get install -y --no-install-recommends \
    tesseract-ocr tesseract-ocr-rus tesseract-ocr-uzb \
    poppler-utils libmagic1 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY ai_server/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY ai_server/ .

RUN mkdir -p temp logs

EXPOSE 8095

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8095", "--workers", "2"]
