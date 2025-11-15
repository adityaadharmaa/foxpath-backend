<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\Profile\ProfileServices;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public $profileService;
    public function __construct(ProfileServices $profileService)
    {
        $this->profileService = $profileService;
    }

    public function updateProfile(Request $request)
    {
        return $this->profileService->updateProfile($request->user(), $request->all());
    }
}
