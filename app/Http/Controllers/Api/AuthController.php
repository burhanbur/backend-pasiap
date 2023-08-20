<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Mails\VerifyMail;

use App\Models\User;
use App\Models\Profile;

use App\Utilities\Response;

use Illuminate\Http\Request;
 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use Illuminate\Database\QueryException;

use Illuminate\Contracts\Encryption\DecryptException;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

use Exception;
use ErrorException;

class AuthController extends Controller
{
    use Response;

    /**
     * @OA\Post(
     *    path="/login",
     *    operationId="login",
     *    tags={"Authentications"},
     *    description="Return token to login into the apps",
     *    @OA\Parameter(name="username", in="query", required=true,
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(name="password", in="query", required=true,
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *        @OA\JsonContent(
     *           @OA\Property(property="success", type="boolean", example="true"),
     *           @OA\Property(property="token",type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL3Bhc2lhcFwvcHVibGljXC9hcGlcL2xvZ2luIiwiaWF0IjoxNjkyNDY3NTY3LCJleHAiOjE2OTI0NzExNjcsIm5iZiI6MTY5MjQ2NzU2NywianRpIjoiNkU1ZUZybklDVjlqckFXdSIsInN1YiI6MiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsIm5hbWUiOiJUd2luIEVkbyBOdWdyYWhhIiwicm9sZSI6InVzZXIifQ.zyhJ7FN_Z2L41LrgMXL4LhWpPj_gUvQMt68UH3c2DGw"),
     *           @OA\Property(property="url",type="string", example="http://localhost/pasiap/public/api/login")
     *        )
     *    )
     *  )
     */
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
        		'message' => $validator->errors(),
                'url' => $this->endpoint()
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
    	} catch (QueryException $ex) {
            return $this->error($ex);
        } catch (ErrorException $ex) {
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    /**
     * @OA\Post(
     *    path="/register",
     *    operationId="register",
     *    tags={"Authentications"},
     *    description="Register account",
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(property="name", type="string"),
     *                @OA\Property(property="username", type="string"),
     *                @OA\Property(property="email", type="string"),
     *                @OA\Property(property="password", type="string"),
     *                @OA\Property(property="password_confirmation", type="string"),
     *                @OA\Property(property="phone", type="string"),
     *                @OA\Property(property="sid", type="string"),
     *                @OA\Property(property="birth_place", type="string"),
     *                @OA\Property(property="birth_date", type="string"),
     *                @OA\Property(property="sex", type="string"),
     *                @OA\Property(property="religion", type="string"),
     *                @OA\Property(property="marital_status", type="string"),
     *                @OA\Property(property="identity_card_photo", type="file"),
     *                @OA\Property(property="photo", type="file"),
     *            ),
     *        ),
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function register(Request $request)
    {
    	$returnValue = [];

        $path = 'assets/';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed',
            'phone' => 'required|string',
            'sid' => 'required|string|size:16',
            'identity_card_photo' => 'file|mimes:jpeg,jpg,png|max:2048',
            'photo' => 'file|mimes:jpeg,jpg,png|max:2048',
        ]);

        if($validator->fails()){
        	$returnValue = [
        		'success' => false,
        		'message' => $validator->errors(),
                'url' => $this->endpoint()
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

            $profile = new Profile;
            $profile->user_id = $user->id;
            $profile->sid = $request->sid;
            $profile->email = $user->email;
            $profile->full_name = $user->name;
            $profile->birth_place = $request->birth_place;
            $profile->birth_date = date('Y-m-d', strtotime($request->birth_date));
            $profile->sex = $request->sex;
            $profile->religion = $request->religion;
            $profile->marital_status = $request->marital_status;
            $profile->phone = $request->phone;

            if ($request->file('identity_card_photo')) {
                $image = $request->file('identity_card_photo');
                $file_image = str_replace(' ', '_', $user->id . '_' . $image->getClientOriginalName());
                $image->move($path . 'identity_card_photo', $file_image);
                $profile->identity_card_photo = $file_image;
            }

            if ($request->file('photo')) {
                $photo = $request->file('photo');
                $file_photo = str_replace(' ', '_', $user->id . '_' . $photo->getClientOriginalName());
                $photo->move($path . 'photo', $file_photo);
                $profile->photo = $file_photo;
            }

            $profile->save();

            $data = [
                'sid' => $request->sid,
                'email' => $user->email,
                'full_name' => $user->name,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'sex' => $request->sex,
                'religion' => $request->religion,
                'marital_status' => $request->marital_status,
                'phone' => $request->phone,
                'identity_card_photo' => ($profile->identity_card_photo) ? URL::to('/') . '/' . $path . 'identity_card_photo/' . $profile->identity_card_photo : '',
                'photo' => ($profile->photo) ? URL::to('/') . '/' . $path . 'photo/' . $profile->photo : '',
            ];

            $dataEmail = [
                'user_id' => $user->id,
                'url' => route('pendaftaran.verify', encrypt($user->id)),
                'name' => $request->name,
            ];

            // send mail verifikasi user
            $mail = new VerifyMail($dataEmail);
            Mail::to($request->email)->send($mail);

            DB::commit();

            $code = 201;
            $returnValue = [
                'success' => true, 
                'data' => $data,
                'url' => $this->endpoint()
            ];
        } catch (Exception $ex) {
            DB::rollback();
	        return $this->error($ex);
        } catch (QueryException $ex) {
            DB::rollback();
            return $this->error($ex);
        } catch (ErrorException $ex) {
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
                'token' => $refreshed,                
                'url' => $this->endpoint()
            );
        } catch (JWTException $ex) {
            return $this->error($ex);
        } catch (QueryException $ex) {
            DB::rollback();
            return $this->error($ex);
        } catch (ErrorException $ex) {
            DB::rollback();
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    public function changePassword(Request $request, $id)
    {
        $returnValue = [];

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|confirmed',
        ]);

        if($validator->fails()){
            $returnValue = [
                'success' => false,
                'message' => $validator->errors(),
                'url' => $this->endpoint()
            ];

            return response()->json($returnValue, 400);
        }

        DB::beginTransaction();

        try {
            $user = User::find($id);
            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();

            $code = 201;
            $returnValue = [
                'success' => true, 
                'data' => $data,
                'url' => $this->endpoint()
            ];
        } catch (Exception $ex) {
            DB::rollback();
            return $this->error($ex);
        } catch (QueryException $ex) {
            DB::rollback();
            return $this->error($ex);
        } catch (ErrorException $ex) {
            DB::rollback();
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
                'message' => 'You have logged out',
                'url' => $this->endpoint()
            ];
        } catch (JWTException $ex) {
            return $this->error($ex);
        } catch (QueryException $ex) {
            return $this->error($ex);
        } catch (ErrorException $ex) {
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    public function verify($id)
    {
        $userId = decrypt($id);

        $user = User::find($userId);

        if (!$user) {
            $code = 404;
            $returnValue = [
                'success' => false,
                'message' => 'User not found',
                'url' => $this->endpoint()
            ];

            return response()->json($returnValue, $code);
        }

        if (!$user->email_verified_at) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();
        }

        return redirect()->route('verified', ['name' => $user->name]);
    }
}
