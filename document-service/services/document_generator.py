"""
Document Generator Service
Generates DOCX documents from templates using docxtpl.
"""

import uuid
from pathlib import Path
from datetime import datetime
from docxtpl import DocxTemplate


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
        
        pdf_path = docx_path.with_suffix('.pdf')
        
        try:
            subprocess.run([
                'soffice',
                '--headless',
                '--convert-to', 'pdf',
                '--outdir', str(docx_path.parent),
                str(docx_path)
            ], check=True, capture_output=True)
            
            return pdf_path
        except subprocess.CalledProcessError as e:
            raise RuntimeError(f"PDF conversion failed: {e.stderr.decode()}")
        except FileNotFoundError:
            raise RuntimeError("LibreOffice not found. Please install LibreOffice for PDF conversion.")
