<?php

namespace App\Services\ApplicationDocument;

use App\Models\ApplicationDocument;
use App\Services\ApplicationScore\AcademicScoreService;
use Illuminate\Support\Facades\DB;

class ApplicationDocumentReviewService
{
    public function __construct(
        protected AcademicScoreService $academic
    )
    {}

    public function review(
        int $documentId, 
        string $status,
        ?string $note,
        int $adminId
    )
    {
        $document = ApplicationDocument::with('application.documents')
            ->find($documentId);

        if(!$document)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found.'
            ], 404);
        }

        if($document->application->status !== 'submitted') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot review document. Application status is already ' . $document->application->status
            ], 422);
        }
        DB::beginTransaction();

        try{
            if($document->status !== 'pending')
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document already reviewed.'
                ], 422);
            }

            $document->update([
                'status' => $status,
                'review_note' => $note,
                'reviewed_at' => now(),
                'reviewed_by' => $adminId
            ]);

            $application = $document->application;

            $allDocuments = $application->documents()->get();

            $hasRejected = $allDocuments->contains('status', 'rejected');

            if($hasRejected) {
                $application->update([
                    'status' => 'submitted',
                    'verified_at' => null
                ]);
            } else {
                $requiredTypes = ['cv', 'transcript'];

                $approvedTypes = $allDocuments
                    ->where('status', 'approved')
                    ->pluck('type')
                    ->unique()
                    ->toArray();

                $missingRequirements = array_diff($requiredTypes, $approvedTypes);

                $hasPending = $allDocuments->contains('status', 'pending');

                if(empty($missingRequirements) && !$hasPending) {
                    $application->update([
                        'status' => 'verified',
                        'verified_at' => now()
                    ]);

                    if(method_exists($this, 'academic')) {
                            $this->academic->sync($application->fresh());
                    }
                }
            }

            // $pending = $application->documents()
            //     ->where('status', 'pending')
            //     ->exists();

            // $rejected = $application->documents()
            //     ->where('status', 'rejected')
            //     ->exists();

            // if($rejected)
            // {
            //     $application->update([
            //         'status' => 'submitted',
            //         'verified_at' => null
            //     ]);
            // } elseif(!$pending)
            // {
            //     $application->update([
            //         'status' => 'verified',
            //         'verified_at' => now()
            //     ]);

            //     $this->academic->sync($application->fresh());
            // }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Document reviewed successfully.',
                'data' => [
                    'document' => $document->fresh(),
                    'application_status' => $application->fresh()->status
                ]
            ], 200);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to review document.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ],500);
        }
    }
}