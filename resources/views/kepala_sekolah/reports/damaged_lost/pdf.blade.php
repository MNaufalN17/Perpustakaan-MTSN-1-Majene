<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Rusak / Hilang</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 18px 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .library {
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
            color: #047857;
        }

        .school {
            font-size: 18px;
            font-weight: bold;
            margin-top: 4px;
        }

        .period {
            font-size: 10px;
            color: #6b7280;
            margin-top: 3px;
        }

        .summary {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-bottom: 12px;
        }

        .summary td {
            width: 20%;
            border: 1px solid #d1d5db;
            padding: 7px;
            border-radius: 6px;
        }

        .summary-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
            font-weight: bold;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 12px 0 6px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            font-size: 7px;
            text-transform: uppercase;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            vertical-align: top;
            font-size: 8px;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
            font-size: 7px;
        }

        .footer {
            margin-top: 18px;
            width: 100%;
        }

        .footer td {
            width: 50%;
            vertical-align: top;
            font-size: 10px;
        }

        .signature {
            text-align: center;
        }

        .signature-space {
            height: 48px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="library">{{ $libraryName }}</div>
        <div class="school">{{ $schoolName }}</div>
        <div class="period">
            Laporan Buku Rusak / Hilang
            @if($keyword)
                — Filter: {{ $keyword }}
            @endif
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Bermasalah</div>
                <div class="summary-value">{{ number_format($totalProblemItems, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Rusak Ringan</div>
                <div class="summary-value">{{ number_format($lightDamagedItems, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Rusak Berat</div>
                <div class="summary-value">{{ number_format($heavyDamagedItems, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Hilang</div>
                <div class="summary-value">{{ number_format($lostItems, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Nonaktif</div>
                <div class="summary-value">{{ number_format($inactiveItems, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Daftar Buku Rusak / Hilang</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 16%;">Kode Eksemplar</th>
                <th style="width: 24%;">Judul Buku</th>
                <th style="width: 14%;">Penulis</th>
                <th style="width: 8%;" class="text-center">DDC</th>
                <th style="width: 7%;" class="text-center">Copy</th>
                <th style="width: 10%;" class="text-center">Status</th>
                <th style="width: 12%;" class="text-center">Kondisi</th>
                <th style="width: 9%;">Ket.</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        <div class="font-bold">{{ $item->item_code ?? '-' }}</div>
                        <div class="muted">
                            {{ $item->classification_code ?? '-' }} -
                            {{ $item->author_code ?? '-' }} -
                            {{ $item->title_code ?? $item->title_initial ?? '-' }}
                        </div>
                    </td>

                    <td>
                        <div class="font-bold">{{ $item->book->title ?? '-' }}</div>
                        <div class="muted">Penerbit: {{ $item->book->publisher ?? '-' }}</div>
                    </td>

                    <td>{{ $item->book->author ?? '-' }}</td>

                    <td class="text-center">
                        {{ $item->book->ddcClass->code ?? $item->classification_code ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->copy_number ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ ucfirst($item->status ?? '-') }}
                    </td>

                    <td class="text-center">
                        {{ ucwords($item->condition ?? '-') }}
                    </td>

                    <td>
                        {{ $item->notes ?? $item->description ?? $item->remarks ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">
                        Tidak ada data buku rusak / hilang yang sesuai.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td>
                <div>Dicetak pada: {{ now()->format('d M Y H:i') }}</div>
            </td>

            <td class="signature">
                <div>Kepala Sekolah / Kepala Perpustakaan</div>
                <div class="signature-space"></div>
                <div class="font-bold">____________________________</div>
            </td>
        </tr>
    </table>
</body>
</html>