<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadPhotoRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfilePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ProfilePhotoService $photoService) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()->load(['role', 'agency', 'admin'])),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $userFields = array_intersect_key($validated, array_flip([
            'name', 'email', 'phone', 'date_of_birth', 'address'
        ]));

        if (! empty($userFields)) {
            $user->update($userFields);
        }

        // Handle agency fields if present and user has an agency / is admin
        $agencyFields = [
            'initials' => $validated['initials'] ?? null,
            'name' => $validated['agency_name'] ?? null,
            'type' => $validated['type'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'email' => $validated['agency_email'] ?? null,
            'phone' => $validated['agency_phone'] ?? null,
            'region_code' => $validated['region_code'] ?? null,
            'province_code' => $validated['province_code'] ?? null,
            'municipality_code' => $validated['municipality_code'] ?? null,
            'barangay_code' => $validated['barangay_code'] ?? null,
            'region_name' => $validated['region_name'] ?? null,
            'province_name' => $validated['province_name'] ?? null,
            'municipality_name' => $validated['municipality_name'] ?? null,
            'barangay_name' => $validated['barangay_name'] ?? null,
            'custom_address' => $validated['custom_address'] ?? null,
            'color' => $validated['color'] ?? null,
        ];

        $hasAgencyInput = collect($agencyFields)->filter(fn ($v) => filled($v))->isNotEmpty();

        if ($hasAgencyInput) {
            $agencyData = \App\Support\PsgcLocation::applyAddress(
                collect($agencyFields)->filter(fn ($v) => $v !== null)->all()
            );

            if ($user->agency) {
                $user->agency->update($agencyData);
            } elseif ($user->isAdmin()) {
                $newAgency = \App\Models\Agency::create(array_merge([
                    'initials' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $user->name) ?: 'AGY', 0, 4)),
                    'name' => $user->name . "'s Agency",
                    'type' => 'Government Agency',
                    'status' => 'Active',
                ], $agencyData));
                $user->update(['agency_id' => $newAgency->id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user->fresh()->load(['role', 'agency', 'admin'])),
        ]);
    }

    public function uploadPhoto(UploadPhotoRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->photoService->upload($user, $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Profile photo uploaded successfully.',
            'data' => [
                'profile_photo_url' => \App\Support\SignedMediaUrl::profilePhoto($user->fresh()),
            ],
        ]);
    }

    public function removePhoto(Request $request): JsonResponse
    {
        $this->photoService->delete($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Profile photo removed successfully.',
        ]);
    }
}
