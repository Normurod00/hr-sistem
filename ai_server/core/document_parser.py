"""
Document Parser for HR AI Server
Извлечение текста из PDF, DOCX, TXT файлов
"""

import os
import io
import base64
import logging
import tempfile
from typing import Optional, Tuple
from pathlib import Path

logger = logging.getLogger(__name__)


class DocumentParser:
    """
    Парсер документов
    Поддерживает: PDF, DOCX, DOC, TXT, RTF, JPG, PNG, JPEG, BMP, TIFF
    """

    SUPPORTED_EXTENSIONS = {'.pdf', '.docx', '.doc', '.txt', '.rtf', '.jpg', '.jpeg', '.png', '.bmp', '.tiff', '.tif'}

    def __init__(self, config: dict = None):
        self.config = config or {}
        self.max_file_size = self.config.get('max_file_size_mb', 10) * 1024 * 1024
        self.temp_dir = self.config.get('temp_dir', 'temp')

        # Создаём temp директорию
        os.makedirs(self.temp_dir, exist_ok=True)

    def parse_file(self, file_path: str) -> Tuple[str, Optional[str]]:
        """
        Извлечение текста из файла

        Args:
            file_path: Путь к файлу

        Returns:
            Tuple[text, error]: Извлечённый текст и ошибка (если есть)
        """
        path = Path(file_path)

        if not path.exists():
            return "", f"Файл не найден: {file_path}"

        if path.stat().st_size > self.max_file_size:
            return "", f"Файл слишком большой (макс. {self.max_file_size // 1024 // 1024} MB)"

        ext = path.suffix.lower()

        if ext not in self.SUPPORTED_EXTENSIONS:
            return "", f"Неподдерживаемый формат файла: {ext}"

        try:
            if ext == '.pdf':
                return self._parse_pdf(file_path), None
            elif ext in {'.docx', '.doc'}:
                return self._parse_docx(file_path), None
            elif ext == '.txt':
                return self._parse_txt(file_path), None
            elif ext == '.rtf':
                return self._parse_rtf(file_path), None
            elif ext in {'.jpg', '.jpeg', '.png', '.bmp', '.tiff', '.tif'}:
                return self._parse_image(file_path), None
            else:
                return "", f"Парсер для {ext} не реализован"

        except Exception as e:
            logger.error(f"Error parsing {file_path}: {e}")
            return "", str(e)

    def parse_base64(self, content: str, filename: str) -> Tuple[str, Optional[str]]:
        """
        Парсинг файла из base64

        Args:
            content: Base64 encoded content
            filename: Имя файла (для определения формата)

        Returns:
            Tuple[text, error]
        """
        try:
            # Декодируем base64
            file_data = base64.b64decode(content)

            # Сохраняем во временный файл
            ext = Path(filename).suffix.lower()
            temp_path = os.path.join(self.temp_dir, f"temp_{os.getpid()}{ext}")

            with open(temp_path, 'wb') as f:
                f.write(file_data)

            try:
                return self.parse_file(temp_path)
            finally:
                # Удаляем временный файл
                if os.path.exists(temp_path):
                    os.remove(temp_path)

        except Exception as e:
            logger.error(f"Error parsing base64: {e}")
            return "", str(e)

    def parse_bytes(self, content: bytes, filename: str) -> Tuple[str, Optional[str]]:
        """
        Парсинг файла из bytes

        Args:
            content: Байты файла
            filename: Имя файла

        Returns:
            Tuple[text, error]
        """
        try:
            ext = Path(filename).suffix.lower()
            temp_path = os.path.join(self.temp_dir, f"temp_{os.getpid()}{ext}")

            with open(temp_path, 'wb') as f:
                f.write(content)

            try:
                return self.parse_file(temp_path)
            finally:
                if os.path.exists(temp_path):
                    os.remove(temp_path)

        except Exception as e:
            logger.error(f"Error parsing bytes: {e}")
            return "", str(e)

    def _parse_pdf(self, file_path: str) -> str:
        """Извлечение текста из PDF с OCR fallback для сканированных документов"""
        text_parts = []

        # Пробуем pdfplumber (лучше для сложных PDF)
        try:
            import pdfplumber
            with pdfplumber.open(file_path) as pdf:
                for page in pdf.pages:
                    page_text = page.extract_text()
                    if page_text:
                        text_parts.append(page_text)

            if text_parts:
                extracted = '\n\n'.join(text_parts)
                # Проверяем, достаточно ли текста извлечено
                if len(extracted.strip()) >= 100:
                    return extracted
                else:
                    logger.info("PDF содержит мало текста, пробуем OCR...")

        except ImportError:
            logger.warning("pdfplumber not installed, trying PyPDF2")
        except Exception as e:
            logger.warning(f"pdfplumber failed: {e}, trying PyPDF2")

        # Fallback на PyPDF2
        if not text_parts:
            try:
                from PyPDF2 import PdfReader
                reader = PdfReader(file_path)
                for page in reader.pages:
                    text = page.extract_text()
                    if text:
                        text_parts.append(text)

                if text_parts:
                    extracted = '\n\n'.join(text_parts)
                    if len(extracted.strip()) >= 100:
                        return extracted

            except ImportError:
                logger.warning("PyPDF2 not installed")
            except Exception as e:
                logger.warning(f"PyPDF2 failed: {e}")

        # OCR fallback для сканированных PDF
        ocr_text = self._ocr_pdf(file_path)
        if ocr_text:
            return ocr_text

        # Если ничего не помогло, возвращаем что есть
        return '\n\n'.join(text_parts) if text_parts else ""

    def _ocr_pdf(self, file_path: str) -> str:
        """OCR для сканированных PDF"""
        try:
            from pdf2image import convert_from_path
            import pytesseract

            logger.info(f"Запуск OCR для PDF: {file_path}")

            # Конвертируем PDF в изображения
            images = convert_from_path(file_path, dpi=300)

            text_parts = []
            for i, image in enumerate(images):
                # OCR с поддержкой русского и английского
                text = pytesseract.image_to_string(
                    image,
                    lang='rus+eng',
                    config='--psm 1 --oem 3'
                )
                if text.strip():
                    text_parts.append(text)
                logger.debug(f"OCR страница {i+1}: {len(text)} символов")

            if text_parts:
                logger.info(f"OCR успешно: извлечено {sum(len(t) for t in text_parts)} символов")
                return '\n\n'.join(text_parts)

        except ImportError as e:
            logger.warning(f"OCR недоступен (установите pdf2image и pytesseract): {e}")
        except Exception as e:
            logger.error(f"OCR ошибка: {e}")

        return ""

    def _parse_image(self, file_path: str) -> str:
        """OCR для изображений (JPG, PNG, BMP, TIFF)"""
        try:
            import pytesseract
            from PIL import Image

            logger.info(f"Запуск OCR для изображения: {file_path}")

            # Открываем изображение
            image = Image.open(file_path)

            # Предобработка для улучшения OCR
            image = self._preprocess_image(image)

            # OCR с поддержкой русского и английского
            text = pytesseract.image_to_string(
                image,
                lang='rus+eng',
                config='--psm 1 --oem 3'
            )

            if text.strip():
                logger.info(f"OCR успешно: извлечено {len(text)} символов")
                return text.strip()

            return ""

        except ImportError as e:
            logger.error(f"pytesseract не установлен: {e}")
            raise ImportError("Установите pytesseract и Pillow: pip install pytesseract Pillow")
        except Exception as e:
            logger.error(f"Ошибка OCR изображения: {e}")
            raise

    def _preprocess_image(self, image):
        """Предобработка изображения для улучшения OCR"""
        try:
            from PIL import ImageEnhance, ImageFilter

            # Конвертируем в RGB если нужно
            if image.mode != 'RGB':
                image = image.convert('RGB')

            # Увеличиваем контраст
            enhancer = ImageEnhance.Contrast(image)
            image = enhancer.enhance(1.5)

            # Увеличиваем резкость
            image = image.filter(ImageFilter.SHARPEN)

            # Масштабируем если слишком маленькое
            width, height = image.size
            if width < 1000:
                ratio = 1000 / width
                image = image.resize((int(width * ratio), int(height * ratio)))

            return image

        except Exception as e:
            logger.warning(f"Предобработка изображения не удалась: {e}")
            return image

    def _parse_docx(self, file_path: str) -> str:
        """Извлечение текста из DOCX"""
        try:
            from docx import Document
            doc = Document(file_path)

            text_parts = []
            for para in doc.paragraphs:
                if para.text.strip():
                    text_parts.append(para.text)

            # Также извлекаем текст из таблиц
            for table in doc.tables:
                for row in table.rows:
                    row_text = ' | '.join(cell.text.strip() for cell in row.cells if cell.text.strip())
                    if row_text:
                        text_parts.append(row_text)

            return '\n'.join(text_parts)

        except ImportError:
            raise ImportError("Установите python-docx: pip install python-docx")

    def _parse_txt(self, file_path: str) -> str:
        """Чтение текстового файла"""
        encodings = ['utf-8', 'cp1251', 'cp1252', 'latin-1']

        for encoding in encodings:
            try:
                with open(file_path, 'r', encoding=encoding) as f:
                    return f.read()
            except UnicodeDecodeError:
                continue

        # Если ничего не помогло, читаем с игнорированием ошибок
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            return f.read()

    def _parse_rtf(self, file_path: str) -> str:
        """Извлечение текста из RTF"""
        try:
            from striprtf.striprtf import rtf_to_text
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                rtf_content = f.read()
            return rtf_to_text(rtf_content)

        except ImportError:
            # Простой fallback - убираем RTF теги
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()

            import re
            # Убираем RTF команды
            content = re.sub(r'\\[a-z]+\d*\s?', '', content)
            content = re.sub(r'[{}]', '', content)
            return content.strip()

    def get_supported_formats(self) -> list:
        """Список поддерживаемых форматов"""
        return list(self.SUPPORTED_EXTENSIONS)

    def validate_file(self, filename: str, file_size: int) -> Tuple[bool, Optional[str]]:
        """
        Валидация файла перед парсингом

        Args:
            filename: Имя файла
            file_size: Размер в байтах

        Returns:
            Tuple[is_valid, error_message]
        """
        ext = Path(filename).suffix.lower()

        if ext not in self.SUPPORTED_EXTENSIONS:
            return False, f"Неподдерживаемый формат: {ext}. Поддерживаются: {', '.join(self.SUPPORTED_EXTENSIONS)}"

        if file_size > self.max_file_size:
            max_mb = self.max_file_size // 1024 // 1024
            return False, f"Файл слишком большой. Максимум: {max_mb} MB"

        return True, None
