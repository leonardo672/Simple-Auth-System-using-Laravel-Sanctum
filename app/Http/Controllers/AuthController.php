<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use App\Models\RefreshToken;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Auth\Events\Registered; 
use Illuminate\Support\Facades\Log; 

class AuthController extends Controller
{
    use HttpResponses;

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => null, // Initially not verified
        ]);

        event(new Registered($user));
        $this->generateVerificationCode($user);

        return $this->success(['message' => 'User registered. Verification code sent to email.']);
    }

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return $this->error('', 'Invalid credentials.', 401);
        }

        $user = User::where('email', $request->email)->first();

        if (empty($user->email_verified_at)) {
            $this->generateVerificationCode($user);
            return $this->error('', 'Email not verified. A new verification code has been sent.', 403);
        }
       
        $accessToken = $user->createToken('API Token of ' . $user->name)->plainTextToken;
        $refreshToken = Str::random(60);
        RefreshToken::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => hash('sha256', $refreshToken), 'expires_at' => now()->addDays(7)]
        );
      
        return $this->success([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ]);
    }

    public function resendCode(Request $request)
    {
      
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('', 'User not found.', 404);
        }
        if (!is_null($user->email_verified_at)) {
            return $this->error('', 'User already verified.', 422);
        }

        VerificationCode::where('user_id', $user->id)->delete();
        $this->generateVerificationCode($user);

        return $this->success(['message' => 'New verification code sent.']);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return $this->error('', 'User not found.', 404);
        }
    
        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)  
            ->where('expires_at', '>', now())  
            ->first();
    
        if (!$verificationCode) {
            return $this->error('', 'Invalid or expired verification code.', 422);
        }
    
        $user->update(['email_verified_at' => now()]);
        Log::info('Email Verified Successfully', ['user_id' => $user->id, 'email_verified_at' => $user->email_verified_at]);
        VerificationCode::where('user_id', $user->id)->delete();
    
        return $this->success(['message' => 'Email verified successfully.']);
    }
    
    public function refreshToken(Request $request)
    {
        $request->validate(['refresh_token' => 'required']);
        $hashed = hash('sha256', $request->refresh_token);
        $tokenRecord = RefreshToken::where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return $this->error('', 'Invalid or expired refresh token.', 403);
        }

        $user = User::find($tokenRecord->user_id);
        $newAccessToken = $user->createToken('API Token of ' . $user->name)->plainTextToken;
        return $this->success([
            'access_token' => $newAccessToken
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        RefreshToken::where('user_id', $request->user()->id)->delete();
        return $this->success(['message' => 'Logged out successfully.']);
    }

    protected function generateVerificationCode(User $user)
    {
        $code = random_int(100000, 999999);

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
        Mail::to($user->email)->send(new VerificationCodeMail($code));
    }
}
