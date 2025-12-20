<?php 

namespace App\Services\Criteria;

use App\Models\Criteria;
use Illuminate\Support\Facades\DB;

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

    public function store(array $data)
    {
        $criteria = Criteria::create($data);

        DB::beginTransaction();

        try {
            $criteria->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Criteria created successfully.',
                'data' => [
                    'criteria' => $criteria
                ]
            ], 201);
            
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create criteria.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error.',
            ], 500);
        }
    }
}