<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SPD - {{ $spd->spd_number }}</title>
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

        table.bordered {
            border: 1px solid #000;
        }

        table.bordered th,
        table.bordered td {
            border: 1px solid #000;
            padding: 8px;
        }

        table.bordered th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .signature {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-box {
            display: inline-block;
            width: 30%;
            text-align: center;
            vertical-align: top;
            margin: 0 10px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 150px;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>SURAT PERJALANAN DINAS (SPD)</h2>
        <h3>Nomor: {{ $spd->spd_number }}</h3>
    </div>

    <table>
        <tr>
            <td style="width: 200px;">Nama</td>
            <td>: {{ $spd->employee->name }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: {{ $spd->employee->nip }}</td>
        </tr>
        <tr>
            <td>Pangkat/Golongan</td>
            <td>: {{ $spd->employee->grade }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: {{ $spd->employee->position }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>: {{ $spd->destination }}</td>
        </tr>
        <tr>
            <td>Maksud Perjalanan</td>
            <td>: {{ $spd->purpose }}</td>
        </tr>
        <tr>
            <td>Tanggal/Waktu</td>
            <td>: {{ $spd->departure_date->format('d F Y') }} s.d. {{ $spd->return_date->format('d F Y') }}
                ({{ $spd->duration }} hari)</td>
        </tr>
        <tr>
            <td>Transportasi</td>
            <td>: {{ ucwords(str_replace('_', ' ', $spd->transport_type)) }}</td>
        </tr>
    </table>

    <h3 style="margin-top: 30px;">RINCIAN BIAYA PERJALANAN DINAS</h3>

    <table class="bordered">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Uraian</th>
                <th width="25%">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($spd->costs as $index => $cost)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $cost->description }}</td>
                    <td style="text-align: right;">{{ number_format($cost->estimated_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ $spd->formatCost() }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        <strong>Pembebanan Anggaran:</strong><br>
        {{ $spd->budget->code }} - {{ $spd->budget->name }}
    </p>

    <div class="signature">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 33%; text-align: center; vertical-align: top;">
                    <p>Mengetahui,<br>Pejabat Pembuat Komitmen</p>
                    <div style="height: 70px;"></div>
                    <div style="border-top: 1px solid #000; display: inline-block; width: 150px;"></div>
                    <p><strong>NIP. </strong></p>
                </td>
                <td style="width: 33%; text-align: center; vertical-align: top;">
                    <p>Bendahara</p>
                    <div style="height: 70px;"></div>
                    <div style="border-top: 1px solid #000; display: inline-block; width: 150px;"></div>
                    <p><strong>NIP. </strong></p>
                </td>
                <td style="width: 33%; text-align: center; vertical-align: top;">
                    <p>Purwokerto, {{ now()->format('d F Y') }}<br>Pelaksana SPD</p>
                    <div style="height: 70px;"></div>
                    <div style="border-top: 1px solid #000; display: inline-block; width: 150px;"></div>
                    <p><strong>{{ $spd->employee->name }}</strong><br>NIP. {{ $spd->employee->nip }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
