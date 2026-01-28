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


@app.post("/generate-sppd", response_model=GenerateResponse)
async def generate_sppd(data: SppdData):
    """Generate SPPD document from template."""
    try:
        logger.info(f"Generating SPPD for: {data.employee.nama}")
        
        generator = DocumentGenerator(TEMPLATES_DIR, GENERATED_DIR)
        
        # Prepare context for template
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
        
        filename = generator.generate("template_sppd.docx", context, prefix="SPPD")
        
        return GenerateResponse(
            success=True,
            message="SPPD document generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
        
    except FileNotFoundError as e:
        logger.error(f"Template not found: {e}")
        raise HTTPException(status_code=404, detail=f"Template not found: {e}")
    except Exception as e:
        logger.error(f"Error generating SPPD: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/generate-surat-tugas", response_model=GenerateResponse)
async def generate_surat_tugas(data: SuratTugasData):
    """Generate Surat Tugas document from template."""
    try:
        logger.info(f"Generating Surat Tugas for: {data.employee.nama}")
        
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
        
        filename = generator.generate("template_surat_tugas.docx", context, prefix="ST")
        
        return GenerateResponse(
            success=True,
            message="Surat Tugas document generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
        
    except FileNotFoundError as e:
        logger.error(f"Template not found: {e}")
        raise HTTPException(status_code=404, detail=f"Template not found: {e}")
    except Exception as e:
        logger.error(f"Error generating Surat Tugas: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/generate-laporan", response_model=GenerateResponse)
async def generate_laporan(data: LaporanData):
    """Generate Laporan Perjalanan document from template."""
    try:
        logger.info(f"Generating Laporan for: {data.employee.nama}")
        
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
        
        filename = generator.generate("template_laporan.docx", context, prefix="LAPORAN")
        
        return GenerateResponse(
            success=True,
            message="Laporan document generated successfully",
            filename=filename,
            download_url=f"/download/{filename}"
        )
        
    except FileNotFoundError as e:
        logger.error(f"Template not found: {e}")
        raise HTTPException(status_code=404, detail=f"Template not found: {e}")
    except Exception as e:
        logger.error(f"Error generating Laporan: {e}")
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
