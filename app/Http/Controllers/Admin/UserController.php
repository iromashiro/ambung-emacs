<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $role = $request->input('role');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);
        
        if ($role) {
            $users = $this->userRepository->findByRoleWithPagination($role, $perPage);
        } elseif ($status) {
            $users = $this->userRepository->findByStatusWithPagination($status, $perPage);
        } else {
            $users = $this->userRepository->getAllWithPagination($perPage);
        }
        
        return view('admin.users.index', [
            'users' => $users,
            'role' => $role,
            'status' => $status,
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): View
    {
        $user = $this->userRepository->findById($id);
        
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id): View
    {
        $user = $this->userRepository->findById($id);
        
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = $this->userRepository->findById($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'role_enum' => ['required', 'string', 'in:buyer,seller,admin'],
            'status_enum' => ['required', 'string', 'in:active,pending,suspended'],
        ]);
        
        $this->userRepository->update($user, $validated);
        
        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'User updated successfully');
    }

    /**
     * Change user status.
     */
    public function changeStatus(Request $request, string $id): RedirectResponse
    {
        $user = $this->userRepository->findById($id);
        
        $validated = $request->validate([
            'status_enum' => ['required', 'string', 'in:active,pending,suspended'],
        ]);
        
        $this->userRepository->update($user, [
            'status_enum' => $validated['status_enum'],
        ]);
        
        return redirect()->back()
            ->with('success', 'User status updated successfully');
    }
}