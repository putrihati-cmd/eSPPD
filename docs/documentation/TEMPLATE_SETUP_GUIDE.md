# 📄 DOCUMENT TEMPLATES SETUP GUIDE

**Status:** Implementation Guide  
**Date:** 29 January 2026  
**Last Updated:** 29 January 2026

---

## Overview

The e-SPPD system uses Word (.docx) templates for document generation. This guide explains how to create and setup these templates.

---

## Templates Required

### 1. SPPD Template (Surat Perjalanan Dinas)
**File:** `template_sppd.docx`  
**Location:** `document-service/templates/template_sppd.docx`  
**Purpose:** Main travel authorization letter

### 2. Task Letter Template (Surat Perintah Tugas)
**File:** `template_surat_tugas.docx`  
**Location:** `document-service/templates/template_surat_tugas.docx`  
**Purpose:** Task assignment letter before travel

### 3. Trip Report Template (Laporan Perjalanan Dinas)
**File:** `template_laporan.docx`  
**Location:** `document-service/templates/template_laporan.docx`  
**Purpose:** Post-travel report documentation

---

## How to Create Templates

### Step 1: Use Microsoft Word
1. Open Microsoft Word (or LibreOffice Writer)
2. Create a new document
3. Design your document template layout
4. Add organization header/footer as needed

### Step 2: Add Placeholders
Use the exact placeholder format: `{{PLACEHOLDER_NAME}}`

**Important:** Use double curly braces `{{` and `}}` with uppercase names.

### Step 3: Save as .docx Format
- Save file with `.docx` extension
- Filename must match exactly: `template_sppd.docx`, `template_surat_tugas.docx`, `template_laporan.docx`
- Save in: `document-service/templates/` folder

### Step 4: Verify Placeholders
Ensure all placeholders use exact names (see sections below).

---

## Template 1: SPPD (Surat Perjalanan Dinas)

### Placeholders

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{NOMOR_SPPD}}` | SPPD Letter Number | 0001/Un.19/K.AUPK/FP.01/2025 |
| `{{NAMA}}` | Employee Name | Dr. Ahmad Wijaya |
| `{{NIP}}` | Employee ID Number | 123456789012345678 |
| `{{JABATAN}}` | Position | Dosen |
| `{{PANGKAT}}` | Rank | Lektor |
| `{{GOLONGAN}}` | Grade/Class | III/c |
| `{{UNIT_KERJA}}` | Work Unit | Fakultas Pendidikan |
| `{{TUJUAN}}` | Travel Destination | Jakarta |
| `{{KEPERLUAN}}` | Travel Purpose | Conference Attendance |
| `{{TANGGAL_BERANGKAT}}` | Departure Date | 15 Februari 2025 |
| `{{TANGGAL_KEMBALI}}` | Return Date | 17 Februari 2025 |
| `{{LAMA_PERJALANAN}}` | Duration | 3 hari |
| `{{SUMBER_DANA}}` | Budget Source | Anggaran APBU 2025 |
| `{{PEJABAT_TTD}}` | Signing Officer Name | Prof. Dr. H. Mujahidin |
| `{{JABATAN_TTD}}` | Signing Officer Position | Rektor |
| `{{NIP_TTD}}` | Signing Officer NIP | 196801011993031001 |
| `{{TANGGAL_SPPD}}` | Document Date | 10 Februari 2025 |

### Sample Layout
```
KEMENTERIAN AGAMA REPUBLIK INDONESIA
UNIVERSITAS ISLAM NEGERI SAIZU PURWOKERTO
SURAT PERJALANAN DINAS (SPPD)

Nomor: {{NOMOR_SPPD}}

Yang bertanda tangan di bawah ini:
Nama: {{PEJABAT_TTD}}
Jabatan: {{JABATAN_TTD}}
NIP: {{NIP_TTD}}

Dengan ini memberi tugas kepada:
Nama: {{NAMA}}
NIP: {{NIP}}
Jabatan: {{JABATAN}}
Pangkat: {{PANGKAT}}
Golongan: {{GOLONGAN}}
Unit Kerja: {{UNIT_KERJA}}

Untuk melaksanakan perjalanan dinas ke:
Tujuan: {{TUJUAN}}
Keperluan: {{KEPERLUAN}}
Tanggal Berangkat: {{TANGGAL_BERANGKAT}}
Tanggal Kembali: {{TANGGAL_KEMBALI}}
Lama Perjalanan: {{LAMA_PERJALANAN}}

Sumber Dana: {{SUMBER_DANA}}

Demikian SPPD ini diberikan untuk digunakan sebagaimana mestinya.

Purwokerto, {{TANGGAL_SPPD}}

{{PEJABAT_TTD}}
NIP. {{NIP_TTD}}
```

---

## Template 2: Surat Perintah Tugas (Task Letter)

### Placeholders

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{NOMOR_SURAT}}` | Task Letter Number | 0001/Un.19/K.AUPK/SPT/2025 |
| `{{NAMA}}` | Employee Name | Dr. Ahmad Wijaya |
| `{{NIP}}` | Employee ID Number | 123456789012345678 |
| `{{JABATAN}}` | Position | Dosen |
| `{{PANGKAT}}` | Rank | Lektor |
| `{{UNIT_KERJA}}` | Work Unit | Fakultas Pendidikan |
| `{{PERIHAL}}` | Task Subject | Peserta Konferensi Nasional |
| `{{TUJUAN}}` | Task Destination | Jakarta |
| `{{TANGGAL_MULAI}}` | Task Start Date | 15 Februari 2025 |
| `{{TANGGAL_SELESAI}}` | Task End Date | 17 Februari 2025 |
| `{{PEJABAT_TTD}}` | Signing Officer Name | Prof. Dr. H. Mujahidin |
| `{{TANGGAL_SURAT}}` | Letter Date | 10 Februari 2025 |

### Sample Layout
```
KEMENTERIAN AGAMA REPUBLIK INDONESIA
UNIVERSITAS ISLAM NEGERI SAIZU PURWOKERTO
SURAT PERINTAH TUGAS (SPT)

Nomor: {{NOMOR_SURAT}}
Perihal: {{PERIHAL}}

Diberikan kepada:
Nama: {{NAMA}}
NIP: {{NIP}}
Jabatan: {{JABATAN}}
Pangkat: {{PANGKAT}}
Unit Kerja: {{UNIT_KERJA}}

Untuk melaksanakan tugas:
Perihal: {{PERIHAL}}
Tujuan: {{TUJUAN}}
Tanggal Mulai: {{TANGGAL_MULAI}}
Tanggal Selesai: {{TANGGAL_SELESAI}}

Demikian surat perintah tugas ini diberikan untuk digunakan sebagaimana mestinya.

Purwokerto, {{TANGGAL_SURAT}}

{{PEJABAT_TTD}}
```

---

## Template 3: Laporan Perjalanan Dinas (Trip Report)

### Placeholders

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{NOMOR_LAPORAN}}` | Report Number | 0001/Un.19/K.AUPK/LPD/2025 |
| `{{NAMA}}` | Employee Name | Dr. Ahmad Wijaya |
| `{{NIP}}` | Employee ID Number | 123456789012345678 |
| `{{JABATAN}}` | Position | Dosen |
| `{{NOMOR_SPPD}}` | Related SPPD Number | 0001/Un.19/K.AUPK/FP.01/2025 |
| `{{TUJUAN}}` | Travel Destination | Jakarta |
| `{{TANGGAL_BERANGKAT}}` | Departure Date | 15 Februari 2025 |
| `{{TANGGAL_KEMBALI}}` | Return Date | 17 Februari 2025 |
| `{{KEGIATAN}}` | Activity Description | Presentasi makalah tentang teknologi pendidikan |
| `{{HASIL}}` | Activity Results | Mendapat feedback positif dari 50+ peserta |
| `{{KESIMPULAN}}` | Conclusion | Konferensi memberikan insight baru... |
| `{{SARAN}}` | Recommendations | Perlu ditingkatkan... |
| `{{TANGGAL_LAPORAN}}` | Report Date | 20 Februari 2025 |

### Sample Layout
```
KEMENTERIAN AGAMA REPUBLIK INDONESIA
UNIVERSITAS ISLAM NEGERI SAIZU PURWOKERTO
LAPORAN PERJALANAN DINAS

Nomor: {{NOMOR_LAPORAN}}

Pelapor:
Nama: {{NAMA}}
NIP: {{NIP}}
Jabatan: {{JABATAN}}

Identitas Perjalanan:
Nomor SPPD: {{NOMOR_SPPD}}
Tujuan: {{TUJUAN}}
Tanggal Berangkat: {{TANGGAL_BERANGKAT}}
Tanggal Kembali: {{TANGGAL_KEMBALI}}

---

I. KEGIATAN YANG DILAKUKAN

{{KEGIATAN}}

II. HASIL YANG DICAPAI

{{HASIL}}

III. KESIMPULAN

{{KESIMPULAN}}

IV. SARAN

{{SARAN}}

---

Demikian laporan ini dibuat untuk digunakan sebagaimana mestinya.

Purwokerto, {{TANGGAL_LAPORAN}}

{{NAMA}}
NIP. {{NIP}}
```

---

## Installation Steps

### Method 1: Manual (Recommended)

1. **Open Microsoft Word**
   - Create a new document
   - Design your template using the sample layouts above
   - Add placeholders exactly as shown

2. **Save Template**
   - File → Save As
   - Format: `.docx` (Office Open XML)
   - Name: `template_sppd.docx` (or other names)
   - Location: `c:\laragon\www\eSPPD\document-service\templates\`

3. **Repeat for all 3 templates**
   - template_sppd.docx
   - template_surat_tugas.docx
   - template_laporan.docx

### Method 2: Using Python Script
```python
from docx import Document
from docx.shared import Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH

# Create a new Document
doc = Document()

# Add title
title = doc.add_paragraph('SURAT PERJALANAN DINAS')
title.alignment = WD_ALIGN_PARAGRAPH.CENTER
title.runs[0].font.size = Pt(16)
title.runs[0].font.bold = True

# Add content with placeholders
doc.add_paragraph('Nomor: {{NOMOR_SPPD}}')
doc.add_paragraph('Nama: {{NAMA}}')
# ... more content

# Save
doc.save('template_sppd.docx')
```

---

## Verification Checklist

After creating templates, verify:

- [ ] All 3 template files exist:
  - [ ] template_sppd.docx
  - [ ] template_surat_tugas.docx
  - [ ] template_laporan.docx

- [ ] Files located in correct folder: `document-service/templates/`

- [ ] Placeholders use correct format: `{{PLACEHOLDER_NAME}}`

- [ ] All placeholders are uppercase

- [ ] No typos in placeholder names (match exactly with list above)

- [ ] Files are readable:
  ```bash
  ls -la document-service/templates/
  # Should show:
  # -rw-r--r-- template_sppd.docx
  # -rw-r--r-- template_surat_tugas.docx
  # -rw-r--r-- template_laporan.docx
  ```

---

## Testing Template Generation

### Manual Test

```bash
# Start the Python microservice
cd document-service
docker-compose up -d

# Test SPPD generation
curl -X POST http://localhost:8001/generate-sppd \
  -H "Content-Type: application/json" \
  -d '{
    "nomor_sppd": "0001/Un.19/K.AUPK/FP.01/2025",
    "nama": "Dr. Ahmad Wijaya",
    "nip": "123456789012345678",
    ...
  }'
```

### Via Web Interface

1. Login as Admin
2. Go to Settings → Templates
3. Click "Test Generation"
4. Select template type (SPPD / Task Letter / Report)
5. Preview generated document

---

## Troubleshooting

### Issue: "Template file not found"
**Solution:** Verify file exists and name matches exactly (case-sensitive on Linux)

### Issue: "Placeholder not replaced"
**Solution:** Check placeholder format is exactly `{{NAME}}` (uppercase, no spaces)

### Issue: "Document formatting looks wrong"
**Solution:** Ensure template uses basic formatting (no macros, no complex formatting)

### Issue: "Can't generate DOCX"
**Solution:** 
1. Verify Python microservice is running: `docker-compose ps`
2. Check logs: `docker-compose logs -f`
3. Ensure all required placeholders are provided

---

## Document Generation Flow

```
User creates SPPD
    ↓
Clicks "Generate Document"
    ↓
Laravel validates data
    ↓
Calls Python microservice
    ↓
Python loads template_sppd.docx
    ↓
Replaces all {{PLACEHOLDER}} with actual values
    ↓
Generates output.docx
    ↓
Returns file to user for download
```

---

## API Integration

### PHP Code Example

```php
// app/Services/PythonDocumentService.php

public function generateSppd($sppd)
{
    $data = [
        'nomor_sppd' => $sppd->spd_number,
        'nama' => $sppd->employee->name,
        'nip' => $sppd->employee->nip,
        'jabatan' => $sppd->employee->position,
        'tujuan' => $sppd->destination,
        // ... all other fields
    ];
    
    $response = Http::post(
        config('services.python.url') . '/generate-sppd',
        $data
    );
    
    return $response->body();
}
```

---

## Next Steps

1. ✅ Create the 3 template files (see Instructions above)
2. ✅ Place in `document-service/templates/` folder
3. ✅ Test document generation (see Testing section)
4. ✅ Verify output quality
5. ✅ Customize templates for your institution

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Maintained by:** Development Team
