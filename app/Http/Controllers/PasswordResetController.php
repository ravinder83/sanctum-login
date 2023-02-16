<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function send_reset_password_email(Request $request)
    {
        $request->validate([
            'email'=>'required|email'
        ]);
        $email = $request->email;
        //Check User's email is exist or not
        $user = User::where('email',$email)->first();
        if(!$user)
        {
            return response()->json([
                'message'=>'Email Does Not Exists',
                'status'=>'Failed',
            ],404);
        }
        //Generate token
        $token = Str::random(60);

        // Saving data to password reset table
        PasswordReset::create([
            'email'=>$email,
            'token'=>$token,
            'created_at'=>Carbon::now(),
        ]);

        //Sending email with password reset view
        Mail::send('reset',['token'=>$token],function(Message $message)use($email){
            $message->subject('Reset Your Password');
            $message->to($email);
        });
        return response([
            'message'=>'Passsword Reset Email Send... Check Your Email',
            'status'=>'Success'
        ],200);
    }

    public function reset(Request $request,$token){
        // Delete token older than 1 minute
        $formatted = Carbon::now()->subMinutes(1)->toDateTimeString();
        PasswordReset::where('created_at','<=',$formatted)->delete();
        $request->validate([
            'password'=>'required|confirmed'
        ]);
        $passwordreset = PasswordReset::where('token',$token)->first();
        if(!$passwordreset){
            return response([
                'message'=>'Token Is Invalid Or Expired',
                'status'=>'Failed'
            ],404);
        }
        // Reset the password if a valid user or token exists
        $user = User::where('email',$passwordreset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        //Delete the token after resetting the password
        PasswordReset::where('email',$user->email)->delete();
        return response([
            'message'=>'Password Reset Successfully',
            'status'=>'Success'
        ],200);
    }
}
