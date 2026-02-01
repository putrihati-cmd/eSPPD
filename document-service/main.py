"""
Python Document Generation Microservice
FastAPI service for generating SPPD, Surat Tugas, and Laporan documents.
"""

import os
import uuid
import logging
from pathlib import Path
from datetime import datetime
from typing import Optional

from fastapi import FastAPI, HTTPException, Response
from fastapi.responses import FileResponse
from pydantic import BaseModel, Field

from services.document_generator import DocumentGenerator

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(
    title="eSPPD Document Service",
    description="Microservice for generating SPPD, Surat Tugas, and Laporan documents",
    version="1.0.0"
)

# Configuration
TEMPLATES_DIR = Path(os.getenv("TEMPLATES_DIR", "./templates"))
GENERATED_DIR = Path(os.getenv("GENERATED_DIR", "./generated"))

# Ensure directories exist
GENERATED_DIR.mkdir(parents=True, exist_ok=True)


# ============== Pydantic Models ==============

class EmployeeData(BaseModel):
    nama: str
    nip: str
    jabatan: str = ""
    pangkat: str = ""
    golongan: str = ""
    unit_kerja: str = ""


class SppdData(BaseModel):
    nomor_sppd: str
    employee: EmployeeData
    tujuan: str
    keperluan: str
    tanggal_berangkat: str
    tanggal_kembali: str
    lama_perjalanan: int = 1
    sumber_dana: str = ""
    pejabat_penandatangan: str = ""
    jabatan_penandatangan: str = ""
    nip_penandatangan: str = ""
    tanggal_sppd: str = Field(default_factory=lambda: datetime.now().strftime("%d %B %Y"))


class SuratTugasData(BaseModel):
    nomor_surat: str
    employee: EmployeeData
    perihal: str
    tujuan: str
    tanggal_mulai: str
    tanggal_selesai: str
    pejabat_penandatangan: str = ""
    jabatan_penandatangan: str = ""
    nip_penandatangan: str = ""
    tanggal_surat: str = Field(default_factory=lambda: datetime.now().strftime("%d %B %Y"))


class LaporanData(BaseModel):
    nomor_laporan: str = ""
    employee: EmployeeData
    nomor_sppd: str
    tujuan: str
    tanggal_berangkat: str
    tanggal_kembali: str
    kegiatan: str
    hasil: str
    kesimpulan: str = ""
    saran: str = ""
    tanggal_laporan: str = Field(default_factory=lambda: datetime.now().strftime("%d %B %Y"))


class LPJData(BaseModel):
    """Full LPJ data model following lpj.md specification"""
    employee: EmployeeData
    nomor_sppd: str
    nomor_surat_tugas: str = ""
    tujuan: str
    keperluan: str
    tanggal_berangkat: str
    tanggal_kembali: str
    lama_perjalanan: int = 1
    hari: str = ""  # Day name in Indonesian
    undangan: str = "-"
    kegiatan: str  # Report content
    outputs: list = []  # List of outputs/results
    tempat_lapor: str = "Purwokerto"
    tanggal_lapor: str = Field(default_factory=lambda: datetime.now().strftime("%d %B %Y"))
    atasan_nama: str = ""
    atasan_nip: str = ""


class GenerateResponse(BaseModel):
    success: bool
    message: str
    filename: Optional[str] = None
    download_url: Optional[str] = None


# ============== Endpoints ==============

@app.get("/health")
async def health_check():
    """Health check endpoint for monitoring."""
    return {
        "status": "healthy",
        "service": "document-service",
        "timestamp": datetime.now().isoformat()
    }


@app.post("/generate-sppd-pdf", response_model=GenerateResponse)
async def generate_sppd_pdf(data: SppdData):
    """Generate SPPD document and convert to PDF."""
    try:
        logger.info(f"Generating SPPD PDF for: {data.employee.nama}")
        
        generator = DocumentGenerator(TEMPLATES_DIR, GENERATED_DIR)
        
        context = {
            "NOMOR_SPPD": data.nomor_sppd,
            "NAMA": data.employee.nama,
            "NIP": data.employee.nip,
            "JABATAN": data.employee.jabatan,
            "PANGKAT": data.employee.pangkat,
            "GOLONGAN": data.employee.golongan,
            "UNIT_KERJA": data.employee.unit_kerja,
            "TUJUAN": data.tujuan,
            "KEPERLUAN": data.keperluan,
            "TANGGAL_BERANGKAT": data.tanggal_berangkat,
            "TANGGAL_KEMBALI": data.tanggal_kembali,
            "LAMA_PERJALANAN": data.lama_perjalanan,
            "SUMBER_DANA": data.sumber_dana,
            "PEJABAT_TTD": data.pejabat_penandatangan,
            "JABATAN_TTD": data.jabatan_penandatangan,
            "NIP_TTD": data.nip_penandatangan,
            "TANGGAL_SPPD": data.tanggal_sppd,
        }
        
        docx_filename = generator.generate("template_sppd.docx", context, prefix="SPPD")
        pdf_path = generator.convert_to_pdf(GENERATED_DIR / docx_filename)
        filename = pdf_path.name
        
        return GenerateResponse(
            success=True,
            message="SPPD PDF generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
    except Exception as e:
        logger.error(f"Error generating SPPD PDF: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/generate-surat-tugas-pdf", response_model=GenerateResponse)
async def generate_surat_tugas_pdf(data: SuratTugasData):
    """Generate Surat Tugas document and convert to PDF."""
    try:
        logger.info(f"Generating Surat Tugas PDF for: {data.employee.nama}")
        
        generator = DocumentGenerator(TEMPLATES_DIR, GENERATED_DIR)
        
        context = {
            "NOMOR_SURAT": data.nomor_surat,
            "NAMA": data.employee.nama,
            "NIP": data.employee.nip,
            "JABATAN": data.employee.jabatan,
            "PANGKAT": data.employee.pangkat,
            "GOLONGAN": data.employee.golongan,
            "UNIT_KERJA": data.employee.unit_kerja,
            "PERIHAL": data.perihal,
            "TUJUAN": data.tujuan,
            "TANGGAL_MULAI": data.tanggal_mulai,
            "TANGGAL_SELESAI": data.tanggal_selesai,
            "PEJABAT_TTD": data.pejabat_penandatangan,
            "JABATAN_TTD": data.jabatan_penandatangan,
            "NIP_TTD": data.nip_penandatangan,
            "TANGGAL_SURAT": data.tanggal_surat,
        }
        
        docx_filename = generator.generate("template_surat_tugas.docx", context, prefix="ST")
        pdf_path = generator.convert_to_pdf(GENERATED_DIR / docx_filename)
        filename = pdf_path.name
        
        return GenerateResponse(
            success=True,
            message="Surat Tugas PDF generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
    except Exception as e:
        logger.error(f"Error generating Surat Tugas PDF: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/generate-laporan-pdf", response_model=GenerateResponse)
async def generate_laporan_pdf(data: LaporanData):
    """Generate Laporan document and convert to PDF."""
    try:
        logger.info(f"Generating Laporan PDF for: {data.employee.nama}")
        
        generator = DocumentGenerator(TEMPLATES_DIR, GENERATED_DIR)
        
        context = {
            "NOMOR_LAPORAN": data.nomor_laporan,
            "NAMA": data.employee.nama,
            "NIP": data.employee.nip,
            "JABATAN": data.employee.jabatan,
            "PANGKAT": data.employee.pangkat,
            "UNIT_KERJA": data.employee.unit_kerja,
            "NOMOR_SPPD": data.nomor_sppd,
            "TUJUAN": data.tujuan,
            "TANGGAL_BERANGKAT": data.tanggal_berangkat,
            "TANGGAL_KEMBALI": data.tanggal_kembali,
            "KEGIATAN": data.kegiatan,
            "HASIL": data.hasil,
            "KESIMPULAN": data.kesimpulan,
            "SARAN": data.saran,
            "TANGGAL_LAPORAN": data.tanggal_laporan,
        }
        
        docx_filename = generator.generate("template_laporan.docx", context, prefix="LAPORAN")
        pdf_path = generator.convert_to_pdf(GENERATED_DIR / docx_filename)
        filename = pdf_path.name
        
        return GenerateResponse(
            success=True,
            message="Laporan PDF generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
    except Exception as e:
        logger.error(f"Error generating Laporan PDF: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/generate-lpj", response_model=GenerateResponse)
async def generate_lpj(data: LPJData):
    """
    Generate LPJ (Laporan Perjalanan Dinas) document with table format.
    This generates a formatted document matching the standard Indonesian government format.
    """
    try:
        logger.info(f"Generating LPJ for: {data.employee.nama}")
        
        # Import LPJ generator
        from services.lpj_generator import LPJDocumentGenerator
        
        lpj_gen = LPJDocumentGenerator(str(GENERATED_DIR))
        
        # Prepare data for generator
        lpj_data = {
            'name': data.employee.nama,
            'nip': data.employee.nip,
            'rank': data.employee.pangkat,
            'golongan': data.employee.golongan,
            'position': data.employee.jabatan,
            'destination': data.tujuan,
            'purpose': data.keperluan,
            'day': data.hari,
            'tanggal_berangkat': data.tanggal_berangkat,
            'tanggal_kembali': data.tanggal_kembali,
            'duration': data.lama_perjalanan,
            'invitation': data.undangan,
            'assignment_letter': data.nomor_surat_tugas,
            'sppd_number': data.nomor_sppd,
            'departure_date': data.tanggal_berangkat,
            'report_content': data.kegiatan,
            'outputs': data.outputs,
            'return_date': data.tanggal_kembali,
            'report_place': data.tempat_lapor,
            'report_date': data.tanggal_lapor,
            'superior_name': data.atasan_nama,
            'superior_nip': data.atasan_nip,
            'employee_name': data.employee.nama,
        }
        
        output_path = lpj_gen.generate(lpj_data)
        filename = Path(output_path).name
        
        return GenerateResponse(
            success=True,
            message="LPJ document generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
        
    except Exception as e:
        logger.error(f"Error generating LPJ: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/generate-lpj-pdf", response_model=GenerateResponse)
async def generate_lpj_pdf(data: LPJData):
    """
    Generate LPJ document and convert to PDF.
    Requires LibreOffice to be installed.
    """
    try:
        logger.info(f"Generating LPJ PDF for: {data.employee.nama}")
        
        from services.lpj_generator import LPJDocumentGenerator
        
        lpj_gen = LPJDocumentGenerator(str(GENERATED_DIR))
        
        # Prepare data
        lpj_data = {
            'name': data.employee.nama,
            'nip': data.employee.nip,
            'rank': data.employee.pangkat,
            'golongan': data.employee.golongan,
            'position': data.employee.jabatan,
            'destination': data.tujuan,
            'purpose': data.keperluan,
            'day': data.hari,
            'tanggal_berangkat': data.tanggal_berangkat,
            'tanggal_kembali': data.tanggal_kembali,
            'duration': data.lama_perjalanan,
            'invitation': data.undangan,
            'assignment_letter': data.nomor_surat_tugas,
            'sppd_number': data.nomor_sppd,
            'departure_date': data.tanggal_berangkat,
            'report_content': data.kegiatan,
            'outputs': data.outputs,
            'return_date': data.tanggal_kembali,
            'report_place': data.tempat_lapor,
            'report_date': data.tanggal_lapor,
            'superior_name': data.atasan_nama,
            'superior_nip': data.atasan_nip,
            'employee_name': data.employee.nama,
        }
        
        # Generate Word first
        docx_path = lpj_gen.generate(lpj_data)
        
        # Convert to PDF
        pdf_path = lpj_gen.convert_to_pdf(docx_path)
        filename = Path(pdf_path).name
        
        return GenerateResponse(
            success=True,
            message="LPJ PDF document generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
        
    except RuntimeError as e:
        logger.error(f"PDF conversion error: {e}")
        raise HTTPException(status_code=500, detail=str(e))
    except Exception as e:
        logger.error(f"Error generating LPJ PDF: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/download/{filename}")
async def download_file(filename: str):
    """Download generated document."""
    file_path = GENERATED_DIR / filename
    
    if not file_path.exists():
        raise HTTPException(status_code=404, detail="File not found")
    
    return FileResponse(
        path=file_path,
        filename=filename,
        media_type="application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    )


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
