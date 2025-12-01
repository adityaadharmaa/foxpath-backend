<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\Role\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $roleService;
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }
    // Controller methods will be defined here
    public function index()
    {
        // show all roles 
        return $this->roleService->index();
    }

    public function store(StoreRoleRequest $request){
        $validated = $request->validated();

        return $this->roleService->store($validated);
    }

    public function show(int $id)
    {
        return $this->roleService->show($id);
    }

    public function update(UpdateRoleRequest $request, int $id){
        return $this->roleService->update($request->validated(), $id);
    }

    public function destroy(int $id){
        return $this->roleService->destroy($id);
    }
}
