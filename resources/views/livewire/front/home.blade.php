<div class="main-wrapper bg-white"> <div class="container-fluid py-5 bg-light-subtle">
        <div class="container py-5">
            
            <div class="row g-4 mb-5 align-items-end">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center mb-2">
                        <span class="bg-primary p-1 me-2 rounded-1"></span>
                        <h5 class="text-primary text-uppercase fw-bold ls-wide m-0" style="font-size: 0.85rem;">Update Terbaru</h5>
                    </div>
                    <h1 class="display-5 fw-black text-dark m-0 ls-tight">Berita & Postingan</h1>
                    <p class="text-muted mt-2 fs-5">Informasi pembangunan sarana dan prasarana Kabupaten Maybrat.</p>
                </div>
            </div>

            <div class="bg-none mb-5">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="input-group border rounded-pill px-3 bg-white focus-within-shadow">
                            <span class="input-group-text bg-white border-0"><i class="fa fa-search text-primary"></i></span>
                            <input wire:model.live.debounce.400ms="search" type="text" 
                            class="form-control border-0 shadow-none bg-white text-dark placeholder:text-muted py-2 " 
                            placeholder="Cari judul informasi atau berita...">
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="input-group border-0 rounded-pill px-0 bg-transparent">
                             <span class="input-group-text bg-warning border-0 small fw-bold text-muted ps-3">OPD</span>
                             <select wire:model.live="departmentId" 
                                class="form-select border-0 shadow-none bg-transparent py-2 fw-semibold text-dark">
                                <option value="">Semua Departemen</option>
                                @isset($departments)
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                                <div class="col-lg-5 text-lg-end">
                                    <button wire:click="resetFilters" class="btn btn-white shadow-sm rounded-pill px-4 py-2 fw-bold border text-secondary transition-all">
                                        <i class="fa fa-sync-alt me-2 text-primary"></i> Reset Filter
                                    </button>
                                </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <div class="row g-4" wire:loading.class="opacity-50">
                @forelse($posts as $post)
                    <div class="col-md-6 col-lg-4" wire:key="post-{{ $post->id }}">
                        <div class="card h-100 border-0 shadow-sm rounded-5 overflow-hidden hover-card transition-all bg-white">
                            <div class="position-relative overflow-hidden" style="height: 230px;">
                                <img class="w-100 h-100 img-zoom" src="{{ $post->thumbnail ? asset('storage/' . $post->thumbnail) : asset('front/img/default-news.jpg') }}" 
                                     style="object-fit: cover;" alt="{{ $post->title }}">
                                
                               {{-- 
                               <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge glass-badge px-3 py-2 rounded-pill shadow-sm small fw-bold">
                                          {{ $post->category }}
                                    </span>
                                </div>
                                --}}
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center mb-3 text-muted" style="font-size: 0.8rem;">
                                    <span class="fw-bold text-muted bg-primary-subtle px-2 py-1 rounded-2">
                                        <i class="fa fa-user me-1"></i> {{ Str::limit($post->user->name ?? 'Umum', 23) }}
                                    </span>
                                 
                                    <span class="me-auto fw-medium "><i class="fa fa-calendar-alt text-primary me-2"></i>{{ $post->created_at->format('d M Y') }}</span>
                                
                                </div>

                                <h6 class="fw-bold text-dark mb-3 card-title-hover" style="line-height: 1.5; min-height: 3rem;">
                                    {{ Str::limit($post->title, 65) }}
                                </h6>

                                <p class="text-secondary small mb-4 lh-base">
                                    {{ Str::limit(strip_tags($post->content), 110) }}
                                </p>

                                <div class="mt-auto">
                                    <a href="{{ url('/berita/' . $post->slug) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold transition-all border-2 py-2">
                                        Selengkapnya <i class="fa fa-arrow-right ms-2 small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="bg-white p-5 rounded-5 border border-dashed mx-auto shadow-sm" style="max-width: 500px;">
                            <div class="icon-box-empty mx-auto mb-4 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fa fa-search-minus fa-3x text-muted opacity-50"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Data Tidak Ditemukan</h4>
                            <p class="text-muted mb-4">Maaf, kami tidak menemukan informasi yang sesuai dengan filter Anda.</p>
                            <button wire:click="resetFilters" class="btn btn-primary rounded-pill px-5 shadow-blue fw-bold py-2">
                                <i class="fa fa-sync-alt me-2"></i>Tampilkan Semua
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-5">
                <div class="pagination-custom-container p-2 bg-white rounded-pill shadow-sm border">
                    {{ $posts->links() }}
                </div>
            </div>

        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .main-wrapper { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
        .fw-black { font-weight: 800; }
        .ls-tight { letter-spacing: -0.02em; }
        .ls-wide { letter-spacing: 0.05em; }
        .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.08) !important; }
        .rounded-5 { border-radius: 1.8rem !important; }
        .transition-all { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
        .hover-card:hover { transform: translateY(-12px); box-shadow: 0 1.5rem 3.5rem rgba(0,0,0,.12) !important; }
        .img-zoom { transition: transform 0.6s ease; }
        .hover-card:hover .img-zoom { transform: scale(1.08); }
        .card-title-hover { transition: color 0.3s ease; }
        .hover-card:hover .card-title-hover { color: #0d6efd !important; }
        .glass-badge { background: rgba(255, 255, 255, 0.95); color: #0d6efd; backdrop-filter: blur(5px); border: 1px solid rgba(13,110,253,0.1); }
        .shadow-blue { box-shadow: 0 8px 20px -5px rgba(13, 110, 253, 0.4); }
        .btn-white { background-color: white; border-color: #eee; }
        .btn-white:hover { background-color: #f8f9fa; transform: translateY(-2px); }
        .focus-within-shadow:focus-within { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); border-color: #0d6efd !important; }
        .border-dashed { border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important; }

        /* PAGINATION CUSTOM CLEANING */
        .pagination-custom-container .pagination { margin-bottom: 0; gap: 4px; border: none; display: flex; list-style: none; padding: 0; }
        
        /* Menyembunyikan teks info bawaan jika pagination view-nya masih memunculkan */
        .pagination-custom-container nav div:first-child { display: none !important; } 
        .pagination-custom-container nav div:last-child { margin-top: 0 !important; }

        .pagination-custom-container .page-link { border: none; color: #64748b; font-weight: 700; padding: 0.5rem 1rem; border-radius: 50% !important; margin: 0 2px; background: transparent; transition: 0.2s; text-decoration: none; }
        .pagination-custom-container .page-item.active .page-link { background-color: #0d6efd !important; color: white !important; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3); }
        .pagination-custom-container .page-item.disabled .page-link { color: #cbd5e1; pointer-events: none; }
        .pagination-custom-container .page-link:hover:not(.active) { background-color: #f1f5f9; color: #0d6efd; }
    </style>

</div>