<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  

    public function loginUser(Request $request)
{
    try {

        $validateUser = Validator::make($request->all(), 
        [
            'email' => 'required|email',
            'password' => 'required'
        ]);


        if($validateUser->fails()){
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }


        if(!Auth::attempt($request->only(['email', 'password']))){
            return response()->json([
                'message' => 'Email & Password do not match with our records.',
            ], 401);
        }

 
        $user = User::where('email', $request->email)->first();


        $firstLogin = false;
        $lastLogin = $user->last_login;


        if (!$lastLogin) {
            $firstLogin = true;
        }


        $user->update(['last_login' => Carbon::now()]);


        $message = $firstLogin 
            ? 'Welcome! This is your first login.' 
            : 'Welcome back! Your last login was on ' . $lastLogin->format('l, F j, Y \a\t g:i A');

    
        return response()->json([
            'user' => $user,
            'first_login' => $firstLogin,  
            'message' => $message,
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([ 
            'message' => $th->getMessage()
        ], 500);
    }
}


    public function logoutUser(Request $request)
    {
        try {
            
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated.'
                ], 401);
            }

         
            $user->tokens->each(function ($token) {
                $token->delete();
            });

  
            return response()->json([
                'message' => 'User logged out successfully'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([ 
                'message' => $th->getMessage()
            ], 500);
        }
    }


    

}
