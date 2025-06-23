<?php

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
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // FIX: Validate tanpa date_of_birth
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:15'],
            'role' => ['required', 'in:buyer,seller'],
        ]);

        try {
            DB::beginTransaction();

            // FIX: Create user tanpa date_of_birth
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => 'active',
            ]);

            // FIX: Jika register sebagai seller, buat store
            if ($request->role === 'seller') {
                Store::create([
                    'user_id' => $user->id,
                    'name' => $user->name . "'s Store",
                    'description' => 'New UMKM store',
                    'status' => 'active',
                    'phone' => $request->phone,
                ]);

                Log::info('Seller registered with store', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $request->role
                ]);
            }

            event(new Registered($user));
            Auth::login($user);

            DB::commit();

            // FIX: Redirect berdasarkan role TANPA route home
            if ($user->role === 'seller') {
                return redirect()->route('seller.dashboard')->with(
                    'success',
                    'Registration successful! Welcome to Ambung Emac Seller Center.'
                );
            } else {
                // FIX: Redirect ke buyer dashboard atau main page
                if (Route::has('buyer.dashboard')) {
                    return redirect()->route('buyer.dashboard')->with('success', 'Welcome to Ambung Emac!');
                } else {
                    return redirect('/')->with('success', 'Welcome to Ambung Emac!');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'role' => $request->role ?? 'not_provided'
            ]);

            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}
