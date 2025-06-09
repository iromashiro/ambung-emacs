<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the user's profile
     */
    public function show()
    {
        $user = auth()->user();
        
        return view('web.profile.show', compact('user'));
    }
    
    /**
     * Show the form for editing the user's profile
     */
    public function edit()
    {
        $user = auth()->user();
        
        return view('web.profile.edit', compact('user'));
    }
    
    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            // Store new photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo = $path;
        }
        
        // Update user data
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();
        
        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully');
    }
    
    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        $user = auth()->user();
        
        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect']);
        }
        
        // Update password
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('profile.show')
            ->with('success', 'Password updated successfully');
    }
    
    /**
     * Display the user's addresses
     */
    public function addresses()
    {
        $user = auth()->user();
        $addresses = $user->addresses;
        
        return view('web.profile.addresses', compact('addresses'));
    }
    
    /**
     * Store a new address for the user
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'sometimes|boolean'
        ]);
        
        $user = auth()->user();
        
        // If this is the first address or is_default is checked, make it default
        $isDefault = $request->has('is_default') || $user->addresses->count() === 0;
        
        // If making this address default, unset default on other addresses
        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }
        
        // Create new address
        $user->addresses()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'is_default' => $isDefault
        ]);
        
        return redirect()->route('profile.addresses')
            ->with('success', 'Address added successfully');
    }
    
    /**
     * Update an existing address
     */
    public function updateAddress(Request $request, Address $address)
    {
        // Check if address belongs to user
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'sometimes|boolean'
        ]);
        
        // If making this address default, unset default on other addresses
        if ($request->has('is_default')) {
            auth()->user()->addresses()->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }
        
        // Update address
        $address->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'is_default' => $request->has('is_default') ? true : $address->is_default
        ]);
        
        return redirect()->route('profile.addresses')
            ->with('success', 'Address updated successfully');
    }
    
    /**
     * Delete an address
     */
    public function destroyAddress(Address $address)
    {
        // Check if address belongs to user
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }
        
        // If deleting default address, make another address default
        if ($address->is_default) {
            $newDefault = auth()->user()->addresses()
                ->where('id', '!=', $address->id)
                ->first();
                
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }
        
        $address->delete();
        
        return redirect()->route('profile.addresses')
            ->with('success', 'Address deleted successfully');
    }
}