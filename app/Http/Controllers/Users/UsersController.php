<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
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

    public function index(UsersIndexRequest $request){
        return $this->userService->index($request);
    }
}
