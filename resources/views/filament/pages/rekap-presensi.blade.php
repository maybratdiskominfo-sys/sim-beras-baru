<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="print" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Pilih Bulan --}}
                <div>
                    <label class="text-sm font-medium">Bulan</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="bulan">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Pilih Tahun --}}
                <div>
                    <label class="text-sm font-medium">Tahun</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="tahun">
                            @foreach(range(now()->year - 2, now()->year) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Pilih Dinas (Hanya untuk Super Admin) --}}
                @if(auth()->user()->hasRole('super_admin'))
                <div>
                    <label class="text-sm font-medium">Dinas/OPD</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="department_id">
                            <option value="">-- Pilih Dinas --</option>
                            @foreach(\App\Models\Department::all() as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                @endif
            </div>

            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-m-printer" color="primary">
                    Download Rekap PDF
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>