<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Authentication extends Controller
{
    public function login(Request $request){
        $validator = Validator::make(request()->all(),[
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = User::where('email',$request->email);
            $userGet = $user->first();
            $token = $userGet->createToken('AppsToken',expiresAt:now()->addDays(2))->plainTextToken;
            return response()->json([
                'message' => "Login Success",
                'token' => $token,
                'user' => $userGet,
            ],200);
        }else{
            return response()->json([
                'message' => "Email/Password salah silahkan coba lagi!",
            ],404);
        }
    }

    public function register(Request $request){
        $validator = Validator::make(request()->all(),[
            'name' => ['required','max:255'],
            'email' => ['required','max:255','email','unique:users'],
            'password' => ['required','max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Validator Fails",
                'error' => $validator->errors()
            ],400);
        }

        $password = Hash::make($request->password);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password'=> $password,
        ];

        $user = User::create($data);

        $token = $user->createToken('AppsToken',expiresAt:now()->addDays(2))->plainTextToken;
        $userGet = User::all()->where('id',$user->id)->first();
        return response()->json([
            'message' => "Login Success",
            'token' => $token,
            'data' => $userGet,
        ], 200);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => "Logout berhasil"
        ]);
    }
}
