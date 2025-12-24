<?php

namespace App\Services\ApplicationDocument;

use App\Models\ApplicationDocument;
use Illuminate\Support\Facades\DB;

class ApplicationDocumentReviewService
{
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

            $pending = $application->documents()
                ->where('status', 'pending')
                ->exists();

            $rejected = $application->documents()
                ->where('status', 'rejected')
                ->exists();

            if($rejected)
            {
                $application->update([
                    'status' => 'submitted',
                    'verified_at' => null
                ]);
            } elseif(!$pending)
            {
                $application->update([
                    'status' => 'verified',
                    'verified_at' => now()
                ]);
            }

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