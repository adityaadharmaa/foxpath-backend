<?php

namespace App\Services\ApplicationDocument;
use App\Models\ApplicationDocument;
use App\Models\InternshipApplication;
use App\Services\InternshipApplication\InternshipApplicationStatusService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationDocumentService
{
    public function __construct(
        protected InternshipApplicationStatusService $statusService
    )
    {}

    public function upload(
        int $applicationId,
        string $type,
        UploadedFile $file,
        int $userId
    ) {
        $application = InternshipApplication::with('documents')
        ->find($applicationId);

        if(!$application)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Application not found.'
            ], 404);
        }

        if($application->users_id !== (int) $userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized application access.'
            ], 403);
        }

        if($application->status !== 'submitted')
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Documents can only be uploaded for applications with status "submitted".',
                'current_status' => $application->status
            ], 422);
        }

        DB::beginTransaction();

        try{
            $existing = $application->documents()
            ->where('type', $type)
            ->first();

            if($existing) {
                Storage::disk('public')->delete($existing->file_path);
                $existing->delete();
            }

            $path = $file->store(
                "applications/{$application->id}",
                'public'
            );

            $document = $application->documents()->create([
                'type' => $type,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'status' => 'pending'
            ]);

            // $this->statusService->autoVerifyIfEligible($application->fresh());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Document uploaded successfully',
                'data' => [
                    'document' => $document,
                    'application_status' => $application->fresh()->status
                ]
            ], 201);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload document',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}