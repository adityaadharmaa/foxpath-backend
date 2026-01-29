<?php

namespace App\Services\ApplicationScore;

use App\Models\{
    InternshipApplication,
    ApplicationScore,
    Criteria
};

use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Application;

class ApplicationScoreService
{
    public function store(int $applicationId, array $data)
    {
        $application = InternshipApplication::find($applicationId);

        if (!$application) {
            return response()->json([
                'status' => 'error',
                'message' => 'Application not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($data['scores'] as $item) {
                $criteria = Criteria::where('id', $item['criteria_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$criteria) {
                    throw new \Exception("Criteria with ID {$item['criteria_id']} not found or inactive.");
                }

                ApplicationScore::updateOrCreate(
                    [
                        'internship_applications_id' => $application->id,
                        'criterias_id' => $criteria->id
                    ],
                    [
                        'value' => $item['value'],
                        'normalized_value' => null,
                        'weighted_value' => null
                    ]
                );
            }

            $application->update([
                'status' => 'scored',
                'scored_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Score saved successfully.',
                'data' => [
                    'application_id' => $application->id
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store application scores.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error.',
            ], 500);
        }
    }
}
