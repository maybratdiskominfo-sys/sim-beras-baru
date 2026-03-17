<div>
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: linear-gradient(rgba(0, 0, 0, .7), rgba(0, 0, 0, .7)), url({{ asset('front/img/carousel-1.jpg') }}) center center no-repeat; background-size: cover;">
    <div class="container text-center py-5">
        <h1 class="display-3 text-white text-uppercase mb-3 animated slideInDown">Blog Grid</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center text-uppercase mb-0">
                <li class="breadcrumb-item"><a class="text-white" href="/">Home</a></li>
                <li class="breadcrumb-item text-primary active">Blog</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="row g-4">
                    @foreach(range(1, 4) as $item)
                    <div class="col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="blog-item bg-white border">
                            <div class="position-relative overflow-hidden">
                                <img class="img-fluid w-100" src="{{ asset('front/img/blog1.jpg') }}" alt="">
                                {{-- <img class="img-fluid w-100" src="{{ asset('front/img/blog-'.$item.'.jpg') }}" alt=""> --}}
                                {{-- <div class="bg-primary text-white position-absolute start-0 bottom-0 px-3 py-1">Judul Post Konten Terbaru</div> --}}
                            </div>
                            <div class="p-4">
                                <div class="d-flex mb-3">
                                    <small class="me-3"><i class="far fa-user text-primary me-2"></i>Admin</small>
                                    <small><i class="far fa-calendar-alt text-primary me-2"></i>01 Jan, 2026</small>
                                </div>
                                <h5 class="text-uppercase mb-3" style="color: #1e3a8a !important;">
                                    Professional Modeling Tips for Beginners
                                </h5>
                                <p>How to start your career in the fashion industry with simple steps...</p>
                                <a class="text-uppercase fw-bold" href="#">Read More <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4">
                <div class="mb-4 wow slideInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-4">
                        <h4 class="text-uppercase mb-4">Search</h4>
                        <div class="input-group">
                            <input type="text" wire:model.live.debounce.500ms="search" class="form-control p-2" placeholder="Cari artikel...">
                            <button class="btn btn-primary px-4"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>

                <div class="mb-4 wow slideInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-4">
                        <h4 class="text-uppercase mb-4">Categories</h4>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="h6 fw-semi-bold bg-dark rounded py-2 px-3 mb-2" href="#"><i class="bi bi-arrow-right me-2"></i>Fashion Show</a>
                            <a class="h6 fw-semi-bold bg-dark rounded py-2 px-3 mb-2" href="#"><i class="bi bi-arrow-right me-2"></i>Commercial</a>
                            <a class="h6 fw-semi-bold bg-dark rounded py-2 px-3 mb-2" href="#"><i class="bi bi-arrow-right me-2"></i>Editorial</a>
                        </div>
                    </div>
                </div>

                <div class="mb-5 wow slideInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-4">
                        <h4 class="text-uppercase mb-4">Recent Post</h4>
                        <div class="d-flex align-items-center mb-3">
                            <img class="img-fluid rounded" src="{{ asset('front/img/blog1.jpg') }}" style="width: 80px; height: 80px; object-fit: cover;" alt="">
                            <div class="ps-3">
                                <a href="" class="h6 d-block mb-2">How to build a portfolio</a>
                                <small>01 Jan, 2026</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <img class="img-fluid rounded" src="{{ asset('front/img/blog2.jpg') }}" style="width: 80px; height: 80px; object-fit: cover;" alt="">
                            <div class="ps-3">
                                <a href="" class="h6 d-block mb-2">Agency scouting tips</a>
                                <small>01 Jan, 2026</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>