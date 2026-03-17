<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $config->judul_laporan ?? 'Rekapitulasi Konsolidasi Distribusi Beras' }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        /* Kop Surat Resmi */
        .kop-surat {
            border-bottom: 3px solid #000;
            padding-bottom: 1px;
            margin-bottom: 1px;
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
            top: 0;
            width: 70px;
            height: 70px;
        }
        .header-text h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0 0;
            font-size: 10px;
            font-style: italic;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }


        /* Header Section */
        .header-container {
            width: 100%;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .logo-box {
            width: 70px;
            float: left;
        }
        .logo-box img {
            width: 60px;
            height: auto;
        }
        .header-text {
            text-align: center;
            margin-right: 70px;
        }
        .header-text h1 {
            margin: 0;
            font-size: 14px;
            color: #1e293b;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 0;
            font-size: 15px;
            color: #1e293b;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0 0;
            font-size: 9px;
            color: #475569;
        }

        /* Stats Cards */
        .stats-table {
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 10px 0; /* Memberi jarak antar kartu */
            border-collapse: separate;
        }
        .stat-card-wrap {
            padding: 0 10px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
        }
        .stat-card .label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            display: block;
        }
        .stat-card .value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Table Design */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .main-table th {
            background-color: #f8fafc;
            color: #1e293b;
            text-align: center;
            padding: 8px 4px;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #e2e8f0;
        }
        .main-table td {
            padding: 6px 4px;
            border: 1px solid #e2e8f0;
        }
        .main-table tr:nth-child(even) { background-color: #f8fafc; }

        /* Footer Table */
        .main-table tfoot td {
            background-color: #f1f5f9;
            font-weight: 800;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            padding: 10px 6px;
        }

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
            height: 65px;
            vertical-align: middle;
        }
        .sig-space img {
            max-height: 60px;
            width: auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="kop-surat clearfix">
        {{-- <div class="logo-box"> --}}
            {{-- Cek apakah variabel $config ada DAN properti logo tidak kosong --}}
            @if(isset($config) && $config->logo)
                <img class="logo-pemda" src="{{ public_path('storage/' .$config->logo) }}" alt="Logo">
            @else
                <div style="width:50px; height:50px; border:1px solid #ddd; font-size:8px; text-align:center; padding-top:15px;">LOGO</div>
            @endif
        {{-- </div> --}}
        <div class="header-text">
            {{-- Properti opsional ditandai dengan ?? agar jika kosong tidak error --}}
            <h1>{{ $config->nama_pemda ?? 'PEMERINTAH KABUPATEN MAYBRAT' }}</h1>
            <h2>{{ $config->judul_laporan ?? 'REKAPITULASI KONSOLIDASI DISTRIBUSI BERAS' }}</h2>
            <p>{{ $config->sub_judul ?? 'Seluruh Dinas / SKPD / Unit Kerja Lingkup Kabupaten Maybrat' }}</p>
            
            {{-- Cek alamat secara aman --}}
            @if(isset($config->alamat) && $config->alamat)
                <p style="font-style: italic;">{{ $config->alamat }}</p>
            @endif
        </div>
    </div>

     <div class="kop-border-thin"></div>

    <div class="text-center" style="margin-bottom: 15px;">
        <h3 style="margin:0; text-decoration: underline; font-size: 13px;">LAPORAN PENYALURAN UNIT KERJA</h3>
        <p style="margin:4px 0; font-size: 10px;">
        <p style="margin:4px 0; font-size: 10px; font-weight: bold;"> 
            Periode: {{ $labelPeriode }}
        </p>

        </p>
    </div>

    @php
        $g_masuk = 0;
        $g_keluar = 0;
        foreach($departments as $dept) {
            $g_masuk += ($dept->total_masuk ?? 0);
            $g_keluar += ($dept->total_keluar ?? 0);
        }
        $g_sisa = $g_masuk - $g_keluar;
    @endphp

    <table class="stats-table">
        <tr>
            <td width="33.3%" class="stat-card-wrap">
                <div class="stat-card" style="border-left: 3px solid #3b82f6;">
                    <span class="label">Total Stok Kabupaten Maybrat</span>
                    <span class="value">{{ number_format($g_masuk, 0, ',', '.') }} Kg</span>
                </div>
            </td>
            <td width="33.3%" class="stat-card-wrap">
                <div class="stat-card" style="border-left: 3px solid #10b981;">
                    <span class="label">Total Penyaluran</span>
                    <span class="value">{{ number_format($g_keluar, 0, ',', '.') }} Kg</span>
                </div>
            </td>
            <td width="33.3%" class="stat-card-wrap">
                <div class="stat-card" style="border-left: 3px solid #f59e0b;">
                    <span class="label">Total Sisa Saldo</span>
                    <span class="value">{{ number_format($g_sisa, 0, ',', '.') }} Kg</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Dinas / Unit Kerja</th>
                <th width="10%">Pegawai</th>
                <th width="15%">Stok (Kg)</th>
                <th width="15%">Penyaluran (Kg)</th>
                <th width="%">Sisa Saldo (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $index => $dept)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $dept->name }}</td>
                <td class="text-center">{{ $dept->employees_count ?? 0 }} Org</td>
                <td class="text-center">{{ number_format($dept->total_masuk ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($dept->total_keluar ?? 0, 0, ',', '.') }}</td>
                <td class="text-center font-bold" style="color: #0f172a;">
                    {{ number_format(($dept->total_masuk ?? 0) - ($dept->total_keluar ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px;">Data unit kerja tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
            $totalPegawai = $departments->sum('employees_count');
            @endphp
            <tr>
                <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ number_format($totalPegawai, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($g_masuk, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($g_keluar, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($g_sisa, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
    <tr>
        <td class="sig-box">
            Mengetahui,<br>
            <strong>{{ $config->jabatan_kiri ?? 'Sekretaris Daerah' }}</strong>
            <div class="sig-space">
                @php
                        // Mengambil path murni dari sistem operasi
                        $ttdPath = $config->ttd_kiri ? public_path('storage/' . $config->ttd_kiri) : null;
                    @endphp

                    @if(($config->aktifkan_ttd_digital ?? false) && $config->ttd_kiri && file_exists($ttdPath))
                        <img src="{{ $ttdPath }}" style="height: 60px; width: auto;">
                    @else
                        {{-- Jika file tidak ada atau toggle mati, tampilkan ruang kosong agar layout tidak berantakan --}}
                        <div style="height: 60px;"></div>
                @endif
            </div>
            <strong><u>{{ $config->nama_kiri ?? '..........................' }}</u></strong><br>
            NIP. {{ $config->nip_kiri ?? '..........................' }}
        </td>
        
        <td width="20%"></td>

        <td class="sig-box">
            Kumurkek, {{ $date }}<br>
            <strong>{{ $config->jabatan_kanan ?? 'Admin Logistik' }}</strong>
            <div class="sig-space">
            @php
                // Mengambil path murni dari sistem operasi
                $ttdPath = $config->ttd_kanan ? public_path('storage/' . $config->ttd_kanan) : null;
            @endphp

            @if(($config->aktifkan_ttd_digital ?? false) && $config->ttd_kanan && file_exists($ttdPath))
                <img src="{{ $ttdPath }}" style="height: 60px; width: auto;">
            @else
                {{-- Jika file tidak ada atau toggle mati, tampilkan ruang kosong agar layout tidak berantakan --}}
                <div style="height: 60px;"></div>
            @endif
            </div>
            <strong><u>{{ $config->nama_kanan ?? '..........................' }}</u></strong><br>
            NIP. {{ $config->nip_kanan ?? '..........................' }}
        </td>
    </tr>
    </table>

    <div style="position: fixed; bottom: -10px; left: 0; font-size: 7px; color: #94a3b8;">
        * Dokumen ini dihasilkan secara otomatis oleh sistem pada {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
