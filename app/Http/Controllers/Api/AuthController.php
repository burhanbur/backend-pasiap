<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use Illuminate\Contracts\Encryption\DecryptException;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Http\Controllers\Controller;
use App\Utilities\Response;
use App\Models\User;
use App\Models\Profile;

class AuthController extends Controller
{
    use Response;

    public function login(Request $request)
    {
    	$returnValue = [];

    	$validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
        	$returnValue = [
        		'success' => false,
        		'message' => $validator->errors()
        	];

            return response()->json($returnValue, 400);
        }

    	$credentials = $request->only('username', 'password');

    	try {
            $user = User::where('username', $credentials['username'])->first();

            if (!$user) {
                return $this->unauthorized();
            }

            $role = null;
            $roles = $user->getRoleNames();

            foreach ($roles as $key => $value) {
                $role = $value;
            }

            $customClaims = array(
                'name' => $user->name,
                'role' => $role
            );

            $token = JWTAuth::claims($customClaims)->attempt($credentials);

	        if (!$token) {
                return $this->unauthorized();
	        } else {
		        $code = 200;
	        	$returnValue = [
		        	'success' => true, 
		        	'token' => $token,
                    'url' => $this->endpoint()
		        ];
	        }
    	} catch (JWTException $ex) {
	        return $this->error($ex);
    	}

        return response()->json($returnValue, $code);
    }

    public function register(Request $request)
    {
    	$returnValue = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed',
            'phone' => 'required|string',
            'sid' => 'required|string|size:16',
        ]);

        if($validator->fails()){
        	$returnValue = [
        		'success' => false,
        		'message' => $validator->errors()
        	];

            return response()->json($returnValue, 400);
        }

        DB::beginTransaction();

        try {
	        $user = User::create([
	        	'name' => $request->name,
                'username' => $request->username,
	        	'email' => $request->email,
	        	'password' => Hash::make($request->password)
	        ]);

            $user->assignRole('user');

            $profile = Profile::create([
                'user_id' => $user->id,
                'sid' => $request->sid,
                'email' => $user->email,
                'full_name' => $user->name,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'sex' => $request->sex,
                'religion' => $request->religion,
                'martial_status' => $request->martial_status,
                'phone' => $request->phone,
                'identity_card_photo' => $request->identity_card_photo,
                'photo' => $request->photo
            ]);

            $data = [
                'sid' => $request->sid,
                'email' => $user->email,
                'full_name' => $user->name,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'sex' => $request->sex,
                'religion' => $request->religion,
                'martial_status' => $request->martial_status,
                'phone' => $request->phone,
            ];

	        $code = 201;
	        $returnValue = [
	        	'success' => true, 
	        	'data' => $data,
                'url' => $this->endpoint()
	        ];

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
	        return $this->error($ex);
        } catch (QueryException $ex) {
            DB::rollback();
            return $this->error($ex);
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
                'success' => true,
                'data' => $refreshed
            );
        } catch (JWTException $ex) {
            return $this->error($ex);
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
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }
}
