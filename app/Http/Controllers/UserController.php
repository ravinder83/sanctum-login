<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required|confirmed',
            // password_confirmation (field used for confirm password)
            'tc'=>'required',
        ]);

        //check whether a user is already exists or not
        if(User::where('email',$request->email)->first())
        {
            return response()->json([
                'message'=>'Email Already Exists',
                'status'=>'Failed',
            ],200);
        }

        //creating user with token
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'tc'=>json_decode($request->tc),
        ]);
        $token = $user->createToken($request->email)->plainTextToken;
        return response()->json([
            'token'=>$token,
            'message'=>'Registration Success',
            'status'=>'Success',
        ],201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);
        $user = User::where('email',$request->email)->first();
        if($user && Hash::check($request->password,$user->password)){
            $token = $user->createToken($request->email)->plainTextToken;
            return response()->json([
                'token'=>$token,
                'message'=>'Login Success',
                'status'=>'Success',
            ],200);
        }
        return response()->json([
            'message'=>'The Provided Credientials Are Incorrect',
            'status'=>'Failed',
        ],401);
    }

    public function logout(){
        // return auth('sanctum')->user();
        // at the time of logout delete the token()
        auth()->user()->tokens()->delete();
        // auth('sanctum')->user()->tokens()->delete();
        return response()->json([
            'message'=>'Logout Success',
            'status'=>'Success',
        ],200);
    }
    public function logged_user(){
        $logged_user = auth()->user();
        // fetching logged in user data with token() info
        return response()->json([
            'user'=>$logged_user,
            'message'=>'Logged User Data',
            'status'=>'Success',
        ],200);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'password'=>'required|confirmed',
        ]);
        // changed password when user is currently logged in.
        $logged_user = auth()->user();
        $logged_user->password = Hash::make($request->password);
        $logged_user->save();
        return response()->json([
            'message'=>'Password Changed Successfully',
            'status'=>'Success',
        ],200);
    }
}
