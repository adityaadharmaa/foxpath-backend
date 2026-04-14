<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileServices
{
  // Lengkapi profile yang sudah ada
  public function updateProfile(User $user, array $data)
  {
    try {
      DB::beginTransaction();


      $profile = $user->profile ?? $user->profile()->create();

      $required = [
        'full_name',
        'phone',
      ];

      $isAdmin = $user->hasRole('admin');

      if (!$isAdmin) {
        $required = array_merge($required, ['applicant_type', 'address', 'date_of_birth']);
      }

      foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === null) {
          throw new \Exception("Field '{$field}' is required.");
        }
      }

      $profilePicturePath = $profile->profile_picture;

      if (isset($data['profile_picture']) && $data['profile_picture'] instanceof UploadedFile) {
        if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
          Storage::disk('public')->delete($profilePicturePath);
        }

        $file = $data['profile_picture'];
        $extension = strtolower($file->getClientOriginalExtension());
        $tempPath = $file->getRealPath();

        if ($extension === 'jpg' || $extension === 'jpeg') {
          $image = imagecreatefromjpeg($tempPath);
        } elseif ($extension === 'png') {
          $image = imagecreatefrompng($tempPath);
          imagepalettetotruecolor($image);
        } elseif ($extension === 'webp') {
          $image = imagecreatefromwebp($tempPath);
        } else {
          $image = null;
        }

        if ($image) {
          $width = imagesx($image);
          $heigth = imagesy($image);
          $newWidth = 300;
          $newHeight = floor($heigth * ($newWidth / $width));

          $tmpImage = imagescale($image, $newWidth, $newHeight);

          $filename = 'profiles/' . uniqid() . '.jpg';
          $fullPath = storage_path('app/public/' . $filename);

          if (!file_exists(storage_path('app/public/profiles'))) {
            mkdir(storage_path('app/public/profiles'), 0755, true);
          }

          imagejpeg($tmpImage, $fullPath, 60);
          imagedestroy($image);
          imagedestroy($tmpImage);
        }

        $profilePicturePath = $filename;
      } else {

        $profilePicturePath =  $data['profile_picture']->store('profiles', 'public');
      }


      // if($data['applicant_type'] === 'siswa'){
      //    // NISN 10–12 digit
      //     if (!preg_match('/^[0-9]{10,12}$/', $data['student_identifier'])) {
      //         throw new \Exception("NISN tidak valid (harus 10–12 digit).");
      //     }
      // }

      // if($data['applicant_type'] === 'mahasiswa'){
      //     // NIM alfanumerik (panjang 5–20)
      //     if (!preg_match('/^[A-Za-z0-9]{5,20}$/', $data['student_identifier'])) {
      //         throw new \Exception("NIM tidak valid.");
      //     }
      // }

      $updateData = [
        'full_name' => $data['full_name'],
        'phone' => $data['phone'],
        'profile_picture' => $profilePicturePath,
      ];

      if (!$isAdmin) {
        $updateData['applicant_type'] = $data['applicant_type'];
        $updateData['address'] = $data['address'];
        $updateData['bio'] = $data['bio'] ?? $profile->bio;
        $updateData['date_of_birth'] = $data['date_of_birth'];
      }

      $profile->update($updateData);

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Profile updated successfully.',
        'data' => $profile->fresh()
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update profile.',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function getProfileData(User $user)
  {
    $user->load(['profile.activeEducation']);

    $profile = $user->profile;
    $education = $profile?->activeEducation;

    return response()->json([
      'status' => 'success',
      'message' => 'Profile data retrieved successfully.',
      'data' => [
        'username' => $user->username,
        'email' => $user->email,
        'role' => $user->roles_name,
        'created_at' => $user->created_at,

        'full_name' => $user->profile->full_name ?? null,
        'phone' => $user->profile->phone ?? null,
        'address' => $user->profile->address ?? null,
        'bio' => $user->profile->bio ?? null,
        'applicant_type' => $user->profile->applicant_type ?? null,
        'profile_picture_url' => $user->profile->profile_picture_url ?? null,
        'date_of_birth' => $user->profile->date_of_birth ?? null,

        'education' => $education ? [
          'institution_name' => $education->institution_name,
          'major' => $education->major,
          'nim' => $education->nim,
          'nisn' => $education->nisn,
          'gpa' => $education->gpa,
          'average_score' => $education->average_score,
          'level' => $education->level,
        ] : null
      ]
    ]);
  }
}
