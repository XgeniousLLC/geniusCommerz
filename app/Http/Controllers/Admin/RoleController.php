<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Admin;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::where('guard_name', 'admin')->withCount('permissions')->get();

        /** @var Collection<string, int> $adminCounts */
        $adminCounts = $roles->mapWithKeys(
            fn (Role $role) => [$role->name => Admin::role($role->name)->count()]
        );

        return view('admin.roles.index', compact('roles', 'adminCounts'));
    }

    public function create(): View
    {
        $permissionGroups = collect(PermissionSeeder::PERMISSIONS)->map(
            fn ($perms) => Permission::whereIn('name', $perms)->where('guard_name', 'admin')->get()
        );

        return view('admin.roles.create', compact('permissionGroups'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" created successfully.");
    }

    public function edit(Role $role): View
    {
        $permissionGroups = collect(PermissionSeeder::PERMISSIONS)->map(
            fn ($perms) => Permission::whereIn('name', $perms)->where('guard_name', 'admin')->get()
        );
        $assigned = $role->permissions->pluck('name')->all();

        return view('admin.roles.edit', compact('role', 'permissionGroups', 'assigned'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name !== 'super-admin') {
            $role->update(['name' => $request->name]);
        }

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'The super-admin role cannot be deleted.');
        }

        $adminsCount = Admin::role($role->name)->count();
        if ($adminsCount > 0) {
            return back()->with('error', "Cannot delete — {$adminsCount} admin(s) still have this role.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted.');
    }
}
