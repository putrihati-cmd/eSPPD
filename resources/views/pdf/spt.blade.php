<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SPT - {{ $spd->spt_number }}</title>
    <style>
        @page {
            margin: 2cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 12pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.data {
            border: 1px solid #000;
        }

        table.data td {
            padding: 8px;
        }

        table.data td:first-child {
            width: 200px;
        }

        .signature {
            margin-top: 40px;
        }

        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 200px;
        }

        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 300px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>SURAT PERINTAH TUGAS</h2>
        <h3>Nomor: {{ $spd->spt_number }}</h3>
    </div>

    <table class="data">
        <tr>
            <td>1. Pejabat yang memberi perintah</td>
            <td>: Rektor UIN Saizu Purwokerto</td>
        </tr>
        <tr>
            <td>2. Nama/NIP yang diperintah</td>
            <td>: {{ $spd->employee->name }} / {{ $spd->employee->nip }}</td>
        </tr>
        <tr>
            <td>3. a. Pangkat dan Golongan</td>
            <td>: {{ $spd->employee->grade }}</td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; b. Jabatan/Instansi</td>
            <td>: {{ $spd->employee->position }}</td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; c. Tingkat biaya perjalanan dinas</td>
            <td>: Golongan {{ substr($spd->employee->grade, 0, 1) }}</td>
        </tr>
        <tr>
            <td>4. Maksud Perjalanan Dinas</td>
            <td>: {{ $spd->purpose }}</td>
        </tr>
        <tr>
            <td>5. Alat angkutan yang dipergunakan</td>
            <td>: {{ ucwords(str_replace('_', ' ', $spd->transport_type)) }}</td>
        </tr>
        <tr>
            <td>6. a. Tempat Berangkat</td>
            <td>: Purwokerto</td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; b. Tempat Tujuan</td>
            <td>: {{ $spd->destination }}</td>
        </tr>
        <tr>
            <td>7. a. Lamanya Perjalanan Dinas</td>
            <td>: {{ $spd->duration }} hari</td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; b. Tanggal Berangkat</td>
            <td>: {{ $spd->departure_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; c. Tanggal harus kembali</td>
            <td>: {{ $spd->return_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>8. Pengikut</td>
            <td>: -</td>
        </tr>
        <tr>
            <td>9. Pembebanan Anggaran</td>
            <td></td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; a. Instansi</td>
            <td>: UIN Saizu Purwokerto</td>
        </tr>
        <tr>
            <td>&nbsp;&nbsp;&nbsp; b. Akun</td>
            <td>: {{ $spd->budget->code }} - {{ $spd->budget->name }}</td>
        </tr>
        <tr>
            <td>10. Keterangan Lain-lain</td>
            <td>: @if ($spd->invitation_number)
                    Undangan Nomor {{ $spd->invitation_number }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="signature">
        <div class="signature-box" style="float: right;">
            <p>Purwokerto, {{ now()->format('d F Y') }}</p>
            <p>Pejabat Pembuat Komitmen</p>
            <div class="signature-line"></div>
            <p><strong>NIP. </strong></p>
        </div>
    </div>
</body>

</html>
