<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perpustakaan</title>

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
            font-size: 10px;
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
            border-spacing: 6px;
            margin-bottom: 12px;
        }

        .summary td {
            width: 25%;
            border: 1px solid #d1d5db;
            padding: 8px;
            border-radius: 6px;
        }

        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            font-weight: bold;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
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
            padding: 6px 5px;
            font-size: 8px;
            text-transform: uppercase;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            vertical-align: top;
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
            font-size: 8px;
        }

        .status {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .status-late {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-active {
            background: #fef3c7;
            color: #92400e;
        }

        .status-done {
            background: #d1fae5;
            color: #065f46;
        }

        .status-other {
            background: #f3f4f6;
            color: #374151;
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
            Laporan Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
            sampai {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ number_format($totalLoans ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Selesai</div>
                <div class="summary-value">{{ number_format($completedLoans ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Aktif</div>
                <div class="summary-value">{{ number_format($activeLoans ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Terlambat</div>
                <div class="summary-value">{{ number_format($overdueLoans ?? 0, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Daftar Peminjam dan Status Peminjaman</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 16%;">Kode</th>
                <th style="width: 30%;">Peminjam</th>
                <th style="width: 14%;">Tanggal Pinjam</th>
                <th style="width: 14%;">Batas Kembali</th>
                <th style="width: 10%;" class="text-center">Buku</th>
                <th style="width: 16%;" class="text-center">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($loans ?? [] as $loan)
                @php
                    $isLate = in_array($loan->status, ['aktif', 'terlambat'], true)
                        && $loan->due_date
                        && \Carbon\Carbon::parse($loan->due_date)->startOfDay()->lt(today());

                    $statusLabel = $isLate
                        ? 'Terlambat'
                        : ($loan->status === 'aktif'
                            ? 'Dipinjam'
                            : ($loan->status === 'selesai'
                                ? 'Selesai'
                                : ucfirst($loan->status ?? '-')));

                    $statusClass = $isLate
                        ? 'status-late'
                        : ($loan->status === 'aktif'
                            ? 'status-active'
                            : ($loan->status === 'selesai'
                                ? 'status-done'
                                : 'status-other'));
                @endphp

                <tr>
                    <td class="font-bold">
                        {{ $loan->loan_code }}
                    </td>

                    <td>
                        <div class="font-bold">{{ $loan->member->name ?? '-' }}</div>
                        <div class="muted">
                            {{ $loan->member->nis_nip ?? '-' }}

                            @if($loan->member?->studentClass)
                                — {{ $loan->member->studentClass->class_name }}
                            @endif
                        </div>
                    </td>

                    <td>
                        {{ $loan->loan_date ? \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') : '-' }}
                    </td>

                    <td>
                        {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('d M Y') : '-' }}
                    </td>

                    <td class="text-center font-bold">
                        {{ $loan->loanItems->count() }}
                    </td>

                    <td class="text-center">
                        <span class="status {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td>
                <div>Dicetak pada: {{ now()->format('d M Y H:i') }}</div>
                <div>Nominal denda per hari: Rp {{ number_format($finePerDay ?? 0, 0, ',', '.') }}</div>
                <div>Estimasi denda: Rp {{ number_format($totalFines ?? 0, 0, ',', '.') }}</div>
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