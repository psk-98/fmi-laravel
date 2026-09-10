<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): UserResource
    {
        $user = $request->user();
        $user->loadSum('galleryImages as storage_used_bytes', 'file_size');

        return new UserResource($user);
    }
}
