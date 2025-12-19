<?php 

namespace App\Services\Criteria;

use App\Models\Criteria;

class CriteriaService
{
    public function index(array $filters)
    {
        $query = Criteria::query();

        if(!empty($filters['q'])){
            $q = $filters['q'];

            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        if(!is_null($filters['is_active'] ?? null)){
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Criteria fetched successfully',
            'data' => $query->orderBy('created_at', 'desc')->get()
        ], 200);
    }
}