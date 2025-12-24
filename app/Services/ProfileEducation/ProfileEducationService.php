<?php

namespace App\Services\ProfileEducation;

use App\Http\Requests\ProfileEducation\StoreProfileEducation;
use App\Models\Profile;
use App\Models\ProfileEducation;
use Illuminate\Support\Facades\DB;

class ProfileEducationService
{
    public function store(StoreProfileEducation $request)
    {
        $data = $request->validated();
        $userId = $request->user()->id;

        $profile = Profile::where('users_id', $userId)->first();

        if(!$profile)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found.'
            ], 404);
        }

        if($profile->applicant_type === 'mahasiswa')
        {
            if(empty($data['nim']) || empty($data['gpa'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'NIM and GPA are required for mahasiswa.'
                ], 422);
            }

            $data['nisn'] = null;
            $data['average_score'] = null;
        }

        if($profile->applicant_type === 'siswa')
        {
            if(empty($data['nisn']) || empty($data['average_score'])){
                return response()->json([
                    'status' => 'error',
                    'message' => 'NISN and average score are required for siswa.'
                ], 422);
            }

            $data['nim'] = null;
            $data['gpa'] = null;
        }

        DB::beginTransaction();
        try{
            $education = ProfileEducation::updateOrCreate(
                [
                    'profiles_id' => $profile->id,
                    'is_active' => true
                ],
                [
                    'level' => $data['level'],
                    'institution_name' =>  $data['institution_name'],
                    'major' => $data['major'] ?? null,
                    'nim' => $data['nim'] ?? null,
                    'nisn' => $data['nisn'] ?? null,
                    'gpa' => $data['gpa'] ?? null,
                    'average_score' => $data['average_score'] ?? null
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Education saved successfully.',
                'data' => [
                    'education' => $education
                ]
            ], 201);
        } catch (\Exception $e)
        {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add profile education.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal service error'
            ], 500);
        }
    }

    public function show()
    {
        $profile = Profile::where('users_id', auth()->id())->first();

        if(!$profile)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found'
            ], 404);
        }

        $education = ProfileEducation::where('profiles_id', $profile->id)
        ->where('is_active', true)
        ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Education profile retrieved successfully.',
            'data' => $education
        ]);
    }
}