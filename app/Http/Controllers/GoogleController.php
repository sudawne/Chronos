<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => bcrypt(str()->random(16)) 
                ]
            );

            Auth::login($user);
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Đăng nhập Google thất bại!');
        }
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            $email = $facebookUser->email ?? $facebookUser->id . '@facebook.com';

            $user = User::updateOrCreate(
                ['facebook_id' => $facebookUser->id], 
                [
                    'name' => $facebookUser->name,
                    'email' => $email,
                    'avatar' => $facebookUser->avatar,
                    'password' => bcrypt(str()->random(16)) 
                ]
            );

            Auth::login($user);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            dd($e->getMessage()); 
        }
    }
}