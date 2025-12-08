<?php

namespace App\Services\Users;

use App\Http\Requests\Users\UsersIndexRequest;
use App\Models\User;

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
}