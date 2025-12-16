<?php

namespace App\Services\Program;

use App\Http\Requests\Programs\StoreProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramService
{
  public function index(Request $request)
  {
    $user = $request->user();
    $isAdmin = $user && $user->hasRole('admin');

    $q = $request->query('q');
    $active = $request->query('active');
    $onlyOpen = $request->query('only_open');
    $includeDeleted = filter_var(
      $request->query('include_deleted', 'false'),
      FILTER_VALIDATE_BOOLEAN
    );
    $perPage = (int) $request->query('per_page', 15);

    $query = Program::query()
    ->select('programs.*')
    ->orderBy('programs.created_at', 'desc');

    if ($isAdmin)
    {
      if($includeDeleted)
      {
        $query->withTrashed();
      }

      if(!is_null($active)){
        $query->where('programs.is_active', (int) $active);
      }
    }
    else {
      $query->where('programs.is_active', 1);

      if($onlyOpen)
      {
        $now = now();

        $query->where(function ($sub) use ($now){
          $sub->whereNull('programs.registration_starts_at')
              ->orWhere('programs.registration_starts_at', '<=', $now);
        })->where(function ($sub) use ($now){
          $sub->whereNull('programs.registration_ends_at')
              ->orWhere('programs.registration_ends_at', '>=', $now);
        });
      }
    }

    if($q) {
      $query->where(function ($sub) use ($q){
        $sub->where('programs.name', 'like', "%{$q}%")
            ->orWhere('programs.description', 'like', "%{$q}%");
      });
    }

    $paginator = $query->paginate($perPage);

     return response()->json([
        'status' => 'success',
        'message' => 'Programs retrieved successfully.',
        'data' => $paginator->items(),
        'meta' => [
            'role' => $isAdmin ? 'admin' : 'user',
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ],
    ]);
  }

  public function store(StoreProgramRequest $request)
  {
    $data = $request->validated();

    DB::beginTransaction();
    try{
      $program = Program::create($data);

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Program created successfully.',
        'data' => [
          'program' => $program,
        ],
      ], 201);
    } catch (\Exception $e)
    {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create program.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], 500);
    }
  }

  public function destroy(int $id)
  {
    $program = Program::find($id);

    if(!$program)
    {
      return response()->json([
        'status' => 'error',
        'message' => 'Program not found.'
      ], 404);
    }

    if($program->trashed()){
      return response()->json([
        'status' => 'error',
        'message' => 'Program already deleted.'
      ], 400);
    }

    if($program->applications()->exists()){
      return response()->json([
        'status' => 'error',
        'message' => 'Cannot delete program with associated applications.'
      ], 400);
    }

    DB::beginTransaction();

    try{ 
      $program->delete();

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Program deleted successfully.'
      ], 204);

    } catch (\Exception $e)
    {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete program.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], 500);
    }
  }
}
