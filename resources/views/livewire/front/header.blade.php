{{-- Root Element Utama --}}
<div class="header-wrapper">
    <style>
        #main-navbar {
            /* Warna Biru Gelap yang serasi dengan Footer */
            background: #061225; 
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.5s ease;
            border-bottom: 3px solid #ffc107; /* Aksen garis kuning emas */
            z-index: 1050;
        }

        /* Branding Maybrat */
        .navbar-brand h2 {
            color: #ffffff !important;
            letter-spacing: 1px;
        }
        .brand-sub {
            color: #ffc107 !important; /* Warna kuning emas untuk sub-text */
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        /* Navigasi Link */
        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 600;
            padding: 25px 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            transition: .3s;
        }

        /* Hover & Active State */
        .navbar-dark .navbar-nav .nav-link:hover,
        .navbar-dark .navbar-nav .nav-link.active {
            color: #ffc107 !important;
        }

        /* Tombol Login (Style Footer Button) */
        .btn-header-login {
            background-color: #ffc107;
            color: #061225 !important;
            border: none;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        .btn-header-login:hover {
            background-color: #ffffff;
            color: #061225 !important;
            transform: translateY(-2px);
        }

        /* Tampilan Mobile */
        @media (max-width: 991px) {
            #navbarCollapse {
                background: #061225;
                margin-top: 0;
                padding: 20px;
                border-top: 1px solid rgba(255,255,255,0.1);
            }
            .navbar-dark .navbar-nav .nav-link {
                padding: 12px 0;
                border-bottom: 1px solid rgba(255,255,255,0.05);
                text-align: center;
            }
        }
    </style>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top p-0 px-lg-5 shadow-lg" id="main-navbar">
        <div class="container-fluid">
            {{-- Logo / Brand --}}
            <a href="{{ url('/') }}" class="navbar-brand d-flex align-items-center py-2">
                <i class="bi bi-shield-lock-fill text-white fs-2 me-3"></i>
                <div class="lh-1">
                    <h2 class="mb-0 fw-bold" style="font-size: 1.4rem;">DISKOMINFO</h2>
                    <small class="brand-sub d-none d-sm-block">KABUPATEN MAYBRAT</small>
                </div>
            </a>
            
            <button type="button" class="navbar-toggler me-3 border-0 shadow-none" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navbarCollapse">
                <div class="navbar-nav">
                    <a href="{{ url('/') }}" class="nav-item nav-link {{ Request::is('/') ? 'active' : '' }}" wire:navigate>Beranda</a>
                    <a href="#!" class="nav-item nav-link" wire:navigate>Profil</a>
                    <a href="#!" class="nav-item nav-link" wire:navigate>Layanan</a>
                    
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Informasi</a>
                        <div class="dropdown-menu m-0 shadow-lg border-0 rounded-3">
                            <a href="#berita" class="dropdown-item py-2">Berita Terbaru</a>
                            <a href="#!" class="dropdown-item py-2">Pengumuman</a>
                            <a href="#!" class="dropdown-item py-2">Galeri Foto</a>
                        </div>
                    </div>
                    
                    <a href="#!" class="nav-item nav-link" wire:navigate>Kontak</a>
                </div>
            </div>

            {{-- Button Akses Admin --}}
            <div class="d-none d-lg-flex align-items-center">
                <a class="btn btn-header-login rounded-pill px-4 py-2 shadow-sm" href="http://sim-beras-baru.test/admin/login" target="_blank">
                    <i class="bi bi-person-circle me-2"></i>Login
                </a>
            </div>
        </div>
    </nav>
</div>