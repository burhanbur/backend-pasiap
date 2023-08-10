<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Contracts\Encryption\DecryptException;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Http\Controllers\Controller;
use App\Traits\ErrorMessage;
use App\Models\User;

class AuthController extends Controller
{
    use ErrorMessage;

    public function login(Request $request)
    {
    	$returnValue = [];

    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
        	$returnValue = [
        		'error' => true,
        		'message' => $validator->errors()
        	];

            return response()->json($returnValue, 400);
        }

    	$credentials = $request->only('email', 'password');

    	try {
    		$token = JWTAuth::attempt($credentials);

	        if (!$token) {
	        	$code = 401;
	        	$returnValue = [
	        		'error' => true,
	        		'message' => 'Invalid credentials'
	        	];
	        } else {
                // $data = User::where('email', $credentials['email'])->first();

                // $customClaims = array(
                //     'user_id' => $data->id
                // );

                // $token = JWTAuth::claims($customClaims)->attempt($credentials);

		        $code = 200;
	        	$returnValue = [
		        	'error' => false, 
		        	'data' => $token
		        ];
	        }
    	} catch (JWTException $ex) {
	        $code = 500;
    		$returnValue = [
	        	'error' => true, 
	        	'message' => $this->errMessage($ex)
	        ];
    	}

        return response()->json($returnValue, $code);
    }

    public function register(Request $request)
    {
    	$returnValue = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        if($validator->fails()){
        	$returnValue = [
        		'error' => true,
        		'message' => $validator->errors()
        	];

            return response()->json($returnValue, 400);
        }

        try {
	        $user = User::create([
	        	'name' => $request->name,
	        	'email' => $request->email,
	        	'password' => Hash::make($request->password)
	        ]);

	        $code = 201;
	        $returnValue = [
	        	'error' => false, 
	        	'data' => $user
	        ];
        } catch (Exception $ex) {
	        $code = 500; 
    		$returnValue = [
	        	'error' => true, 
	        	'message' => $this->errMessage($ex)
	        ];       	
        }

        return response()->json($returnValue, $code);
    }

    public function refreshToken(Request $request)
    {
    	$returnValue = [];

        try {
            if (!JWTAuth::getToken()) {
                $refreshed = JWTAuth::refresh($request->get('token'));
            } else {                
                $refreshed = JWTAuth::refresh(JWTAuth::getToken());
            }

            $code = 200;
            $returnValue = array(
                'error' => false,
                'data' => $refreshed
            );
        } catch (JWTException $ex) {
            $code = 500; 
    		$returnValue = [
	        	'error' => true, 
	        	'message' => $this->errMessage($ex)
	        ]; 
        }

        return response()->json($returnValue, $code);
    }

    public function logout(Request $request)
    {
    	$returnValue = [];
        $token = $request->header('Authorization');
        
        try {
            JWTAuth::parseToken()->invalidate($token);

            $code = 200;
            $returnValue = [
                'success' => true,
                'message' => 'You have logged out'
            ];
        } catch (JWTException $ex) {
        	$code = 500;
            $returnValue = [
	        	'error' => true, 
	        	'message' => $this->errMessage($ex)
	        ]; 
        }

        return response()->json($returnValue, $code);
    }
}
