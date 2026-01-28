<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Perjalanan Dinas</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 2cm;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 30%;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .activity {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        ol {
            padding-left: 20px;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PERJALANAN DINAS</h2>
        <p>{{ $report->spd->spd_number }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td>Nama</td>
            <td>: {{ $report->employee->name }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: {{ $report->employee->nip }}</td>
        </tr>
        <tr>
            <td>Pangkat/Golongan</td>
            <td>: {{ $report->employee->rank }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: {{ $report->employee->position }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>: {{ $report->employee->unit->name }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>: {{ $report->spd->destination }}</td>
        </tr>
        <tr>
            <td>Tanggal Berangkat</td>
            <td>: {{ $report->actual_departure_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Tanggal Kembali</td>
            <td>: {{ $report->actual_return_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Lama Perjalanan</td>
            <td>: {{ $report->actual_duration }} hari</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">I. ISI PERJALANAN</div>
        @foreach ($report->activities as $activity)
            <div class="activity">
                <strong>{{ \Carbon\Carbon::parse($activity->date)->format('d F Y') }}
                    ({{ $activity->time }})</strong><br>
                <strong>Lokasi:</strong> {{ $activity->location }}<br>
                <p>{{ $activity->description }}</p>
            </div>
        @endforeach
    </div>

    <div class="section">
        <div class="section-title">II. OUTPUT PERJALANAN</div>
        <ol>
            @foreach ($report->outputs as $output)
                <li>{{ $output->description }}</li>
            @endforeach
        </ol>
    </div>

    <div class="signature">
        <p>{{ $report->employee->unit->location ?? 'Jakarta' }}, {{ now()->format('d F Y') }}</p>
        <p style="margin-top: 80px;">
            <strong>{{ $report->employee->name }}</strong><br>
            NIP. {{ $report->employee->nip }}
        </p>
    </div>

    @if ($report->is_verified)
        <div style="margin-top: 40px;">
            <p><strong>Mengetahui/Menyetujui:</strong></p>
            <p style="margin-top: 80px;">
                <strong>{{ $report->verifier->name ?? 'Atasan Langsung' }}</strong><br>
                @if ($report->verifier)
                    NIP. {{ $report->verifier->nip }}
                @endif
            </p>
        </div>
    @endif
</body>

</html>
