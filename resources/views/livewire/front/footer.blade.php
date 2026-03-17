<div>
    <style>
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1; /* Di belakang konten */
        }
        .footer-content {
            position: relative;
            z-index: 2; /* Di depan partikel */
            pointer-events: none; /* Agar klik tetap tembus ke partikel jika ingin interaktif */
        }
        .footer-content a, .footer-content button {
            pointer-events: auto; /* Tombol & Link tetap bisa diklik */
        }
        .footer-relative {
            position: relative;
            background-color: #111; /* Warna dasar footer */
            overflow: hidden;
        }
    </style>

    <div class="container-fluid footer-relative text-light py-5 wow fadeIn" data-wow-delay="0.1s">
        <div id="particles-js"></div>

        <div class="container text-center py-5 footer-content">
            <a href="{{ url('/') }}">
                <h1 class="display-4 mb-3 text-white text-uppercase">KABUPATEN MAYBRAT</h1>
            </a>
            <div class="d-flex justify-content-center mb-4">
                <a class="btn btn-lg-square btn-outline-primary border-2 m-1" href="#"><i class="fab fa-facebook-f"></i></a>
                <a class="btn btn-lg-square btn-outline-primary border-2 m-1" href="#"><i class="fab fa-x-twitter"></i></a>
            </div>
            <p>&copy; {{ date('Y') }} Poseify. All Right Reserved.</p>
        </div>
    </div>
</div>