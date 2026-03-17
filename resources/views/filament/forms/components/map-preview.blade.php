<div x-data="{
    lat: '',
    lng: '',
    init() {
        $watch('$wire.data.location_lat_long', value => {
            if (value) {
                const parts = value.split(',');
                this.lat = parts[0];
                this.lng = parts[1];
            }
        })
    }
}" class="w-full">
    <template x-if="lat">
        <div class="mt-2 overflow-hidden rounded-lg border border-gray-300">
            <img :src="`https://static-maps.yandex.ru/1.x/?lang=en_US&ll=${lng},${lat}&z=16&l=map&pt=${lng},${lat},pm2rdm`" 
                 class="w-full h-48 object-cover" />
        </div>
    </template>
</div>