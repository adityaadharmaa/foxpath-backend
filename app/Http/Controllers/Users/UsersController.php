<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\AdminStoreUserRequest;
use App\Http\Requests\Users\AdminUpdateUserRoleRequest;
use App\Http\Requests\Users\UsersExportRequest;
use App\Http\Requests\Users\UsersIndexRequest;
use App\Services\Users\UserService;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    private $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService; 
    }

    public function index(UsersIndexRequest $request)
    {
        return $this->userService->index($request);
    }

    public function store(AdminStoreUserRequest $request)
    {
        return $this->userService->storeByAdmin($request);
    }

    public function updateRole(AdminUpdateUserRoleRequest $request, string $id)
    {
        return $this->userService->updateRole($request, $id);
    }

    public function activate(string $id)
    {
        return $this->userService->activate($id);
    }

    public function deactivate(string $id)
    {
        return $this->userService->deactivate($id);
    }

    public function resendVerification(string $id)
    {
        return $this->userService->resendVerification($id);
    }

    public function export(UsersExportRequest $request)
    {
        return $this->userService->export($request);
    }
}
