<?php

namespace App\Services\Users;

use App\Http\Requests\Users\AdminStoreUserRequest;
use App\Http\Requests\Users\AdminUpdateUserRoleRequest;
use App\Http\Requests\Users\UsersIndexRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService{
    public function index(UsersIndexRequest $request)
    {
        $data = $request->validated();

        $type = $data['type'] ?? null;
        $perPage = $data['per_page'];

        $query = User::select(
            'users.id',
            'users.username',
            'users.email',
            'users.roles_id',
            'roles.name as role_name',
            'profiles.applicant_type',
        )
        ->join('roles', 'users.roles_id', '=' ,'roles.id')
        ->leftJoin('profiles', 'users.id', '=', 'profiles.users_id')
        ->orderBy('users.created_at', 'desc');

        if($type) {
            $query->where('profiles.applicant_type', $type);
        }

        $users = $query->paginate($perPage);

         if ($type && $users->total() === 0) {
            return response()->json([
                'status'  => 'success',
                'message' => "No users found for type '{$type}'.",
                'data'    => [],
                'meta'    => [
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'per_page'     => $users->perPage(),
                        'total'        => $users->total(),
                        'last_page'    => $users->lastPage(),
                    ],
                    'filters' => [
                        'type' => $type,
                    ],
                ],
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully.',
            'data' => $users->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ],
                'filters' => $type
            ]
        ], 200);
    }

    public function storeByAdmin(AdminStoreUserRequest $request)
    {
        $data = $request->validated();

        $role = Role::where('name', $data['role'])->first();

        if(!$role){
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.'
            ], 404);
        }

        DB::beginTransaction();

        try{
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'roles_id' => $role->id,
                'is_active' => 1
            ]);

            $user->profile()->create();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully by admin.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $role->name,
                    ],
                ],
            ], 201);
        } catch (\Exception $e)
        {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateRole(AdminUpdateUserRoleRequest $request, string $id)
    {
        $data = $request->validated();

        $user = User::find($id);

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $role = Role::where('name', $data['role'])->first();

        if(!$role){
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.'
            ], 404);
        }

        DB::beginTransaction();
        try{
            $user->roles_id = $role->id;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User role updated successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $role->name
                    ],
                ],
            ], 200);

        }catch(\Exception $e)
        {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user role.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}