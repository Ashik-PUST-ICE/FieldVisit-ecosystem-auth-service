<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePinRequest;
use App\Http\Requests\Auth\UpdateMnpRequest;
use App\Http\Requests\Auth\UpdateOtpChannelRequest;
use App\Http\Requests\Auth\UpdateSecurityPreferenceRequest;
use App\Models\UserSecuritySetting;
use App\Models\User;
use App\Services\Applications\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecuritySettingsController extends Controller
{
    public function show(Request $request)
    {
        $settings = $this->settings($request);

        return ApiResponse::success([
            'has_pin' => filled($settings->pin_hash),
            'mnp' => $settings->mnp ? '••••'.substr($settings->mnp, -4) : null,
            'otp_channel' => $settings->otp_channel,
            'biometric_enabled' => $settings->biometric_enabled,
            'randomize_pin_keyboard' => $settings->randomize_pin_keyboard,
        ], 'Security settings retrieved successfully');
    }

    public function changePin(ChangePinRequest $request)
    {
        $settings = $this->settings($request);
        if ($settings->pin_hash && ! Hash::check($request->string('current_pin')->toString(), $settings->pin_hash)) {
            return response()->json(['message' => 'Current PIN is incorrect'], 422);
        }
        $settings->pin_hash = Hash::make($request->string('new_pin')->toString());
        $settings->save();

        return ApiResponse::success(null, 'PIN changed successfully');
    }

    public function updateMnp(UpdateMnpRequest $request)
    {
        $settings = $this->settings($request);
        $settings->mnp = $request->string('mnp')->toString();
        $settings->save();

        return ApiResponse::success(null, 'MNP updated successfully');
    }

    public function updateOtpChannel(UpdateOtpChannelRequest $request)
    {
        $settings = $this->settings($request);
        $settings->otp_channel = $request->string('channel')->toString();
        $settings->save();

        return ApiResponse::success(null, 'SMS/OTP channel updated successfully');
    }

    public function updateBiometric(UpdateSecurityPreferenceRequest $request)
    {
        $settings = $this->settings($request);
        $settings->biometric_enabled = $request->boolean('enabled');
        $settings->save();

        return ApiResponse::success(null, 'Biometric preference updated successfully');
    }

    public function updateRandomKeyboard(UpdateSecurityPreferenceRequest $request)
    {
        $settings = $this->settings($request);
        $settings->randomize_pin_keyboard = $request->boolean('enabled');
        $settings->save();

        return ApiResponse::success(null, 'Randomized PIN keyboard preference updated successfully');
    }

    private function settings(Request $request): UserSecuritySetting
    {
        $userId = authId();
        abort_unless($userId && User::whereKey($userId)->exists(), 401, 'Authenticated user not found');

        return UserSecuritySetting::firstOrCreate(['user_id' => $userId]);
    }
}
