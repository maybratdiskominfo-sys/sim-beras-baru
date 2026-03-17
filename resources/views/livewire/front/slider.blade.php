<div>
    {{-- Slider Carousel Start --}}
    <div id="header-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            @php
                // Data slider bisa dipindahkan ke Class Slider.php nanti agar lebih profesional
                $sliders = [
                    [
                        'image' => 'maybrat1.png',
                        'subtitle' => 'Welcome to Portal',
                        'title' => 'Diskominfo Maybrat',
                        'desc' => 'Transformasi Digital Menuju Pelayanan Publik yang Transparan dan Akuntabel.',
                        'link' => '#',
                    ],
                    [
                        'image' => 'maybrat2.jpg',
                        'subtitle' => 'Informasi Publik',
                        'title' => 'Satu Data Maybrat',
                        'desc' => 'Mengintegrasikan seluruh data daerah dalam satu platform yang mudah diakses.',
                        'link' => '#',
                    ]
                ];
            @endphp

            @foreach($sliders as $index => $slider)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <img class="w-100" src="{{ asset('front/img/' . $slider['image']) }}" alt="{{ $slider['title'] }}" style="object-fit: cover; height: 90vh;">
                <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                    <div class="title mx-5 px-5 animated slideInDown">
                        <div class="title-center">
                            <h5 class="text-uppercase text-primary mb-3" style="letter-spacing: 3px;">{{ $slider['subtitle'] }}</h5>
                            <h1 class="display-1 text-white mb-4">{{ $slider['title'] }}</h1>
                        </div>
                    </div>
                    <p class="fs-5 mb-5 animated slideInDown px-4 text-center" style="max-width: 800px;">
                        {{ $slider['desc'] }}
                    </p>
                    <a href="{{ $slider['link'] }}" class="btn btn-primary py-3 px-5 animated slideInUp rounded-pill">
                        Explore More <i class="ms-2 bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Kontrol Navigasi --}}
        <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    {{-- Slider Carousel End --}}
</div>