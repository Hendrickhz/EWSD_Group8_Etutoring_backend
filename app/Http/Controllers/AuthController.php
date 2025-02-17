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
            // Validate user input
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // If validation fails, return errors
            if($validateUser->fails()){
                return response()->json([
                    
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            // Attempt login
            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                   
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            // Fetch the user and return token
            $user = User::where('email', $request->email)->first();
            $user->update(['last_login'=>Carbon::now()]);
            return response()->json([
                'user' => $user,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            // Handle any exceptions
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
