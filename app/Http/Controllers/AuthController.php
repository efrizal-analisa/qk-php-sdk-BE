<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
$vendorDir = dirname(dirname(dirname(dirname(__FILE__))));
require_once $vendorDir . '/vendor/quickey/quickey-php-sdk/src/QK.php';

class AuthController extends Controller
{
    public function register(Request $request)
    {
        return User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password'))
        ]);
    }

    public function login(Request $request)
    {
        if ($request->provider) {
            try {
                $app = \SDK\QK\Quickey::auth($request->email);
                var_dump($app->access_token);
                // return response()->json($app->access_token);
            } catch (\Throwable $th) {
                echo $th;
            }

        } else {

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response([
                    'message' => 'Invalid credentials!'
                ], Response::HTTP_UNAUTHORIZED);
            }
    
            $user = Auth::user();
    
            $token = $user->createToken('token')->plainTextToken;
    
            $cookie = cookie('jwt', $token, 60 * 24); // 1 day
    
            return response([
                'message' => $token
            ])->withCookie($cookie);
        }
    }

    public function user()
    {
        return Auth::user();
    }

    public function logout()
    {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success'
        ])->withCookie($cookie);
    }
}
