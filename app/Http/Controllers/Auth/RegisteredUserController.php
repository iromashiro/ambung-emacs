<?php
// app/Http/Controllers/Auth/RegisteredUserController.php - PERBAIKAN FATAL

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:15'],
            'role' => ['required', 'in:buyer,seller'],
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => 'active',
            ]);

            // 🚨 PERBAIKAN FATAL: Jika register sebagai seller, buat store dengan handling yang lebih baik
            if ($request->role === 'seller') {
                $storeName = $user->name . "'s Store";
                $storeSlug = Str::slug($storeName);

                // Ensure unique slug
                $originalSlug = $storeSlug;
                $counter = 1;
                while (Store::where('slug', $storeSlug)->exists()) {
                    $storeSlug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                // 🚨 PERBAIKAN: Reset sequence jika ada masalah
                try {
                    DB::statement("SELECT setval('stores_id_seq', (SELECT COALESCE(MAX(id), 0) + 1 FROM stores))");
                } catch (\Exception $e) {
                    Log::warning('Failed to reset stores sequence: ' . $e->getMessage());
                }

                $store = Store::create([
                    'seller_id' => $user->id,
                    'name' => $storeName,
                    'slug' => $storeSlug,
                    'description' => 'New UMKM store - Please update your store information',
                    'address' => 'Default Address - Please update in store settings',
                    'status' => 'active',
                    'phone' => $request->phone ?? 'No phone provided',
                ]);

                Log::info('🚨 Seller registered with store', [
                    'user_id' => $user->id,
                    'store_id' => $store->id,
                    'email' => $user->email,
                    'role' => $request->role,
                    'store_name' => $storeName,
                    'store_slug' => $storeSlug
                ]);
            }

            event(new Registered($user));
            Auth::login($user);

            DB::commit();

            // Redirect berdasarkan role
            if ($user->role === 'seller') {
                return redirect()->route('seller.dashboard')->with(
                    'success',
                    'Registration successful! Welcome to Ambung Emac Seller Center. Please update your store information.'
                );
            } else {
                return redirect('/')->with('success', 'Welcome to Ambung Emac!');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('🚨 Registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'role' => $request->role ?? 'not_provided',
                'trace' => $e->getTraceAsString()
            ]);

            // Handle specific database errors
            if (str_contains($e->getMessage(), 'duplicate key value')) {
                return back()->withErrors(['registration' => 'Registration failed due to database conflict. Please try again.'])->withInput();
            }

            if (str_contains($e->getMessage(), 'violates unique constraint')) {
                return back()->withErrors(['registration' => 'Email or store name already exists. Please try different values.'])->withInput();
            }

            return back()->withErrors(['registration' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }
}
