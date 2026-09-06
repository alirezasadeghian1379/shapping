<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::query()->with('roles')->when($request->search, fn ($query, $search) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")))->latest()->paginate(min($request->integer('per_page', 20), 100));
    }

    public function show(User $user): User
    {
        return $user->load('roles');
    }

    public function update(Request $request, User $user): User
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:100',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user)],
            'is_active' => 'sometimes|boolean',
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'exists:roles,id',
        ]);
        $roles = $data['role_ids'] ?? null;
        unset($data['role_ids']);
        $user->update($data);
        if ($roles !== null) {
            $user->roles()->sync($roles);
        }

        return $user->load('roles');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->is($user), 422, 'نمی‌توانید حساب مدیر فعلی را حذف کنید.');
        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }

    public function roles()
    {
        return Role::withCount('users')->get();
    }

    public function storeRole(Request $request)
    {
        return response()->json(Role::create($this->roleData($request)), 201);
    }

    public function updateRole(Request $request, Role $role): Role
    {
        $role->update($this->roleData($request, $role));

        return $role;
    }

    public function deleteRole(Role $role)
    {
        abort_if($role->is_super_admin, 422, 'نقش مدیر کل قابل حذف نیست.');
        abort_if($role->users()->exists(), 422, 'این نقش به کاربر متصل است.');
        $role->delete();

        return response()->noContent();
    }

    private function roleData(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'alpha_dash', Rule::unique('roles')->ignore($role)],
            'label' => 'required|string|max:100',
            'abilities' => 'required|array',
            'abilities.*' => 'string|max:100',
            'is_super_admin' => 'sometimes|boolean',
        ]);
    }
}
