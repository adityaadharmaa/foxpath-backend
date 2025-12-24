<?php

namespace App\Http\Controllers\ProfileEducation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileEducation\StoreProfileEducation;
use App\Http\Requests\ProfileEducation\UpdateProfileEducationRequest;
use App\Models\Profile;
use App\Models\ProfileEducation;
use App\Services\ProfileEducation\ProfileEducationService;
use Illuminate\Http\Request;

class ProfileEducationController extends Controller
{
    public function __construct(
        protected ProfileEducationService $service
    )
    {}

    public function index()
    {
        $profile = Profile::where('users_id', auth()->id())->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $this->service->index($profile)
        ]);
    }

    public function store(StoreProfileEducation $request)
    {
        return $this->service->store($request);
    }

    public function show()
    {
        return $this->service->show();
    }
}
