<?php

namespace App\Services\Program;

use App\Exports\Programs\ProgramsExport;
use App\Http\Requests\Programs\ProgramIndexRequest;
use App\Http\Requests\Programs\StoreProgramRequest;
use App\Http\Requests\Programs\UpdateProgramRequest;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProgramService
{
  public function index(ProgramIndexRequest $request)
  {
    $user = $request->user();
    $isAdmin = $user && $user->hasRole('admin');

    $filter = $request->validated();

    $q = $filter['q'] ?? null;
    $active = $filter['is_active'] ?? null;
    $onlyOpen = $filter['only_open'] ?? null;
    $includeDeleted = (bool) ($filter['include_deleted'] ?? false);
    $deletedOnly = (bool) ($filter['deleted_only'] ?? false);
    $perPage = (int) ($filter['per_page'] ?? 15);

    $query = Program::query()
    ->select('programs.*')
    ->withCount('applications')
    ->orderBy('programs.created_at', 'desc');

    if ($isAdmin)
    {
       if ($deletedOnly) {
        $query->onlyTrashed();
    } elseif ($includeDeleted) {
        $query->withTrashed();
    }

    if (!is_null($active)) {
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

     $data = collect($paginator->items())->map(function ($program) {
        return [
            ...$program->toArray(),
            'is_deleted' => !is_null($program->deleted_at),
        ];
    });

     return response()->json([
        'status' => 'success',
        'message' => 'Programs retrieved successfully.',
        'data' => $data,
        'meta' => [
            'role' => $isAdmin ? 'admin' : 'user',
            'filters' => [
                'include_deleted' => (bool) $includeDeleted,
                'deleted_only' => (bool) $deletedOnly,
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ],
    ], 200);
  }

  public function getApplicantsByProgram(int $programId, ?string $status = null, ?string $result = null){
    DB::beginTransaction();

    try{
      $query = InternshipApplication::with([
                'user:id,email',
                'user.profile:id,users_id,full_name,applicant_type',
                'user.profileEducation',
                'scores.criteria:id,name,weight',
            ])
            ->where('programs_id', $programId);

            // Filter status (submitted, verified, scored)
            if ($status) {
                $query->where('status', $status);
            }

            // Filter hasil akhir
            if ($result === 'accepted') {
                $query->where('status', 'accepted');
            }

            if ($result === 'rejected') {
                $query->where('status', 'rejected');
            }

            $applications = $query
                ->orderBy('rank')
                ->get();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Applicants retrieved successfully.',
                'data' => $applications
            ], 200);

    } catch (\Exception $e) {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to retrive applicants.',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'
      ], 500);
    }
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

  public function update(UpdateProgramRequest $request, int $id)
  {
    $data = $request->validated();

    $program = Program::find($id);

    if(!$program)
    {
      return response()->json([
        'status' => 'error',
        'message' => 'Program not found.'
      ], 404);
    }

    DB::beginTransaction();
    try{
      $program->update($data);

      $program->save();

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Program updated successfully.',
        'data' => [
          'program' => $program,
        ],
      ], 200);

    } catch (\Exception $e)
    {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to updat program.',
        'error' => config('app.debug') ? $e->getMessage() : null
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

  public function activate(int $id)
  {
    $program = Program::find($id);

    if(!$program)
    {
      return response()->json([
        'status' => 'error',
        'message' => 'Program not found.'
      ], 404);
    }

    DB::beginTransaction();
    try {
      $program->is_active = !$program->is_active;

      $program->save();

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Program status updated.',
        'data' => [
          'id' => $program->id,
          'name' => $program->name,
          'is_active' => $program->is_active,
        ],
      ], 200);
    } catch (\Exception $e)
    {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update program status.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], 500);
    }
  }

  public function restore(int $id)
  {
    $program = Program::onlyTrashed()->find($id);

    if (!$program) {
      return response()->json([
        'status' => 'error',
        'message' => 'Program not found.'
      ], 404);
    }

    DB::beginTransaction();
    try {
      $program->restore();

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Program restored successfully.',
        'data' => [
          'program' => $program,
        ],
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to restore program.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], 500);
    }
  }

  public function export(array $filters)
  {
     $format = $filters['format'];
      $includeDeleted = (bool) ($filters['include_deleted'] ?? false);
      $deletedOnly = (bool) ($filters['deleted_only'] ?? false);
      $isActive = $filters['is_active'] ?? null;

      $fileName = 'programs_' . now()->format('Ymd_His') . '.' . $format;

      return Excel::download(
          new ProgramsExport($includeDeleted, $deletedOnly, $isActive),
          $fileName
      );
  }

  public function summary()
  {
    $programs = Program::withTrashed()
        ->withCount([
            'applications',
            'applications as pending_applicants' => fn ($q) =>
                $q->where('status', 'pending'),
            'applications as accepted_applicants' => fn ($q) =>
                $q->where('status', 'accepted'),
            'applications as rejected_applicants' => fn ($q) =>
                $q->where('status', 'rejected'),
        ])
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => [
            'total_programs' => $programs->count(),
            'active_programs' => $programs->whereNull('deleted_at')->where('is_active', true)->count(),
            'deleted_programs' => $programs->whereNotNull('deleted_at')->count(),

            'total_applications' => $programs->sum('applications_count'),
            'pending_applications' => $programs->sum('pending_applicants'),
            'accepted_applications' => $programs->sum('accepted_applicants'),
            'rejected_applications' => $programs->sum('rejected_applicants'),

            'programs' => $programs->map(fn ($program) => [
                'id' => $program->id,
                'name' => $program->name,
                'is_active' => $program->is_active,
                'is_deleted' => !is_null($program->deleted_at),
                'applications' => [
                    'total' => $program->applications_count,
                    'pending' => $program->pending_applicants,
                    'accepted' => $program->accepted_applicants,
                    'rejected' => $program->rejected_applicants,
                ],
            ]),
        ],
    ], 200);
  }
}