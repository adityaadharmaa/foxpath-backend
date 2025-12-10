<?php

namespace App\Services\Users;

use App\Exports\Users\UsersExport;
use App\Http\Requests\Users\AdminResetUserPasswordRequest;
use App\Http\Requests\Users\AdminStoreUserRequest;
use App\Http\Requests\Users\AdminUpdateUserRoleRequest;
use App\Http\Requests\Users\UsersExportRequest;
use App\Http\Requests\Users\UsersIndexRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserService
{
    public function __construct(
        protected EmailVerificationServices $emailServices
    ) {}

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
            'users.is_active',
            'roles.name as role_name',
            'profiles.applicant_type',
        )
            ->join('roles', 'users.roles_id', '=', 'roles.id')
            ->leftJoin('profiles', 'users.id', '=', 'profiles.users_id')
            ->orderBy('users.created_at', 'desc');

        if ($type) {
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

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.'
            ], 404);
        }

        DB::beginTransaction();

        try {
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
        } catch (\Exception $e) {
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

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $role = Role::where('name', $data['role'])->first();

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.'
            ], 404);
        }

        DB::beginTransaction();
        try {
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
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user role.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function resetPassword(AdminResetUserPasswordRequest $request, string $id)
    {
        $data = $request->validated();

        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $user->password = Hash::make($data['password']);
            $user->save;

            $user->tokens()->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User password has been reset successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset user password.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function softDelete(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->trashed()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User already deleted.'
            ], 200);
        }

        DB::beginTransaction();

        try {
            $user->tokens()->delete();

            $user->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User soft deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset user password.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function restore(string $id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $user->restore();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User restored successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'is_active' => $user->is_active
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore user.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function summary()
    {
        $totalUsers = User::count();
        $totalActive = User::where('is_active', 1)->count();
        $totalInactive = User::where('is_active', 0)->count();

        $totalDeleted = User::onlyTrashed()->count();

        $totalSiswa = User::join('profiles', 'users.id', '=', 'profiles.users_id')
            ->where('profiles.applicant_type', 'siswa')
            ->whereNull('users.deleted_at')
            ->count();

        $totalMahasiswa = User::join('profiles', 'users.id', '=', 'profiles.users_id')
            ->where('profiles.applicant_type', 'mahasiswa')
            ->whereNull('users.deleted_at')
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Users summary retrieved successfully.',
            'data' => [
                'totals' => [
                    'users' => $totalUsers,
                    'deleted_users' => $totalDeleted,
                    'active' => $totalActive,
                    'inactive' => $totalInactive
                ],
                'by_applicant_type' => [
                    'siswa' => $totalSiswa,
                    'mahasiswa' => $totalMahasiswa
                ],
            ],
        ], 200);
    }

    public function activate(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ((int) $user->is_active === 1) {
            return response()->json([
                'status' => 'success',
                'message' => 'User is already active.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'is_active' => $user->is_active,
                    ],
                ],
            ], 200);
        }

        DB::beginTransaction();
        try {

            $user->is_active = 1;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User activated successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'is_active' => $user->is_active
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate user.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        if ((int) $user->is_active === 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'User already inactive.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'is_active' => $user->is_active
                    ],
                ],
            ], 200);
        }

        DB::beginTransaction();
        try {
            $user->is_active = 0;
            $user->save();

            $user->tokens()->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User deactivated successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'is_active' => $user->is_active,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactivate user.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function resendVerification(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ((int) $user->is_active === 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already active. Verification email is not needed.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $this->emailServices->sendVerificationEmail($user);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Verification email has been resent.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to resend verification email.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function export(UsersExportRequest $request)
    {
        $data = $request->validated();
        $type = $data['type'] ?? null;
        $format = $data['format'];

        $fileNameBase = 'users';
        if ($type) {
            $fileNameBase .= "_{$type}";
        }
        $fileNameBase .= '_' . now()->format('Y-m-d_His');

        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        $fileName = $fileNameBase . '.' . $extension;

        return Excel::download(new UsersExport($type), $fileName);
    }
}
