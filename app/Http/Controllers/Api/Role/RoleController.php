<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\Role\RoleService;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index()
    {
        return $this->roleService->index();
    }

    public function store(StoreRoleRequest $request)
    {
        return $this->roleService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->roleService->show($id);
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        return $this->roleService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->roleService->destroy($id);
    }
}
