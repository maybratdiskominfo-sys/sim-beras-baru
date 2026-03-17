<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Form Cetak Rekapitulasi
            </x-slot>
            <x-slot name="description">
                Pilih parameter di bawah ini untuk menghasilkan laporan distribusi beras.
            </x-slot>

            <form wire:submit.prevent="print">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">

                    {{-- Filter Dinas (Hanya untuk SuperAdmin) --}}
                    @if(auth()->user()->hasRole('super_admin'))
                    <div class="space-y-2">
                        <label class="block text-sm font-medium leading-6 text-gray-950 dark:text-white">Pilih Dinas / SKPD</label>
                        <select wire:model.live="department_id" required class="block w-full rounded-lg border-none bg-white shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20">
                            <option value="">-- Pilih Dinas --</option>
                            @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    {{-- Tampilan untuk Admin Dinas --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium leading-6 text-gray-500 dark:text-gray-400">Instansi / Unit Kerja</label>
                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-gray-950/5">
                            {{ auth()->user()->department?->name ?? 'Dinas Tidak Terdeteksi' }}
                        </div>
                        <input type="hidden" wire:model="department_id">
                    </div>
                    @endif

                    {{-- Select Tahap --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium leading-6 text-gray-950 dark:text-white">Tahap Pengambilan</label>
                        <select wire:model="tahap" required class="block w-full rounded-lg border-none bg-white shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20">
                            @if(empty($this->tahapList) || count($this->tahapList) == 0)
                                <option value="">Belum ada jatah stok</option>
                            @else
                                <option value="">Pilih Tahap</option>
                                @foreach($this->tahapList as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Select Bulan --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium leading-6 text-gray-950 dark:text-white">Bulan</label>
                        <select wire:model="bulan" required class="block w-full rounded-lg border-none bg-white shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Select Tahun --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium leading-6 text-gray-950 dark:text-white">Tahun</label>
                        <select wire:model="tahun" required class="block w-full rounded-lg border-none bg-white shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20">
                            @foreach(range(now()->year - 2, now()->year + 3) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <x-filament::button type="submit" icon="heroicon-m-printer" size="md" wire:target="print">
                        Cetak Rekapitulasi (PDF)
                    </x-filament::button>

                    {{-- <div wire:loading.delay wire:target="print" class="flex items-center text-sm text-primary-600 dark:text-primary-400 font-medium italic">
                        <x-filament::loading-indicator class="h-5 w-5 mr-2" />
                        Sedang mengolah data dan membuat PDF...
                    </div> --}}
                </div>
            </form>
        </x-filament::section>

        {{-- Info Box --}}
        <div class="bg-primary-50 border-l-4 border-primary-500 p-4 dark:bg-gray-800/50 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-s-information-circle class="h-5 w-5 text-primary-500" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-primary-800 dark:text-primary-300">Informasi Laporan</h3>
                    <div class="mt-1 text-sm text-primary-700 dark:text-primary-400">
                        @if(auth()->user()->hasRole('super_admin'))
                            <p>Sebagai <strong>SuperAdmin</strong>, Anda dapat memantau progres distribusi dari semua dinas. Pastikan data <strong>Stok Beras</strong> sudah diinput agar Tahap muncul pada pilihan.</p>
                        @else
                            <p>Laporan ini akan mencakup seluruh pegawai pada <strong>{{ auth()->user()->department?->name }}</strong>. Pegawai yang baru mengambil sebagian jatah akan otomatis muncul dengan status <span class="font-bold underline text-amber-600">Sebagian</span> pada hasil cetakan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
