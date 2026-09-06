<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function requestOtp(Request $r, OtpService $otp)
    {
        $data = $r->validate(['mobile' => ['required', 'regex:/^09\d{9}$/']]);
        $otp->issue($data['mobile']);

        return response()->json(['message' => 'کد ورود ارسال شد.', 'expires_in' => 120]);
    }

    public function verify(Request $r, OtpService $otp)
    {
        $data = $r->validate(['mobile' => ['required', 'regex:/^09\d{9}$/'], 'code' => ['required', 'digits:5'], 'device_name' => ['nullable', 'string', 'max:100']]);
        $otp->consume($data['mobile'], $data['code']);
        [$user,$plain] = DB::transaction(function () use ($data) {
            $user = User::firstOrCreate(['mobile' => $data['mobile']], ['mobile_verified_at' => now()]);
            $user->update(['mobile_verified_at' => $user->mobile_verified_at ?? now()]);
            $plain = Str::random(64);
            $user->tokens()->create(['name' => $data['device_name'] ?? 'web', 'token' => hash('sha256', $plain), 'expires_at' => now()->addDays(30)]);

            return [$user, $plain];
        });

        return response()->json(['token' => $plain, 'token_type' => 'Bearer', 'is_new_user' => ! $user->profile_completed_at, 'user' => $user->load('roles')]);
    }

    public function me(Request $r)
    {
        return response()->json($r->user()->load('roles'));
    }

    public function updateProfile(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:100', 'email' => 'nullable|email|unique:users,email,'.$r->user()->id, 'national_code' => ['nullable', 'digits:10', 'unique:users,national_code,'.$r->user()->id], 'birth_date' => 'nullable|date']);
        $r->user()->update($data + ['profile_completed_at' => now()]);

        return response()->json($r->user()->fresh());
    }

    public function logout(Request $r)
    {
        $r->attributes->get('access_token')->delete();

        return response()->noContent();
    }
}
