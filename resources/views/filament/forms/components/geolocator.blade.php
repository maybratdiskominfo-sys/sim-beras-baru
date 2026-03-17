<div x-data="{
    lat: '',
    lng: '',
    loading: true,
    error: '',

    init() {
        this.getLocation();
    },

    getLocation() {
        this.loading = true;
        this.error = '';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.lat = position.coords.latitude;
                    this.lng = position.coords.longitude;
                    const latlong = this.lat + ',' + this.lng;
                    
                    // Mengisi state Livewire secara langsung (lebih stabil)
                    $wire.set('data.location_lat_long', latlong);
                    
                    // Juga update Map Picker utama agar marker sinkron
                    $wire.set('data.location', {
                        lat: parseFloat(this.lat),
                        lng: parseFloat(this.lng)
                    });

                    this.loading = false;
                },
                (err) => {
                    this.loading = false;
                    this.error = 'Gagal mengambil lokasi. Pastikan GPS aktif.';
                    console.error('GPS Error:', err);
                },
                { 
                    enableHighAccuracy: true, 
                    timeout: 10000, 
                    maximumAge: 0 
                }
            );
        } else {
            this.loading = false;
            this.error = 'Browser tidak mendukung GPS.';
        }
    }
}" class="w-full">
    
    <div class="flex items-center gap-2 mb-2">
        <template x-if="loading">
            <div class="flex items-center text-sm text-blue-600 animate-pulse">
                <svg class="w-4 h-4 mr-2 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mengunci posisi GPS...
            </div>
        </template>

        <template x-if="!loading && lat">
            <div class="text-sm text-green-600 flex items-center font-medium">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                Lokasi Berhasil Dikunci
            </div>
        </template>
    </div>

    <template x-if="error">
        <div class="text-xs text-red-500 bg-red-50 p-2 rounded border border-red-100">
            <span x-text="error"></span>
        </div>
    </template>

    <template x-if="lat">
        <div class="mt-2 overflow-hidden rounded-lg border border-gray-300 shadow-sm">
            <img :src="`https://static-maps.yandex.ru/1.x/?lang=en_US&ll=${lng},${lat}&z=16&l=map&pt=${lng},${lat},pm2rdm&size=600,200`" 
                 class="w-full h-32 object-cover" />
        </div>
    </template>
</div>