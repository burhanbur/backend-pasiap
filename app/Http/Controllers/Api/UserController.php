<?php

namespace App\Http\Controllers\Api;

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

use App\Http\Controllers\Controller;

use App\Utilities\Response;

use App\Models\FcmToken;
use App\Models\LogProfile;
use App\Models\Profile;
use App\Models\User;

use Tymon\JWTAuth\Facades\JWTAuth;

use Exception;
use ErrorException;

class UserController extends Controller
{
    use Response;

    /**
     * @OA\Get(
     *    path="/profiles",
     *    operationId="getProfile",
     *    tags={"Profiles"},
     *    description="Get data profile account",
     *    security={{"bearerAuth": {}}},
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function getProfile(Request $request)
    {
        $data = [];
        $returnValue = [];
        $path = 'assets/';
        $code = 400;

        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();

            $id = $payload['sub'];

            $user = User::select('id', 'name', 'username', 'email')->where('id', $id)->first();
            $profile = Profile::select('sid', 'birth_place', 'birth_date', 'sex', 'religion', 'marital_status', 'phone', 'identity_card_photo', 'photo')->where('user_id', $id)->first();

            $data = [
                'user_id' => $user->id,
                'full_name' => $user->name,
                'email' => $user->email,
                'sid' => $profile->sid,
                'birth_place' => $profile->birth_place,
                'birth_date' => $profile->birth_date,
                'sex' => $profile->sex,
                'religion_id' => $profile->religion,
                'religion_name' => @$profile->getReligion->name,
                'marital_status' => $profile->marital_status,
                'phone' => $profile->phone,
                'identity_card_photo' => ($profile->identity_card_photo) ? URL::to('/') . '/' . $path . 'identity_card_photo/' . $profile->identity_card_photo : '',
                'photo' => ($profile->photo) ? URL::to('/') . '/' . $path . 'photo/' . $profile->photo : ''
            ];

            $code = 200;
            $returnValue = [
                'success' => true, 
                'data' => $data,
                'url' => $this->endpoint()
            ];            
        } catch (Exception $ex) {
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    /**
     * @OA\Post(
     *    path="/profiles",
     *    operationId="updateProfile",
     *    tags={"Profiles"},
     *    description="Update data profile account",
     *    security={{"bearerAuth": {}}},
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(property="name", type="string"),
     *                @OA\Property(property="phone", type="string"),
     *                @OA\Property(property="sid", type="string"),
     *                @OA\Property(property="birth_place", type="string"),
     *                @OA\Property(property="birth_date", type="string"),
     *                @OA\Property(property="sex", type="string"),
     *                @OA\Property(property="religion", type="string"),
     *                @OA\Property(property="marital_status", type="string"),
     *                @OA\Property(property="identity_card_photo", type="string"),
     *                @OA\Property(property="photo", type="string"),
     *            ),
     *        ),
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function updateProfile(Request $request)
    {
        $returnValue = [];

        $path = 'assets/';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'sid' => 'required|string|size:16',
            'identity_card_photo' => 'nullable|string',
            'photo' => 'nullable|string',
            // 'identity_card_photo' => 'file|mimes:jpeg,jpg,png|max:2048',
            // 'photo' => 'file|mimes:jpeg,jpg,png|max:2048',
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
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();

            $id = $payload['sub'];

            // check log profile
            $month = date('m');
            $log = LogProfile::where(arra('user_id' => $id, 'column' => 'name'))->whereMonth('created_at', date('m'))->whereYear('created_at', date('y'))->exists();

            if ($log) {
                throw new Exception("Cannot update your name in this month", 1);
            }

            $user = User::find($id);

            if ($user->name != $request->name) {
                LogProfile::create([
                    'user_id' => $id,
                    'updated_by' => $id,
                    'column' => 'name',
                    'old_value' => $user->name,
                    'new_value' => $request->name
                ]);
            }

            $user->name = $request->name;
            $user->save();

            $profile = Profile::where('user_id', $user->id)->first();
            $profile->sid = $request->sid;
            $profile->full_name = $user->name;
            $profile->birth_place = $request->birth_place;
            $profile->birth_date = date('Y-m-d', strtotime($request->birth_date));
            $profile->sex = $request->sex;
            $profile->religion = $request->religion;
            $profile->marital_status = $request->marital_status;
            $profile->phone = $request->phone;

            if ($request->identity_card_photo) {
                $image = base64_decode($request->identity_card_photo);

                $file_image = str_replace(' ', '_', $user->id . '_' . strtotime(date('Y-m-d H:i:s')));

                $finfo = finfo_open();
                $mimeType = finfo_buffer($finfo, $image, FILEINFO_MIME_TYPE);
                finfo_close($finfo);

                $format = [
                    'image/jpg' => 'jpg', 
                    'image/jpeg' => 'jpeg', 
                    'image/png' => 'png'
                ];

                if (isset($format[$mimeType])) {
                    $extension = $format[$mimeType];
                } else {
                    throw new Exception("The photo must be a file of type: jpeg, jpg, png.", 400);
                }

                $file_image .= '.' . $extension;

                $image_size = strlen($image);

                if ($image_size > 2097152) {
                    throw new Exception("The photo must not be greater than 2048 kilobytes.", 400);
                }

                $file_path = public_path('assets') . '/identity_card_photo/' . $file_image;
                file_put_contents($file_path, $image);
                $profile->identity_card_photo = $file_image;
            }

            if ($request->photo) {
                $image = base64_decode($request->photo);

                $file_photo = str_replace(' ', '_', $user->id . '_' . strtotime(date('Y-m-d H:i:s')));

                $finfo = finfo_open();
                $mimeType = finfo_buffer($finfo, $image, FILEINFO_MIME_TYPE);
                finfo_close($finfo);

                $format = [
                    'image/jpg' => 'jpg', 
                    'image/jpeg' => 'jpeg', 
                    'image/png' => 'png'
                ];

                if (isset($format[$mimeType])) {
                    $extension = $format[$mimeType];
                } else {
                    throw new Exception("The photo must be a file of type: jpeg, jpg, png.", 400);
                }

                $file_photo .= '.' . $extension;

                $image_size = strlen($image);

                if ($image_size > 2097152) {
                    throw new Exception("The photo must not be greater than 2048 kilobytes.", 400);
                }

                $file_path = public_path('assets') . '/photo/' . $file_photo;
                file_put_contents($file_path, $image);
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

            $code = 200;
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
        } catch (ErrorException $ex) {
            DB::rollback();
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    /**
     * @OA\Post(
     *    path="/tokens",
     *    operationId="storeTokenFcm",
     *    tags={"Profiles"},
     *    description="Store token firebase into profile account",
     *    security={{"bearerAuth": {}}},
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(property="token", type="string"),
     *            ),
     *        ),
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function storeTokenFcm(Request $request)
    {
        $code = 400;
        $returnValue = [];

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if($validator->fails()){
            $returnValue = [
                'success' => false,
                'message' => $validator->errors(),
                'url' => $this->endpoint()
            ];

            return response()->json($returnValue, $code);
        }

        DB::beginTransaction();

        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();

            $data = FcmToken::create([
                'user_id' => $payload['sub'],
                'token' => $request->token
            ]);

            DB::commit();

            $code = 200;
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
}