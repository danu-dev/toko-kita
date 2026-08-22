@extends('layouts.app')

@section('title', 'Checkout Pesanan — Toko Kita')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    fulfillmentType: 'delivery',
    deliveryFee: 8000,
    serviceFee: 1000,
    subtotal: {{ $subtotal }},
    selectedPaymentCategory: 'gateway',
    usePoints: false,
    userPoints: {{ Auth::user()->loyalty_points ?? 0 }},
    get discountAmount() {
        if (!this.usePoints || this.userPoints <= 0) return 0;
        let maxDiscount = this.subtotal + (this.fulfillmentType === 'delivery' ? this.deliveryFee : 0) + this.serviceFee - 1000;
        return Math.min(this.userPoints, Math.max(0, maxDiscount));
    },
    get total() {
        let gross = this.subtotal + (this.fulfillmentType === 'delivery' ? this.deliveryFee : 0) + this.serviceFee;
        return Math.max(1000, gross - this.discountAmount);
    }
}">

    <div class="flex items-center gap-3">
        <a href="{{ route('cart') }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-700"></i>
        </a>
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Konfirmasi & Checkout</h1>
            <p class="text-xs text-gray-500">Toko: <span class="font-bold text-[#1E2723]">{{ $store->name }}</span></p>
        </div>
    </div>

    <form action="{{ route('checkout.process', $store->id) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf

        <!-- Left Column: Fulfillment, Address with Map, Items -->
        <div class="lg:col-span-7 space-y-5">
            
            <!-- 1. Fulfillment Type (Delivery vs Pickup) -->
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-display font-bold text-sm text-[#0B5A45] uppercase tracking-wider">1. Metode Penerimaan</h3>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3.5 rounded-2xl border-2 cursor-pointer flex flex-col items-center justify-center gap-1.5 transition" :class="fulfillmentType === 'delivery' ? 'border-[#0E9F6E] bg-[#EDFDF5]' : 'border-gray-200 bg-white'">
                        <input type="radio" name="fulfillment_type" value="delivery" x-model="fulfillmentType" class="hidden">
                        <i data-lucide="truck" class="w-5 h-5 text-[#0E9F6E]"></i>
                        <span class="text-xs font-bold text-gray-800">Pesan Antar</span>
                        <span class="text-[10px] text-gray-500">Kurir Lokal (Rp 8.000)</span>
                    </label>

                    <label class="p-3.5 rounded-2xl border-2 cursor-pointer flex flex-col items-center justify-center gap-1.5 transition" :class="fulfillmentType === 'pickup' ? 'border-[#0E9F6E] bg-[#EDFDF5]' : 'border-gray-200 bg-white'">
                        <input type="radio" name="fulfillment_type" value="pickup" x-model="fulfillmentType" class="hidden">
                        <i data-lucide="store" class="w-5 h-5 text-[#0E9F6E]"></i>
                        <span class="text-xs font-bold text-gray-800">Ambil Sendiri (Pickup)</span>
                        <span class="text-[10px] text-gray-500">Di Lokasi Toko (Gratis)</span>
                    </label>
                </div>
            </div>

            <!-- 2. Delivery Address with Map Picker Link -->
            <div x-show="fulfillmentType === 'delivery'" class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-display font-bold text-sm text-[#0B5A45] uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#0E9F6E]"></i>
                        <span>2. Alamat Pengantaran</span>
                    </h3>
                    <a href="{{ route('profile') }}" class="text-xs text-[#0E9F6E] font-bold hover:underline flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Atur Pin Peta Alamat</span>
                    </a>
                </div>

                @if($addresses->isEmpty())
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 flex items-center justify-between">
                        <span>Anda belum memiliki titik alamat tersimpan di peta.</span>
                        <a href="{{ route('profile') }}" class="bg-[#0E9F6E] text-white px-3 py-1.5 rounded-xl font-bold">Pasang Pin Sekarang</a>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($addresses as $addr)
                            <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                                <input type="radio" name="address_id" value="{{ $addr->id }}" {{ $defaultAddress?->id === $addr->id ? 'checked' : '' }} class="mt-0.5 text-[#0E9F6E] focus:ring-[#0E9F6E]">
                                <div class="text-xs flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-[#1E2723]">{{ $addr->label }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono">📍 Lat: {{ round($addr->latitude, 4) }}, Lng: {{ round($addr->longitude, 4) }}</span>
                                    </div>
                                    <span class="text-gray-500">({{ $addr->recipient_name }} - {{ $addr->recipient_phone }})</span>
                                    <p class="text-gray-600 mt-1">{{ $addr->address_line }}, {{ $addr->city }}</p>
                                    @if($addr->notes)
                                        <p class="text-[11px] text-gray-400 italic">Patokan: {{ $addr->notes }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 3. Store Location (Visible if pickup) -->
            <div x-show="fulfillmentType === 'pickup'" x-cloak class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
                <h3 class="font-display font-bold text-sm text-[#0B5A45] uppercase tracking-wider">Lokasi Pengambilan</h3>
                <div class="p-3.5 bg-[#FAF8F2] rounded-2xl border border-gray-200 text-xs space-y-1">
                    <p class="font-bold text-[#1E2723]">{{ $store->name }}</p>
                    <p class="text-gray-600">{{ $store->address }}</p>
                    <p class="text-[11px] text-[#0E9F6E] font-semibold">Jam Buka: {{ $store->operational_hours }}</p>
                </div>
            </div>

            <!-- 4. Ordered Items Preview -->
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-display font-bold text-sm text-[#0B5A45] uppercase tracking-wider">Ringkasan Item Pesanan</h3>
                <div class="divide-y divide-gray-100">
                    @foreach($items as $item)
                        <div class="py-2.5 first:pt-0 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-gray-800">{{ $item->quantity }}x {{ $item->product->name }}</span>
                                @if($item->variant)
                                    <span class="text-gray-400">({{ $item->variant->name }})</span>
                                @endif
                                @if($item->notes)
                                    <p class="text-[10px] text-gray-400 italic">{{ $item->notes }}</p>
                                @endif
                            </div>
                            <span class="font-mono font-bold text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-2">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Catatan Tambahan untuk Penjual</label>
                    <input type="text" name="buyer_notes" placeholder="Contoh: Titip di satpam, jangan dikasih cabe..." class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E]">
                </div>
            </div>

        </div>

        <!-- Right Column: Loyalty Points, Payment Methods & Summary -->
        <div class="lg:col-span-5 space-y-5">
            
            <!-- Loyalty Points Redeem Card -->
            @auth
                @if(Auth::user()->loyalty_points > 0)
                    <div class="bg-[#FEF9EE] p-5 rounded-3xl border border-[#F2A93B]/40 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="award" class="w-5 h-5 text-[#F2A93B]"></i>
                                <h3 class="font-display font-bold text-sm text-amber-950">Tukar Poin Loyalitas</h3>
                            </div>
                            <span class="font-mono font-bold text-xs text-[#F2A93B]">{{ number_format(Auth::user()->loyalty_points) }} Poin</span>
                        </div>
                        
                        <label class="flex items-center justify-between p-3 rounded-2xl bg-white border border-amber-200 cursor-pointer hover:border-[#F2A93B] transition">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" name="use_points" value="1" x-model="usePoints" class="rounded text-[#F2A93B] focus:ring-[#F2A93B]">
                                <span class="text-xs font-semibold text-gray-800">Gunakan Poin untuk Diskon Belanja</span>
                            </div>
                            <span class="font-mono text-xs font-bold text-[#0E9F6E]" x-text="'-Rp ' + discountAmount.toLocaleString('id-ID')"></span>
                        </label>
                        <p class="text-[10px] text-amber-800/80">1 Poin bernilai potongan Rp 1 langsung dari total belanja Anda.</p>
                    </div>
                @endif
            @endauth

            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-display font-bold text-sm text-[#0B5A45] uppercase tracking-wider">Metode Pembayaran</h3>
                </div>

                <!-- Payment Type Tabs -->
                <div class="grid grid-cols-2 gap-2 bg-[#FAF8F2] p-1.5 rounded-2xl border border-gray-200 text-xs font-bold">
                    <button type="button" @click="selectedPaymentCategory = 'gateway'" class="py-2 rounded-xl transition flex items-center justify-center gap-1.5" :class="selectedPaymentCategory === 'gateway' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Payment Gateway</span>
                    </button>
                    <button type="button" @click="selectedPaymentCategory = 'cash'" class="py-2 rounded-xl transition flex items-center justify-center gap-1.5" :class="selectedPaymentCategory === 'cash' ? 'bg-[#F2A93B] text-[#1E2723] shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                        <span>Cash / Tunai</span>
                    </button>
                </div>
                
                <!-- Category 1: Payment Gateway Online -->
                <div class="space-y-2 text-xs" x-show="selectedPaymentCategory === 'gateway'">
                    <label class="flex items-center justify-between p-3 rounded-2xl border cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="payment_method" value="qris" checked class="text-[#0E9F6E] focus:ring-[#0E9F6E]">
                            <div>
                                <span class="font-bold text-[#1E2723] block">QRIS Payment Gateway</span>
                                <span class="text-[10px] text-gray-500">Scan via GoPay, OVO, BCA, Livin, DANA</span>
                            </div>
                        </div>
                        <span class="bg-emerald-100 text-[#0E9F6E] text-[10px] font-black px-2 py-0.5 rounded">INSTAN</span>
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-2xl border cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="payment_method" value="gopay" class="text-[#0E9F6E] focus:ring-[#0E9F6E]">
                            <span class="font-bold text-[#1E2723]">GoPay / GoPay Later</span>
                        </div>
                        <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-2xl border cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="payment_method" value="dana" class="text-[#0E9F6E] focus:ring-[#0E9F6E]">
                            <span class="font-bold text-[#1E2723]">DANA / OVO Digital Wallet</span>
                        </div>
                        <i data-lucide="smartphone" class="w-4 h-4 text-emerald-600"></i>
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-2xl border cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="payment_method" value="bca_va" class="text-[#0E9F6E] focus:ring-[#0E9F6E]">
                            <span class="font-bold text-[#1E2723]">BCA Virtual Account</span>
                        </div>
                        <span class="font-mono text-[10px] text-gray-400 font-bold">VA</span>
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-2xl border cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="payment_method" value="mandiri_va" class="text-[#0E9F6E] focus:ring-[#0E9F6E]">
                            <span class="font-bold text-[#1E2723]">Mandiri Virtual Account</span>
                        </div>
                        <span class="font-mono text-[10px] text-gray-400 font-bold">VA</span>
                    </label>
                </div>

                <!-- Category 2: Cash / COD Options -->
                <div class="space-y-2 text-xs" x-show="selectedPaymentCategory === 'cash'" x-cloak>
                    <label class="flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer border-[#F2A93B] bg-[#FEF9EE]">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="payment_method" value="cod" class="text-[#F2A93B] focus:ring-[#F2A93B]">
                            <div>
                                <span class="font-bold text-[#1E2723] block">Bayar Tunai / Cash di Tempat</span>
                                <span class="text-[10px] text-gray-600">Bayar langsung saat makanan/produk sampai atau saat diambil.</span>
                            </div>
                        </div>
                        <span class="bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded">CASH</span>
                    </label>
                </div>
            </div>

            <!-- Price Breakdown Box -->
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-display font-bold text-sm text-[#0B5A45] uppercase tracking-wider">Rincian Pembayaran</h3>
                
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal Produk</span>
                        <span class="font-mono font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Ongkos Kirim</span>
                        <span class="font-mono font-bold" x-text="fulfillmentType === 'delivery' ? 'Rp 8.000' : 'Rp 0 (Pickup)'"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Biaya Layanan Platform</span>
                        <span class="font-mono font-bold">Rp 1.000</span>
                    </div>

                    <div x-show="discountAmount > 0" class="flex justify-between text-[#0E9F6E] font-bold">
                        <span>Diskon Tukar Poin</span>
                        <span class="font-mono" x-text="'-Rp ' + discountAmount.toLocaleString('id-ID')"></span>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-between items-baseline">
                        <span class="font-display font-bold text-sm text-[#1E2723]">Total Tagihan</span>
                        <span class="font-mono font-black text-xl text-[#0B5A45]" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3.5 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition flex items-center justify-center gap-2 text-sm mt-4">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Konfirmasi & Proses Pesanan</span>
                </button>

                <p class="text-[10px] text-center text-gray-400 mt-2">
                    🛡️ Pembayaran diamankan dengan sistem escrow garansi Toko Kita.
                </p>
            </div>

        </div>
    </form>

</div>
@endsection
