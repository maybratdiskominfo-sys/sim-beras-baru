<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Distribusi Beras - {{ $pejabat['nama_instansi'] }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        /* Kop Surat Resmi */
        .kop-surat {
            border-bottom: 3px solid #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
            text-align: center;
            position: relative;
        }
        .kop-border-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 20px;
        }
        .logo-pemda {
            position: absolute;
            left: 0;
            top: -5px;
            width: 60px;
        }
        .header-text h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0 0;
            font-size: 9px;
            font-style: italic;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Stats Cards */
        .stats-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .stat-card-wrap {
            padding: 0 5px;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px;
            text-align: center;
        }
        .stat-card .label {
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            display: block;
        }
        .stat-card .value {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Table Design */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.main-table tr {
            page-break-inside: avoid;
        }
        .main-table th {
            background-color: #1e293b;
            color: white;
            text-align: center;
            padding: 8px 4px;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #1e293b;
        }
        .main-table td {
            padding: 5px 4px;
            border: 1px solid #e2e8f0;
        }
        .main-table tr:nth-child(even) { background-color: #f8fafc; }

        .main-table tfoot td {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
            border-top: 2px solid #1e293b;
        }

        /* Badge Status */
        .badge {
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-gray { background-color: #f1f5f9; color: #475569; }

        /* Signature Section */
        .signature-table {
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .sig-space {
            height: 60px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="kop-surat clearfix">
        @if(!empty($pejabat['logo']))
            <img class="logo-pemda" src="{{ public_path('storage/' . $pejabat['logo']) }}" alt="Logo">
        @endif
        <div class="header-text">
            <h1>Pemerintah Kabupaten Maybrat</h1>
            <h2>{{ strtoupper($pejabat['nama_instansi']) }}</h2>
            <p>{{ $pejabat['alamat'] }}</p>
        </div>
    </div>

    <div class="kop-border-thin"></div>

    <div class="text-center" style="margin-bottom: 15px;">
        <h3 style="margin:0; text-decoration: underline; font-size: 12px;">REKAPITULASI DISTRIBUSI BERAS TAHAP {{ $tahap }}</h3>
        <p style="margin:4px 0; font-size: 9px;">
            Periode: {{ $bulan_nama }} {{ $tahun }}
        </p>
    </div>

    {{-- Ringkasan Statistik --}}
    <table class="stats-table">
        <tr>
            <td width="33.3%" class="stat-card-wrap">
                <div class="stat-card" style="border-left: 3px solid #22c55e;">
                    <span class="label">Jatah Lunas (100%)</span>
                    <span class="value">{{ $stats['lunas'] }} Pegawai</span>
                </div>
            </td>
            <td width="33.3%" class="stat-card-wrap">
                <div class="stat-card" style="border-left: 3px solid #eab308;">
                    <span class="label">Sebagian (Sisa)</span>
                    <span class="value">{{ $stats['sisa'] }} Pegawai</span>
                </div>
            </td>
            <td width="33.3%" class="stat-card-wrap">
                <div class="stat-card" style="border-left: 3px solid #94a3b8;">
                    <span class="label">Belum Diambil</span>
                    <span class="value">{{ $stats['belum'] }} Pegawai</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="30%">Nama Pegawai</th>
                <th width="10%">Jatah (Kg)</th>
                <th width="10%">Realisasi (Kg)</th>
                <th width="10%">Sisa (Kg)</th>
                <th width="15%">Tanggal Ambil</th>
                <th width="21%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div class="font-bold">{{ $row['nama'] }}</div>
                    <div style="font-size: 7px; color: #64748b;">NIP. {{ $row['nip'] }}</div>
                </td>
                <td class="text-center">{{ number_format($row['jatah'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($row['total_ambil'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($row['sisa'], 0, ',', '.') }}</td>
                <td class="text-center" style="font-size: 8px;">{{ $row['tanggal'] }}</td>
                <td class="text-center">
                    @if($row['status_key'] === 'lunas')
                        <span class="badge badge-success">Lunas</span>
                    @elseif($row['status_key'] === 'sisa')
                        <span class="badge badge-warning">Sebagian</span>
                    @else
                        <span class="badge badge-gray">Belum</span>
                    @endif

                    <div style="font-size: 6px; margin-top: 2px; color: #475569;">
                        {{ $row['status_text'] }}
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Data pegawai tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ number_format(collect($data)->sum('jatah'), 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format(collect($data)->sum('total_ambil'), 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format(collect($data)->sum('sisa'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td class="sig-box">
                Mengetahui,<br>
                <strong>Kepala Dinas</strong>
                <div class="sig-space"></div>
                <strong><u>{{ $pejabat['nama_kadin'] }}</u></strong><br>
                NIP. {{ $pejabat['nip_kadin'] }}
            </td>
            <td width="20%"></td>
            <td class="sig-box">
                Kumurkek, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Petugas Logistik</strong>
                <div class="sig-space"></div>
                <strong><u>{{ $pejabat['nama_petugas'] }}</u></strong><br>
                NIP. {{ $pejabat['nip_petugas'] }}
            </td>
        </tr>
    </table>

    <div style="position: fixed; bottom: -10px; left: 0; font-size: 7px; color: #94a3b8;">
        * Dokumen ini dihasilkan secara otomatis oleh sistem SIM-BERAS pada {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>