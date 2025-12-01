<?php 

namespace App\Services\Role;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleService {
    public function index(){
        try{
            DB::beginTransaction();

            $roles = Role::all();

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => "Success fecth all roles.",
                "data" => $roles
            ]);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => "Failed to fetch all roles.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function store(array $data){
        try{
            DB::beginTransaction();

            $role = Role::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Create role success.',
                'data' => $role
            ], 201);
        } catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create role.',
                'error' => $e->getMessage()
            ], 500);
        }    
    }

    public function show(int $id){
        try{
           $role = Role::find($id);

           if(!$role){
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found'
            ], 404);
           }

            return response()->json([
                'status' => 'success',
                'message' => 'Success fetch role detail.',
                'data' => $role
            ], 200);

        } catch (\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch role detail.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(array $data, int $id){
        try{
            DB::beginTransaction();
            // Melakukan pengecekan apakah role benar benar ada
            $role = Role::find($id);

            if(! $role){
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message'=> 'Role not found.'
                ], 404);
            }

            // Jika role dengan name 'admin' maka tidak bisa diupdate 
            if($role->name === 'admin' && array_key_exists('name', $data) && $data['name'] !== 'admin'){
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'The admin role name cannot be changed.'
                ], 403);
            }

            // Proses update role
            DB::table('roles')
            ->where('id', $id)
            ->update([
                'name' => $data['name'] ?? $role->name,
                'description' => $data['description'] ?? $role->description,
                'updated_at' => now()
            ]);

            $role->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Update roles success.',
                'data' => $role->fresh(),
            ], 200);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try{
            DB::beginTransaction();

            $role = Role::find($id);

            if(!$role){
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Role not found.'
                ], 404);
            }
        
            if($role->name === 'admin'){
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Admin role cannot be deleted.'
                ], 403);
            }

            $role->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delete role success.'
            ], 200);
        } catch(\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete role.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}