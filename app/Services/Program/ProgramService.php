<?php

namespace App\Services\Program;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramService
{
  public function index(Request $request)
  {
    $q = $request->query('q');
    $active = $request->query('active');
    $perPage = (int) $request->query('per_page', 15);
    $onlyOpen = $request->query('only_open');

    $query = Program::query()
      ->select('programs.*')
      ->orderBy('programs.created_at', 'desc');

    if (!is_null($active)) {
      $query->where('programs.is_active', (int) $active);
    } else {
      $query->where('programs.is_active', '=', 1);
    }

    if ($q) {
      $query->where(function ($sub) use ($q) {
        $sub->where('programs.name', 'like', "%{$q}%")
          ->orWhere('programs.description', 'like', "%{$q}%");
      });
    }

    if ($onlyOpen) {
      $now = now();
      $query->where(function ($sub) use ($now) {
        $sub->whereNull('programs.registration_starts_at')
          ->orWhere('programs.registration_starts_at', '<=', $now);
      })->where(function ($sub) use ($now) {
        $sub->whereNull('programs.registration_ends_at')
          ->orWhere('programs.registration_ends_at', '>=', $now);
      });
    }

    $paginator = $query->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'message' => 'Programs retrieved successfully.',
      'data' => $paginator->items(),
      'meta' => [
        'pagination' => [
          'current_page' => $paginator->currentPage(),
          'per_page' => $paginator->perPage(),
          'total' => $paginator->total(),
          'last_page' => $paginator->lastPage(),
        ],
      ],
    ], 200);
  }

  public function adminIndex(Request $request)
  {
    $q = $request->query('q');
    $includeDeleted = filter_var($request->query('included_deleted', false), FILTER_VALIDATE_BOOLEAN);
    $perPage = (int) $request->query('per_page', 15);

    $query = Program::query()->orderBy('created_at', 'desc');

    if ($includeDeleted) {
      $query->withTrashed();
    }

    if ($q) {
      $query->where('name', 'like', "%{$q}%")
        ->orWhere('description', 'like', "%{$q}%");
    }

    $paginator = $query->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'message' => 'Programs retrieved (admin).',
      'data' => $paginator->items(),
      'meta' => [
        'pagination' => [
          'current_page' => $paginator->currentPage(),
          'per_page' => $paginator->perPage(),
          'total' => $paginator->total(),
          'last_page' => $paginator->lastPage(),
        ],
      ],
    ], 200);
  }
}
