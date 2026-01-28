<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $reportType }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
        }

        .filters span {
            margin-right: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #334155;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
        }

        td {
            padding: 6px 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $reportType }}</h1>
        <p>Digenerate: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if (!empty(array_filter($filters)))
        <div class="filters">
            <strong>Filter:</strong>
            @if ($filters['status'] ?? null)
                <span>Status: {{ ucfirst($filters['status']) }}</span>
            @endif
            @if ($filters['from_date'] ?? null)
                <span>Dari: {{ \Carbon\Carbon::parse($filters['from_date'])->format('d/m/Y') }}</span>
            @endif
            @if ($filters['to_date'] ?? null)
                <span>Sampai: {{ \Carbon\Carbon::parse($filters['to_date'])->format('d/m/Y') }}</span>
            @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @foreach ($fields as $field)
                        <td>
                            @php
                                $value = $row[$field] ?? ($row->{$field} ?? '-');
                                if (
                                    in_array($field, ['departure_date', 'return_date', 'created_at']) &&
                                    $value !== '-'
                                ) {
                                    $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
                                }
                                if (in_array($field, ['estimated_cost', 'actual_cost']) && $value !== '-') {
                                    $value = 'Rp ' . number_format($value, 0, ',', '.');
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        e-SPPD System - {{ config('app.name') }} | Total Data: {{ count($data) }}
    </div>
</body>

</html>
