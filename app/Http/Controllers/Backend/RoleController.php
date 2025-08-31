<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\PermissionService;
use App\Services\RolesService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RolesService $rolesService,
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('viewAny', Role::class);

        $this->setBreadcrumbTitle(__('Roles'));

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.index');
    }

    public function create(): Renderable
    {
        $this->authorize('create', Role::class);

        $this->setBreadcrumbTitle(__('New Role'))
            ->addBreadcrumbItem(__('Roles'), route('admin.roles.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.create', [
            'roleService' => $this->rolesService,
            'all_permissions' => $this->permissionService->getAllPermissionModels(),
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->rolesService->createRole($request->name, $request->input('permissions', []));

        session()->flash('success', __('Role has been created.'));

        return redirect()->route('admin.roles.index');
    }

    public function edit(int $id): Renderable|RedirectResponse
    {
        $role = $this->rolesService->findRoleById($id);
        if (! $role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        $this->authorize('update', $role);

        $this->setBreadcrumbTitle(__('Edit Role'))
            ->addBreadcrumbItem(__('Roles'), route('admin.roles.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.roles.edit', [
            'role' => $role,
            'roleService' => $this->rolesService,
            'all_permissions' => $this->permissionService->getAllPermissionModels(),
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, int $id): RedirectResponse
    {
        $role = $this->rolesService->findRoleById($id);

        if (! $role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        // Check if this is the Superadmin role in demo mode - return 403 directly
        if (config('app.demo_mode') && $role->name === 'Superadmin') {
            abort(403, 'Cannot modify Superadmin role in demo mode.');
        }

        $this->authorize('update', $role);

        $role = $this->rolesService->updateRole($role, $request->name, $request->input('permissions', []));

        session()->flash('success', __('Role has been updated.'));

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = $this->rolesService->findRoleById($id);

        if (! $role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        // Check if this is the Superadmin role in demo mode - return 403 directly
        if (config('app.demo_mode') && $role->name === Role::SUPERADMIN) {
            abort(403, 'Cannot delete Superadmin role in demo mode.');
        }

        $this->authorize('delete', $role);

        $this->rolesService->deleteRole($role);

        session()->flash('success', __('Role has been deleted.'));

        return redirect()->route('admin.roles.index');
    }

    /**
     * Delete multiple roles at once
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('bulkDelete', Role::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.roles.index')
                ->with('error', __('No roles selected for deletion'));
        }

        $deletedCount = 0;

        foreach ($ids as $id) {
            $role = $this->rolesService->findRoleById((int) $id);

            if (! $role) {
                continue;
            }

            // Skip Superadmin role.
            if ($role->name === Role::SUPERADMIN) {
                continue;
            }

            $this->rolesService->deleteRole($role);

            $deletedCount++;
        }

        if ($deletedCount > 0) {
            session()->flash('success', __(':count roles deleted successfully', ['count' => $deletedCount]));
        } else {
            session()->flash('error', __('No roles were deleted. Selected roles may include protected roles.'));
        }

        return redirect()->route('admin.roles.index');
    }
}
