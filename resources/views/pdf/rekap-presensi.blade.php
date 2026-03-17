<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Presensi - {{ $header['dinas'] }}</title>
    <style>
        @page {
            margin: 0.6cm 0.8cm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        
        /* Kop Surat Resmi */
        .kop-container {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 2px;
        }
        .kop-border-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }
        .logo-box {
            width: 8%;
            vertical-align: middle;
            text-align: left;
        }
        .logo-pemda {
            width: 50px;
            height: auto;
        }
        .header-text {
            width: 92%;
            text-align: center;
            vertical-align: middle;
        }
        .header-text h1 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-text p {
            margin: 2px 0 0;
            font-size: 8px;
            font-style: italic;
        }

        /* Stats Cards */
        .stats-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px;
            text-align: center;
        }
        .stat-card .label {
            font-size: 6px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            display: block;
        }
        .stat-card .value {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Table Design Matrix */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed; /* Kunci lebar agar presisi */
        }
        .main-table th {
            background-color: #1e293b;
            color: white;
            text-align: center;
            padding: 2px 0;
            font-size: 6px;
            border: 1px solid #cbd5e1;
        }
        .main-table td {
            padding: 2px 1px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        /* Optimasi Lebar Kolom */
        .no-col { 
            width: 2px; 
            font-size: 4px; 
            padding: 2px 1px !important;
            }       /* Dipersempit */
        .tgl-col { 
            width: 2px; 
            font-size: 4px; 
            padding: 2px 1px !important;
        }      /* Dipersempit maksimal */
        .total-col { 
            width: 2px; 
            font-weight: bold; 
            background-color: #f1f5f9; 
            border: 1px solid #94a3b8 !important;
        }
        
        /* Kolom Nama Menjadi Sangat Luas */
        /* Kolom Nama Menjadi Luas dan Mendukung Baris Baru */
        .nama-col { 
            width: auto !important; 
            text-align: left !important; 
            padding-left: 2px !important;
            padding-right: 2px !important;
            /* Hapus white-space: nowrap agar teks bisa turun ke bawah */
            word-wrap: break-word; 
            vertical-align: middle;
        }

        .nama-wrapper {
            width: 100%;
            line-height: 1.1;
        }

        .nama-text {
           // font-weight: bold;
            font-size: 6px;
            display: block;
        }

        .nip-text {
            font-size: 6px;
            color: #64748b;
            display: block;
            margin-top: 1px;
        }

        /* Status & Weekend Styling */
        .bg-weekend { background-color: #fee2e2 !important; color: #b91c1c; }
        .bg-hadir { color: #166534; font-weight: bold; font-size: 7px; }
        .bg-alpa { background-color: #fef2f2; color: #ef4444; font-weight: bold; font-size: 7px; }

        /* Signature Section */
        .signature-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 33%;
            text-align: center;
            vertical-align: top;
            font-size: 9px;
        }
        .sig-space { height: 45px; position: relative; }
        .ttd-digital { height: 45px; width: auto; }
        
        .keterangan {
            font-size: 7px;
            margin-top: 5px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            display: inline-block;
        }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <table class="kop-container">
        <tr>
            <td class="logo-box">
                @if($header['logo'])
                    <img class="logo-pemda" src="{{ $header['logo'] }}">
                @endif
            </td>
            <td class="header-text">
                <h1>{{ $header['pemda'] }}</h1>
                <h2>{{ $header['dinas'] }}</h2>
                <p>{{ $header['alamat'] }}</p>
            </td>
        </tr>
    </table>
    <div class="kop-border-thin"></div>

    <div style="text-align: center; margin-bottom: 8px;">
        <h3 style="margin:0; text-decoration: underline; font-size: 11px;">LAPORAN REKAPITULASI PRESENSI HARIAN PEGAWAI</h3>
        <p style="margin:2px 0; font-size: 9px;">Periode: {{ $bulan_nama }} {{ $tahun }}</p>
    </div>

    <table class="stats-table">
        <tr>
            <td width="20%"><div class="stat-card" style="border-left: 3px solid #1e293b;"><span class="label">Total Pegawai</span><span class="value">{{ count($data) }} Org</span></div></td>
            <td width="20%"><div class="stat-card" style="border-left: 3px solid #22c55e;"><span class="label">Hari Efektif</span><span class="value">{{ $stats['total_hari_kerja'] }} Hari</span></div></td>
            <td width="20%"><div class="stat-card" style="border-left: 3px solid #3b82f6;"><span class="label">Total Hadir</span><span class="value">{{ $stats['total_hadir'] }}</span></div></td>
            <td width="20%"><div class="stat-card" style="border-left: 3px solid #f59e0b;"><span class="label">Izin/Sakit</span><span class="value">{{ $stats['total_izin'] }}</span></div></td>
            <td width="20%"><div class="stat-card" style="border-left: 3px solid #ef4444;"><span class="label">Total Alpa</span><span class="value">{{ $stats['total_alpa'] }}</span></div></td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="no-col">No</th>
                <th rowspan="2" class="nama-col">Nama Pegawai</th>
                <th colspan="{{ count($dateRange) }}">Tanggal</th>
                <th colspan="4" style="width: 64px;">Total</th>
            </tr>
            <tr>
                @foreach($dateRange as $day)
                    <th class="tgl-col" style="{{ $day['is_weekend'] ? 'background-color: #b91c1c;' : '' }}">
                        {{ $day['tgl'] }}<br>
                        <span style="font-size: 4px;">{{ substr($day['hari'], 0, 1) }}</span>
                    </th>
                @endforeach
                <th class="total-col" style="color: #166534;">H</th>
                <th class="total-col" style="color: #1d4ed8;">I</th>
                <th class="total-col" style="color: #b45309;">S</th>
                <th class="total-col" style="color: #b91c1c;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="nama-col">
                    <div class="nama-wrapper">
                        <span class="nama-text">{{ $row['nama'] }}</span>
                        
                    </div>
                </td>

                @foreach($dateRange as $day)
                    @php
                        $status = $row['details'][$day['tgl']] ?? '';
                        $class = $day['is_weekend'] ? 'bg-weekend' : '';
                        if($status == 'A') $class = 'bg-alpa';
                        if($status == 'H') $class = 'bg-hadir';
                    @endphp
                    <td class="{{ $class }}">
                        {{ $status ?: ($day['is_weekend'] ? '' : '-') }}
                    </td>
                @endforeach

                <td class="total-col">{{ $row['hadir'] }}</td>
                <td class="total-col">{{ $row['izin'] }}</td>
                <td class="total-col">{{ $row['sakit'] }}</td>
                <td class="total-col" style="color: #ef4444;">{{ $row['alpa'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="keterangan">
        <strong>Keterangan:</strong> 
        H=Hadir | I=Izin | S=Sakit | A=Alpa | Merah=Weekend | (-) = Belum Absen
    </div>

    <table class="signature-table">
        <tr>
            <td class="sig-box">
                Mengetahui,<br>
                <strong>{{ $footer['jabatan'] }}</strong>
                <div class="sig-space">
                    @if($footer['show_ttd'] && $footer['ttd_path'])
                        <img src="{{ $footer['ttd_path'] }}" class="ttd-digital">
                    @endif
                </div>
                <strong><u>{{ $footer['nama'] }}</u></strong><br>
                NIP. {{ $footer['nip'] }}
            </td>
            <td></td>
            <td class="sig-box">
                {{ $footer['kota'] }}, {{ now()->isoFormat('D MMMM Y') }}<br>
                <strong>Petugas Administrasi</strong>
                <div class="sig-space"></div>
                <strong><u>{{ $footer['petugas'] }}</u></strong><br>
                NIP. {{ $footer['nip_petugas'] }}
            </td>
        </tr>
    </table>
</body>
</html>