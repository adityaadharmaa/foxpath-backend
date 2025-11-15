<?php 

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileServices
{
  // Lengkapi profile yang sudah ada
  public function updateProfile(User $user, array $data){
    try {
      DB::beginTransaction();


      $profile = $user->profile ?? $user->profile()->create();

      $required = [
        'applicant_type',
        'student_identifier',
        'full_name',
        'phone',
        'address',
        'date_of_birth'
      ];

      foreach($required as $field ){
        if(!isset($data[$field]) || $data[$field] === null){
          throw new \Exception("Field '{$field}' is required.");
        }
      }

      if($data['applicant_type'] === 'siswa'){
         // NISN 10–12 digit
          if (!preg_match('/^[0-9]{10,12}$/', $data['student_identifier'])) {
              throw new \Exception("NISN tidak valid (harus 10–12 digit).");
          }
      }

      if($data['applicant_type'] === 'mahasiswa'){
          // NIM alfanumerik (panjang 5–20)
          if (!preg_match('/^[A-Za-z0-9]{5,20}$/', $data['student_identifier'])) {
              throw new \Exception("NIM tidak valid.");
          }
      }

      $profile->update([
        'applicant_type' => $data['applicant_type'],
        'student_identifier' => $data['student_identifier'],
        'full_name' => $data['full_name'],
        'phone' => $data['phone'],
        'address' => $data['address'],
        'bio' => $data['bio'] ?? null,
        'profile_picture' => $data['profile_picture'] ?? $profile->profile_picture,
        'date_of_birth' => $data['date_of_birth'],
      ]);

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Profile updated successfully.',
        'data' => $profile->fresh()
      ], 201);
    } catch(\Exception $e){
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update profile.',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}