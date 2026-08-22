@extends('layouts.app')

@section('title', 'Profil & Alamat Tersimpan — Toko Kita')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    addressLat: -7.946714,
    addressLng: 112.615668,
    addressMap: null,
    addressMarker: null,
    initAddressMap() {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition((pos) => {
                this.addressLat = pos.coords.latitude;
                this.addressLng = pos.coords.longitude;
                if (this.addressMap) {
                    this.addressMap.setView([this.addressLat, this.addressLng], 15);
                    this.addressMarker.setLatLng([this.addressLat, this.addressLng]);
                }
            });
        }

        this.addressMap = L.map('address-picker-map').setView([this.addressLat, this.addressLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(this.addressMap);

        const pinIcon = L.divIcon({
            className: 'custom-pin',
            html: `<div class='w-8 h-8 rounded-full bg-red-600 border-2 border-white shadow-xl flex items-center justify-center text-white text-xs font-bold'>📍</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });

        this.addressMarker = L.marker([this.addressLat, this.addressLng], {
            draggable: true,
            icon: pinIcon
        }).addTo(this.addressMap);

        this.addressMarker.on('dragend', (e) => {
            const pos = e.target.getLatLng();
            this.addressLat = pos.lat;
            this.addressLng = pos.lng;
        });

        this.addressMap.on('click', (e) => {
            this.addressLat = e.latlng.lat;
            this.addressLng = e.latlng.lng;
            this.addressMarker.setLatLng(e.latlng);
        });
    }
}" x-init="$nextTick(() => initAddressMap())">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Profil & Titik Alamat Saya</h1>
        <p class="text-xs text-gray-500">Kelola informasi kontak dan tentukan titik alamat pengantaran langsung via peta interaktif.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- User Information Card -->
        <div class="md:col-span-5 space-y-5">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-[#0E9F6E] text-white font-bold flex items-center justify-center text-xl shadow-md shadow-[#0E9F6E]/20">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-[#1E2723]">{{ $user->name }}</h3>
                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="bg-[#FAF8F2] p-3.5 rounded-2xl border border-gray-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-400 block text-[10px]">Poin Loyalitas</span>
                        <span class="font-display font-bold text-[#F2A93B] text-base">{{ number_format($user->loyalty_points) }} Poin</span>
                    </div>
                    <i data-lucide="award" class="w-6 h-6 text-[#F2A93B]"></i>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-3 pt-2">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-700 mb-1">No. WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E]">
                    </div>
                    <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white py-2 rounded-xl text-xs font-bold transition">
                        Simpan Profil
                    </button>
                </form>
            </div>
        </div>

        <!-- Saved Addresses Section with Map Pinning -->
        <div class="md:col-span-7 space-y-5">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-display font-bold text-base text-[#0B5A45]">Daftar Alamat Pengiriman</h3>
                </div>

                <div class="space-y-3">
                    @forelse($addresses as $addr)
                        <div class="p-4 rounded-2xl border {{ $addr->is_default ? 'border-[#0E9F6E] bg-[#EDFDF5]' : 'border-gray-100 bg-[#FAF8F2]' }} text-xs space-y-1 relative">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#1E2723]">{{ $addr->label }}</span>
                                <div class="flex items-center gap-2">
                                    @if($addr->is_default)
                                        <span class="bg-[#0E9F6E] text-white text-[10px] font-bold px-2 py-0.5 rounded-full">UTAMA</span>
                                    @endif
                                    <button type="button" @click="$store.userLocation.setLocation({{ $addr->latitude }}, {{ $addr->longitude }}, '{{ $addr->address_line }}')" class="bg-white text-[#0E9F6E] font-bold px-2.5 py-1 rounded-lg border border-emerald-200 hover:bg-emerald-50 transition text-[10px]">
                                        Gunakan Titik Ini
                                    </button>
                                </div>
                            </div>
                            <p class="text-gray-700 font-semibold">{{ $addr->recipient_name }} ({{ $addr->recipient_phone }})</p>
                            <p class="text-gray-600">{{ $addr->address_line }}, {{ $addr->city }}</p>
                            <p class="text-[10px] text-gray-400 font-mono">📍 Koordinat: {{ $addr->latitude }}, {{ $addr->longitude }}</p>
                            @if($addr->notes)
                                <p class="text-[11px] text-gray-400 italic">"{{ $addr->notes }}"</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-4">Belum ada alamat tersimpan.</p>
                    @endforelse
                </div>

                <!-- Add New Address with Interactive Map Pin Form -->
                <div class="pt-4 border-t border-gray-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-xs text-[#0B5A45] uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#0E9F6E]"></i>
                            <span>Tambah Alamat via Peta (Geser / Klik Pin)</span>
                        </h4>
                    </div>

                    <!-- Map Picker Canvas -->
                    <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-xs">
                        <div id="address-picker-map" class="h-44 w-full z-0"></div>
                    </div>

                    <form action="{{ route('addresses.store') }}" method="POST" class="space-y-3 text-xs">
                        @csrf
                        <input type="hidden" name="latitude" :value="addressLat">
                        <input type="hidden" name="longitude" :value="addressLng">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-gray-600 mb-1">Label Alamat</label>
                                <input type="text" name="label" required placeholder="Rumah / Kantor / Kos" class="w-full px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Kota</label>
                                <input type="text" name="city" value="Malang" required class="w-full px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-gray-600 mb-1">Nama Penerima</label>
                                <input type="text" name="recipient_name" required placeholder="Nama" class="w-full px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Nomor HP</label>
                                <input type="text" name="recipient_phone" required placeholder="08..." class="w-full px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Detail Alamat / Nama Jalan</label>
                            <textarea name="address_line" rows="2" required placeholder="Nama jalan, nomor rumah, gang..." class="w-full px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Patokan Lokasi (Opsional)</label>
                            <input type="text" name="notes" placeholder="Pagar hitam, samping warung madura..." class="w-full px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_default" value="1" class="text-[#0E9F6E] rounded">
                            <span>Jadikan Alamat Utama</span>
                        </label>
                        <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white py-2.5 rounded-xl font-bold transition flex items-center justify-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Titik Alamat Ini</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection
