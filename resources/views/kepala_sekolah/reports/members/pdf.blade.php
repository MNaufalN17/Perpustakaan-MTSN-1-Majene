<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Anggota Perpustakaan</title>

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
            margin-bottom: 12px;
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

        .status-active {
            color: #047857;
            font-weight: bold;
        }

        .status-inactive {
            color: #b91c1c;
            font-weight: bold;
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
            Laporan Anggota Perpustakaan
            @if($keyword)
                — Filter: {{ $keyword }}
            @endif
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Anggota</div>
                <div class="summary-value">{{ number_format($totalMembers, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Aktif</div>
                <div class="summary-value">{{ number_format($activeMembers, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Nonaktif</div>
                <div class="summary-value">{{ number_format($inactiveMembers, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Siswa</div>
                <div class="summary-value">{{ number_format($studentMembers, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Guru</div>
                <div class="summary-value">{{ number_format($teacherMembers, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Rekap Per Kelas</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Kelas</th>
                <th style="width: 13%;" class="text-center">Total</th>
                <th style="width: 13%;" class="text-center">Aktif</th>
                <th style="width: 13%;" class="text-center">Nonaktif</th>
                <th style="width: 13%;" class="text-center">Siswa</th>
                <th style="width: 13%;" class="text-center">Guru</th>
            </tr>
        </thead>

        <tbody>
            @forelse($classRecaps as $recap)
                <tr>
                    <td class="font-bold">{{ $recap['class_name'] }}</td>
                    <td class="text-center">{{ number_format($recap['total'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($recap['active'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($recap['inactive'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($recap['students'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($recap['teachers'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Tidak ada rekap kelas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Daftar Anggota</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode</th>
                <th style="width: 25%;">Nama</th>
                <th style="width: 15%;">NIS/NIP</th>
                <th style="width: 12%;">Jenis</th>
                <th style="width: 18%;">Kelas</th>
                <th style="width: 15%;" class="text-center">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($members as $member)
                <tr>
                    <td class="font-bold">{{ $member->member_code ?? '-' }}</td>
                    <td>{{ $member->name ?? '-' }}</td>
                    <td>{{ $member->nis_nip ?? '-' }}</td>
                    <td>{{ ucfirst($member->member_type ?? '-') }}</td>
                    <td>{{ $member->studentClass->class_name ?? 'Guru/Staff' }}</td>
                    <td class="text-center">
                        @if($member->status === 'aktif')
                            <span class="status-active">Aktif</span>
                        @else
                            <span class="status-inactive">Nonaktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Tidak ada data anggota yang sesuai.
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