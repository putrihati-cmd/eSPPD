"""
Document Generator Service
Generates DOCX documents from templates using docxtpl.
"""

import uuid
from pathlib import Path
from datetime import datetime
import logging
from docxtpl import DocxTemplate

logger = logging.getLogger(__name__)


class DocumentGenerator:
    """
    Service class to generate documents from DOCX templates.
    Uses python-docx-template (docxtpl) for template rendering.
    """
    
    def __init__(self, templates_dir: Path, output_dir: Path):
        self.templates_dir = Path(templates_dir)
        self.output_dir = Path(output_dir)
        self.output_dir.mkdir(parents=True, exist_ok=True)
    
    def generate(self, template_name: str, context: dict, prefix: str = "DOC") -> str:
        """
        Generate a document from a template.
        
        Args:
            template_name: Name of the template file (e.g., 'template_sppd.docx')
            context: Dictionary of placeholder values
            prefix: Prefix for the output filename
            
        Returns:
            Generated filename
        """
        template_path = self.templates_dir / template_name
        
        if not template_path.exists():
            raise FileNotFoundError(f"Template {template_name} not found at {template_path}")
        
        # Load template
        doc = DocxTemplate(template_path)
        
        # Render template with context
        doc.render(context)
        
        # Generate unique filename
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        unique_id = str(uuid.uuid4())[:8]
        output_filename = f"{prefix}_{timestamp}_{unique_id}.docx"
        output_path = self.output_dir / output_filename
        
        # Save document
        doc.save(output_path)
        
        return output_filename
    
    def convert_to_pdf(self, docx_path: Path) -> Path:
        """
        Convert DOCX to PDF using LibreOffice.
        Requires LibreOffice to be installed.
        
        Args:
            docx_path: Path to the DOCX file
            
        Returns:
            Path to the generated PDF file
        """
        import subprocess
        import os
        
        pdf_path = docx_path.with_suffix('.pdf')
        
        # Common LibreOffice paths on Windows
        soffice_paths = [
            'soffice', # If in PATH
            r'C:\Program Files\LibreOffice\program\soffice.exe',
            r'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
            r'C:\laragon\bin\libreoffice\program\soffice.exe',
        ]
        
        soffice_bin = None
        for path in soffice_paths:
            if path != 'soffice' and os.path.exists(path):
                soffice_bin = path
                break
        
        if not soffice_bin:
            soffice_bin = 'soffice'

        try:
            logger.info(f"Converting {docx_path} to PDF using {soffice_bin}")
            subprocess.run([
                soffice_bin,
                '--headless',
                '--convert-to', 'pdf',
                '--outdir', str(docx_path.parent),
                str(docx_path)
            ], check=True, capture_output=True)
            
            return pdf_path
        except subprocess.CalledProcessError as e:
            logger.error(f"PDF conversion failed: {e.stderr.decode()}")
            raise RuntimeError(f"PDF conversion failed: {e.stderr.decode()}")
        except FileNotFoundError:
            logger.error("LibreOffice (soffice) not found in common paths.")
            raise RuntimeError("LibreOffice not found. Please install LibreOffice and add 'soffice' to PATH.")
