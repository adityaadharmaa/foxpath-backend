<?php 

namespace App\Services\Criteria;

use App\Exports\Criterias\CriteriaExport;
use App\Models\Criteria;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CriteriaService
{
    public function index(array $filters)
    {
        $query = Criteria::query();

        $active = $filters['is_active'] ?? null;
        $includeDeleted = (bool) ($filters['include_deleted'] ?? false);
        $deletedOnly = (bool) ($filters['deleted_only'] ?? false);
        $perPage = (int) ($filters['per_page'] ?? 5);

        if ($includeDeleted) {
            $query->withTrashed();
        }

        if ($deletedOnly) {
            $query->onlyTrashed();
        }

        if(!empty($filters['q'])){
            $q = $filters['q'];

            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        if(!is_null($filters['is_active'] ?? null)){
            $query->where('is_active', $active);
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $data = collect($paginator->items())->map(function ($item) {
            return [
                ...$item->toArray(),
                'is_deleted' => !is_null($item->deleted_at),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Criteria fetched successfully',
            'data' => $data,
            'meta' => [
                'filters' => [
                    'is_active' => $active,
                    'include_deleted' => $includeDeleted,
                    'deleted_only' => $deletedOnly,
                    'per_page' => $perPage,
                    ],
                'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                ],
            ]
        ], 200);
    }

    public function store(array $data)
    {
        
        DB::beginTransaction();
        
        try {
            // $criteria->save();
            $criteria = Criteria::create($data);
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

    public function update(array $data, int $id)
    {
        $criteria = Criteria::find($id);

        if(!$criteria)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Criteria not found.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $criteria->update($data);

            // $criteria->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Criteria updated successfully.',
                'data' => [
                    'criteria' => $criteria
                ]
            ], 200);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update criteria.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error.',
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        $criteria = Criteria::find($id);

        if(!$criteria)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Criteria not found.'
            ], 404);
        }

        if($criteria->scores()->exists())
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete criteria with associated application scores.'
            ], 400);
        }

        if($criteria->trashed())
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Criteria already deleted.'
            ], 400);
        }


        DB::beginTransaction();
        try {
            $criteria->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Criteria deleted successfully.'
            ], 200);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete criteria.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error.',
            ], 500);
        }
    }

    public function restore(int $id)
    {
        $criteria = Criteria::onlyTrashed()->find($id);

        if(!$criteria)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Criteria not found or not deleted.'
            ], 404);
        }

        DB::beginTransaction();
        try{
            $criteria->restore();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Criteria restored successfully.',
                'data' => [
                    'criteria' => $criteria
                ]
            ], 200);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restore criteria.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error.',
            ], 500);
        }
    }

    public function toggle(int $id)
    {
        $criteria = Criteria::find($id);

        if(!$criteria)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Criteria not found.'
            ], 404);
        }

        DB::beginTransaction();
        try{
            $criteria->is_active = !$criteria->is_active;

            $criteria->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Criteria status updated.',
                'data' => [
                    'id' => $criteria->id,
                    'name' => $criteria->name,
                    'is_active' => $criteria->is_active,
                ],
            ], 200);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update criteria status.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error.',
            ], 500);
        }
    }

    public function export(array $filters)
    {
        $format = $filters['format'];
        $isActive = $filters['is_active'] ?? null;

        $fileName = 'criterias_' . now()->format('Ymd_His') . '.' . $format;

        return Excel::download(
            new CriteriaExport($isActive),
            $fileName
        );
    }
}