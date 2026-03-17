<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- SISI KIRI: KONTROL PRESENSI --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-finger-print class="w-5 h-5 text-primary-500" />
                        <span>Presensi Kehadiran</span>
                    </div>
                </x-slot>
                
                <div class="space-y-6 py-4 text-center">
                    <div>
                        <div class="text-5xl font-black tracking-tighter text-primary-600 mb-1 font-mono" id="live-clock">
                            00:00:00
                        </div>
                        <div class="text-sm font-semibold text-gray-500 uppercase tracking-widest">
                            {{ now()->isoFormat('dddd, D MMMM YYYY') }}
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    {{-- Status Jarak --}}
                    <div id="distance-info" class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 transition-all duration-500">
                        <div class="text-xs text-gray-400 mb-2 uppercase font-bold tracking-widest">Status Lokasi</div>
                        <div id="distance-value" class="text-lg font-bold">📡 Menunggu GPS...</div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 mt-3 rounded-full overflow-hidden">
                            <div id="distance-bar" class="bg-primary-500 h-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        @if(!$this->hasAttendanceToday)
                            <x-filament::button 
                                size="xl" 
                                color="primary" 
                                icon="heroicon-o-check-circle" 
                                wire:click="checkIn"
                                wire:loading.attr="disabled"
                                class="w-full shadow-xl hover:shadow-primary-500/20 transition-all duration-300 py-4 text-lg"
                            >
                                <span wire:loading.remove>KONFIRMASI MASUK</span>
                                <span wire:loading>MEMPROSES DATA...</span>
                            </x-filament::button>
                        @elseif($this->canCheckOut)
                            <x-filament::button 
                                size="xl" 
                                color="danger" 
                                icon="heroicon-o-arrow-left-on-rectangle" 
                                wire:click="checkOut"
                                wire:loading.attr="disabled"
                                class="w-full shadow-xl hover:shadow-danger-500/20 transition-all duration-300 py-4 text-lg"
                            >
                                <span wire:loading.remove>KONFIRMASI PULANG</span>
                                <span wire:loading>MEMPROSES DATA...</span>
                            </x-filament::button>
                        @else
                            <div class="p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-check-badge class="w-12 h-12" />
                                    <span class="font-bold text-lg text-center text-balance">Presensi hari ini telah selesai tercatat.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- SISI KANAN: PETA --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">Peta Lokasi Real-time</x-slot>
                <div class="relative group">
                    <div wire:ignore id="map" 
                         class="rounded-2xl border border-gray-200 dark:border-gray-800 z-0 shadow-inner" 
                         style="height: 420px; width: 100%;">
                    </div>
                    
                    {{-- Overlay Indikator GPS --}}
                    <div id="location-status" class="absolute top-4 right-4 z-[1000] bg-white/90 dark:bg-gray-900/90 backdrop-blur-md px-3 py-1.5 rounded-full shadow-sm border border-gray-200 dark:border-gray-700 text-[10px] font-bold font-mono">
                        🔴 GPS OFFLINE
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>

    {{-- Assets & Logic --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container { font-family: inherit; }
        .leaflet-bar { border: none !important; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important; }
        #map { transition: filter 0.3s ease; }
    </style>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let map, userMarker, officeCircle, accuracyCircle;

            const officeLat = @js($this->officeLat);
            const officeLng = @js($this->officeLng);
            const radius = @js($this->radius);

            // 1. Digital Clock (High Performance)
            const clockEl = document.getElementById('live-clock');
            const updateClock = () => {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            };
            setInterval(updateClock, 1000);
            updateClock();

            // 2. Map Initialization Helper
            function initMap(lat, lng) {
                map = L.map('map', { zoomControl: false }).setView([lat, lng], 17);
                L.control.zoom({ position: 'bottomright' }).addTo(map);
                
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: 'OpenStreetMap'
                }).addTo(map);

                // Marker User
                userMarker = L.marker([lat, lng]).addTo(map);
                accuracyCircle = L.circle([lat, lng], { radius: 0, weight: 1, fillOpacity: 0.1 }).addTo(map);

                // Area Kantor
                if (officeLat && officeLng) {
                    officeCircle = L.circle([officeLat, officeLng], {
                        color: '#4f46e5',
                        fillColor: '#4f46e5',
                        fillOpacity: 0.1,
                        radius: radius,
                        dashArray: '5, 10'
                    }).addTo(map);

                    // Center point kantor
                    L.circleMarker([officeLat, officeLng], { 
                        radius: 4, 
                        color: '#ef4444', 
                        fillOpacity: 1 
                    }).addTo(map);
                }
            }

            // 3. Geolocation Engine
            if ("geolocation" in navigator) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        const { latitude: lat, longitude: lng, accuracy } = position.coords;
                        
                        // Kirim ke Livewire
                        @this.dispatch('updateLocation', { lat, lng });

                        // Update Status UI
                        const statusEl = document.getElementById('location-status');
                        statusEl.innerHTML = `🟢 GPS ACTIVE (±${Math.round(accuracy)}m)`;
                        statusEl.classList.add('text-success-600');

                        const dist = calculateDistance(lat, lng, officeLat, officeLng);
                        updateDistanceUI(dist, radius);

                        // Peta Logic
                        if (!map) {
                            initMap(lat, lng);
                        } else {
                            userMarker.setLatLng([lat, lng]);
                            accuracyCircle.setLatLng([lat, lng]).setRadius(accuracy);
                            
                            // Update warna radius jika di luar
                            if (dist > radius) {
                                officeCircle.setStyle({ color: '#ef4444', fillColor: '#ef4444' });
                            } else {
                                officeCircle.setStyle({ color: '#4f46e5', fillColor: '#4f46e5' });
                            }
                        }
                    },
                    (error) => {
                        const statusEl = document.getElementById('location-status');
                        statusEl.innerHTML = `🔴 GPS ERROR: ${error.message}`;
                        statusEl.classList.add('text-danger-600');
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }

            function updateDistanceUI(dist, maxRadius) {
                const distVal = document.getElementById('distance-value');
                const distBar = document.getElementById('distance-bar');
                const infoBox = document.getElementById('distance-info');
                
                const isInside = dist <= maxRadius;
                const percentage = Math.min((dist / (maxRadius * 2)) * 100, 100);

                distVal.innerHTML = isInside 
                    ? `<span class="text-success-600">${Math.round(dist)} Meter (Dalam Radius)</span>`
                    : `<span class="text-danger-600">${Math.round(dist)} Meter (Diluar Radius)</span>`;
                
                distBar.style.width = `${percentage}%`;
                distBar.className = isInside ? 'bg-success-500 h-full transition-all' : 'bg-danger-500 h-full transition-all';
                
                if (isInside) {
                    infoBox.classList.add('ring-2', 'ring-success-500/20');
                    infoBox.classList.remove('ring-danger-500/20');
                } else {
                    infoBox.classList.add('ring-2', 'ring-danger-500/20');
                    infoBox.classList.remove('ring-success-500/20');
                }
            }

            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3;
                const φ1 = lat1 * Math.PI/180;
                const φ2 = lat2 * Math.PI/180;
                const Δφ = (lat2-lat1) * Math.PI/180;
                const Δλ = (lon2-lon1) * Math.PI/180;
                const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                          Math.cos(φ1) * Math.cos(φ2) *
                          Math.sin(Δλ/2) * Math.sin(Δλ/2);
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            }
        });
    </script>
</x-filament-panels::page>