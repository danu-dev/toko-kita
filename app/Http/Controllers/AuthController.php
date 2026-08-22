<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);
        $user->assignRole($buyerRole);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Selamat datang di Toko Kita!');
    }

    public function showSellerRegister()
    {
        $categories = Category::where('is_active', true)->get();
        return view('auth.seller-register', compact('categories'));
    }

    public function sellerRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'store_name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'address' => ['required', 'string'],
            'operational_hours' => ['required', 'string'],
            'nib_number' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $user->assignRole($sellerRole);

        $store = Store::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['store_name'],
            'slug' => Str::slug($validated['store_name']) . '-' . rand(100, 999),
            'description' => $validated['description'] ?? 'Toko Mitra Resmi Toko Kita',
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => 'Malang',
            'operational_hours' => $validated['operational_hours'],
            'nib_number' => $validated['nib_number'] ?? null,
            'status' => 'pending', // Waiting for Admin review
            'is_open' => true,
        ]);

        Wallet::create([
            'store_id' => $store->id,
            'balance' => 0,
            'held_balance' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('seller.dashboard')->with('info', 'Pendaftaran toko berhasil dikirim! Menunggu verifikasi tim admin.');
    }

    public function quickLogin(Request $request, string $role)
    {
        $email = match ($role) {
            'admin' => 'admin@tokokita.id',
            'seller' => 'seller@tokokita.id',
            'buyer' => 'buyer@tokokita.id',
            default => 'buyer@tokokita.id',
        };

        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user);
            return $this->redirectBasedOnRole($user);
        }

        return redirect()->route('login')->withErrors(['email' => 'Akun demo tidak ditemukan.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function redirectBasedOnRole(User $user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('seller')) {
            return redirect()->route('seller.dashboard');
        }
        return redirect()->route('home');
    }
}
