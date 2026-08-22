<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\Review;
use App\Models\Banner;
use App\Models\PlatformSetting;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);

        // 2. Settings
        PlatformSetting::set('platform_commission_percent', '5'); // 5% komisi
        PlatformSetting::set('platform_name', 'Toko Kita');
        PlatformSetting::set('support_phone', '081234567890');

        // 3. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@tokokita.id'],
            [
                'name' => 'Admin Toko Kita',
                'phone' => '081122334455',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        // 4. Categories
        $categoriesData = [
            ['name' => 'Kuliner & Makanan', 'slug' => 'kuliner-makanan', 'icon' => 'utensils'],
            ['name' => 'Warung Kelontong & Sembako', 'slug' => 'sembako-kelontong', 'icon' => 'shopping-bag'],
            ['name' => 'Minuman & Kopi', 'slug' => 'minuman-kopi', 'icon' => 'coffee'],
            ['name' => 'Kue & Camilan', 'slug' => 'kue-camilan', 'icon' => 'cookie'],
            ['name' => 'Kerajinan & Kriya', 'slug' => 'kerajinan-kriya', 'icon' => 'sparkles'],
            ['name' => 'Sayur & Buah Segar', 'slug' => 'sayur-buah', 'icon' => 'apple'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::create($cat);
        }

        // 5. Banners
        Banner::create([
            'title' => 'Dukung UMKM Lokal Malang Raya',
            'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
            'badge_text' => 'SUPER PROMO',
            'link' => '/#explore',
            'order_position' => 1,
        ]);
        Banner::create([
            'title' => 'Gratis Ongkir Kuliner Terdekat',
            'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80',
            'badge_text' => 'HEMAT 100%',
            'link' => '/#explore',
            'order_position' => 2,
        ]);

        // 6. Sellers & Stores
        // Seller 1: Warung Bu Siti (Approved)
        $seller1User = User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'seller@tokokita.id',
            'phone' => '081298765432',
            'password' => Hash::make('password'),
        ]);
        $seller1User->syncRoles([$sellerRole]);

        $store1 = Store::create([
            'user_id' => $seller1User->id,
            'category_id' => $categories['kuliner-makanan']->id,
            'name' => 'Warung Nasi Bu Siti',
            'slug' => 'warung-nasi-bu-siti',
            'description' => 'Masakan rumahan khas Jawa Timur legendaris. Rawon Nguling, Nasi Pecel Madiun, dan Ayam Geprek Sambal Korek.',
            'logo' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=200&q=80',
            'banner' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1000&q=80',
            'phone' => '081298765432',
            'address' => 'Jl. Soekarno Hatta No. 45, Lowokwaru',
            'city' => 'Malang',
            'latitude' => -7.946714,
            'longitude' => 112.615668,
            'operational_hours' => '07:00 - 20:00',
            'nib_number' => '9120001234567',
            'status' => 'approved',
            'is_open' => true,
            'rating' => 4.9,
            'total_reviews' => 48,
        ]);

        Wallet::create([
            'store_id' => $store1->id,
            'balance' => 450000,
            'held_balance' => 125000,
        ]);

        // Products for Store 1
        $p1 = Product::create([
            'store_id' => $store1->id,
            'category_id' => $categories['kuliner-makanan']->id,
            'name' => 'Rawon Daging Sapi Spesial',
            'slug' => 'rawon-daging-sapi-spesial',
            'description' => 'Rawon khas Jawa Timur dengan kuah kluwek hitam pekat gurih, potongan daging sapi empuk, sambal terasi, tauge pendek, dan telur asin.',
            'price' => 28000,
            'compare_at_price' => 32000,
            'stock' => 50,
            'unit' => 'porsi',
            'image' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=600&q=80',
            'is_active' => true,
            'is_featured' => true,
            'rating' => 4.9,
            'total_sales' => 180,
        ]);

        ProductVariant::create(['product_id' => $p1->id, 'name' => 'Biasa (Tanpa Telur Asin)', 'price_modifier' => 0, 'stock' => 30]);
        ProductVariant::create(['product_id' => $p1->id, 'name' => 'Komplit (+ Telur Asin & Empal)', 'price_modifier' => 7000, 'stock' => 20]);

        $p2 = Product::create([
            'store_id' => $store1->id,
            'category_id' => $categories['kuliner-makanan']->id,
            'name' => 'Nasi Pecel Madiun Pincuk',
            'slug' => 'nasi-pecel-madiun-pincuk',
            'description' => 'Nasi pecel dengan aneka sayuran segar disiram sambal kacang medok pedas manis gurih, lengkap dengan rempeyek renyah.',
            'price' => 16000,
            'stock' => 40,
            'unit' => 'porsi',
            'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80',
            'is_active' => true,
            'is_featured' => true,
            'rating' => 4.8,
            'total_sales' => 140,
        ]);

        // Seller 2: Kopi Tjap Kendedes
        $seller2User = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.kopi@tokokita.id',
            'phone' => '085712345678',
            'password' => Hash::make('password'),
        ]);
        $seller2User->syncRoles([$sellerRole]);

        $store2 = Store::create([
            'user_id' => $seller2User->id,
            'category_id' => $categories['minuman-kopi']->id,
            'name' => 'Kopi Tjap Kendedes',
            'slug' => 'kopi-tjap-kendedes',
            'description' => 'Roastery & kedai kopi lokal biji asli Dampit & Arjuno Malang. Racikan manual brew dan es kopi susu gula aren legit.',
            'logo' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=200&q=80',
            'banner' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1000&q=80',
            'phone' => '085712345678',
            'address' => 'Jl. Ijen Boulevard No. 12, Klojen',
            'city' => 'Malang',
            'latitude' => -7.972341,
            'longitude' => 112.623412,
            'operational_hours' => '09:00 - 23:00',
            'nib_number' => '9120007654321',
            'status' => 'approved',
            'is_open' => true,
            'rating' => 4.8,
            'total_reviews' => 32,
        ]);

        Wallet::create([
            'store_id' => $store2->id,
            'balance' => 280000,
            'held_balance' => 45000,
        ]);

        Product::create([
            'store_id' => $store2->id,
            'category_id' => $categories['minuman-kopi']->id,
            'name' => 'Es Kopi Susu Aren Arema',
            'slug' => 'es-kopi-susu-aren-arema',
            'description' => 'Double espresso robusta Dampit dipadu susu segar creamy dan gula aren organik Jawa asli.',
            'price' => 18000,
            'stock' => 100,
            'unit' => 'cup',
            'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=600&q=80',
            'is_active' => true,
            'is_featured' => true,
            'rating' => 4.9,
            'total_sales' => 320,
        ]);

        Product::create([
            'store_id' => $store2->id,
            'category_id' => $categories['minuman-kopi']->id,
            'name' => 'Biji Kopi Arabika Arjuno (250g)',
            'slug' => 'biji-kopi-arabika-arjuno-250g',
            'description' => 'Single origin Arabika lereng Gunung Arjuno, tasting notes: brown sugar, green apple, floral.',
            'price' => 65000,
            'stock' => 25,
            'unit' => 'pack',
            'image' => 'https://images.unsplash.com/photo-1587734195503-904fca47e0e9?auto=format&fit=crop&w=600&q=80',
            'is_active' => true,
            'is_featured' => false,
            'rating' => 4.8,
            'total_sales' => 45,
        ]);

        // Seller 3: Toko Kelontong Berkah Jaya (Pending Verification)
        $seller3User = User::create([
            'name' => 'Ahmad Hidayat',
            'email' => 'ahmad.sembako@tokokita.id',
            'phone' => '082133445566',
            'password' => Hash::make('password'),
        ]);
        $seller3User->syncRoles([$sellerRole]);

        $store3 = Store::create([
            'user_id' => $seller3User->id,
            'category_id' => $categories['sembako-kelontong']->id,
            'name' => 'Toko Sembako Berkah Jaya',
            'slug' => 'toko-sembako-berkah-jaya',
            'description' => 'Pusat sembako murah, beras rojo lele, minyak goreng, gula, dan telur segar langsung dari peternak.',
            'logo' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=200&q=80',
            'banner' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=1000&q=80',
            'phone' => '082133445566',
            'address' => 'Jl. Danau Toba Raya Blok G1 No. 5, Sawojajar',
            'city' => 'Malang',
            'latitude' => -7.978210,
            'longitude' => 112.658219,
            'operational_hours' => '06:00 - 21:00',
            'nib_number' => '9120009988776',
            'status' => 'pending', // Antrean verifikasi admin!
            'is_open' => true,
            'rating' => 5.0,
            'total_reviews' => 0,
        ]);

        Wallet::create([
            'store_id' => $store3->id,
            'balance' => 0,
            'held_balance' => 0,
        ]);

        // 7. Buyer User
        $buyerUser = User::create([
            'name' => 'Dimas Aditya',
            'email' => 'buyer@tokokita.id',
            'phone' => '087812349999',
            'password' => Hash::make('password'),
            'loyalty_points' => 1250,
        ]);
        $buyerUser->syncRoles([$buyerRole]);

        $address = Address::create([
            'user_id' => $buyerUser->id,
            'label' => 'Rumah',
            'recipient_name' => 'Dimas Aditya',
            'recipient_phone' => '087812349999',
            'address_line' => 'Jl. Bunga Coklat No. 18B, Jatimulyo',
            'city' => 'Malang',
            'district' => 'Lowokwaru',
            'postal_code' => '65141',
            'latitude' => -7.951234,
            'longitude' => 112.618999,
            'notes' => 'Pagar hitam samping pos satpam, titip di teras.',
            'is_default' => true,
        ]);

        // 8. Sample Active Orders
        // Order 1: Status DIPROSES (Testing Status Pulse)
        $order1 = Order::create([
            'order_number' => 'TK-20260822-001',
            'buyer_id' => $buyerUser->id,
            'store_id' => $store1->id,
            'address_id' => $address->id,
            'fulfillment_type' => 'delivery',
            'subtotal' => 44000,
            'delivery_fee' => 8000,
            'service_fee' => 1000,
            'discount_amount' => 0,
            'commission_fee' => 2200, // 5%
            'seller_earnings' => 41800,
            'total' => 53000,
            'status' => 'diproses',
            'confirmed_at' => now()->subMinutes(15),
            'buyer_notes' => 'Tolong sambalnya dipisah ya bu.',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $p1->id,
            'product_name' => 'Rawon Daging Sapi Spesial',
            'variant_name' => 'Komplit (+ Telur Asin & Empal)',
            'price' => 35000,
            'quantity' => 1,
            'subtotal' => 35000,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => null,
            'product_name' => 'Kerupuk Udang',
            'variant_name' => null,
            'price' => 9000,
            'quantity' => 1,
            'subtotal' => 9000,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'from_status' => null,
            'to_status' => 'menunggu_konfirmasi',
            'changed_by' => $buyerUser->id,
            'notes' => 'Pesanan berhasil dibuat & menunggu konfirmasi penjual.',
            'created_at' => now()->subMinutes(20),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'from_status' => 'menunggu_konfirmasi',
            'to_status' => 'diproses',
            'changed_by' => $seller1User->id,
            'notes' => 'Penjual telah menerima pesanan dan sedang menyiapkan makanan.',
            'created_at' => now()->subMinutes(15),
        ]);

        Payment::create([
            'order_id' => $order1->id,
            'payment_code' => 'QRIS-' . Str::upper(Str::random(8)),
            'method' => 'qris',
            'amount' => 53000,
            'status' => 'paid',
            'transaction_reference' => 'TRX-QRIS-' . rand(100000, 999999),
            'paid_at' => now()->subMinutes(19),
        ]);

        // Order 2: Status SELESAI (With Review)
        $order2 = Order::create([
            'order_number' => 'TK-20260821-098',
            'buyer_id' => $buyerUser->id,
            'store_id' => $store1->id,
            'address_id' => $address->id,
            'fulfillment_type' => 'delivery',
            'subtotal' => 32000,
            'delivery_fee' => 8000,
            'service_fee' => 1000,
            'discount_amount' => 0,
            'commission_fee' => 1600,
            'seller_earnings' => 30400,
            'total' => 41000,
            'status' => 'selesai',
            'confirmed_at' => now()->subDay()->subHours(2),
            'ready_at' => now()->subDay()->subHours(1)->subMinutes(30),
            'completed_at' => now()->subDay()->subHours(1),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $p2->id,
            'product_name' => 'Nasi Pecel Madiun Pincuk',
            'variant_name' => null,
            'price' => 16000,
            'quantity' => 2,
            'subtotal' => 32000,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'from_status' => null,
            'to_status' => 'menunggu_konfirmasi',
            'changed_by' => $buyerUser->id,
            'notes' => 'Pesanan dibuat.',
            'created_at' => now()->subDay()->subHours(2),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'from_status' => 'menunggu_konfirmasi',
            'to_status' => 'diproses',
            'changed_by' => $seller1User->id,
            'notes' => 'Pesanan diproses dapur.',
            'created_at' => now()->subDay()->subHours(1)->subMinutes(45),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'from_status' => 'diproses',
            'to_status' => 'siap_diambil_dikirim',
            'changed_by' => $seller1User->id,
            'notes' => 'Pesanan siap diantar kurir lokal.',
            'created_at' => now()->subDay()->subHours(1)->subMinutes(30),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'from_status' => 'siap_diambil_dikirim',
            'to_status' => 'selesai',
            'changed_by' => $buyerUser->id,
            'notes' => 'Pesanan diterima dengan baik oleh pembeli.',
            'created_at' => now()->subDay()->subHours(1),
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'payment_code' => 'GOPAY-' . Str::upper(Str::random(8)),
            'method' => 'gopay',
            'amount' => 41000,
            'status' => 'paid',
            'transaction_reference' => 'TRX-GP-' . rand(100000, 999999),
            'paid_at' => now()->subDay()->subHours(2),
        ]);

        Review::create([
            'order_id' => $order2->id,
            'buyer_id' => $buyerUser->id,
            'store_id' => $store1->id,
            'product_id' => $p2->id,
            'rating' => 5,
            'comment' => 'Bumbu pecelnya juara banget! Beneran medok dan rempeyeknya garing pol. Recommended!',
            'seller_reply' => 'Matur nuwun Mas Dimas! Ditunggu pesanan berikutnya nggih 🙏',
            'replied_at' => now()->subDay()->subMinutes(30),
        ]);

        // 9. Sample Chat
        $chat = Chat::create([
            'buyer_id' => $buyerUser->id,
            'store_id' => $store1->id,
            'product_id' => $p1->id,
            'last_message_at' => now()->subMinutes(10),
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $buyerUser->id,
            'body' => 'Halo Bu Siti, rawonnya kuahnya masih panas kan ya?',
            'created_at' => now()->subMinutes(12),
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $seller1User->id,
            'body' => 'Halo Mas Dimas! Masih panas mendidih baru mateng nggih, ini langsung dibungkus rapat.',
            'created_at' => now()->subMinutes(10),
        ]);
    }
}
