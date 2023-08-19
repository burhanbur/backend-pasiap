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
use App\Models\Profile;
use App\Models\User;

use Tymon\JWTAuth\Facades\JWTAuth;

use Exception;
use ErrorException;

class UserController extends Controller
{
    use Response;

    public function getProfile(Request $request, $id)
    {
        $data = [];
        $returnValue = [];
        $path = 'assets/';
        $code = 400;

        try {
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

    public function updateProfile(Request $request, $id)
    {
        $returnValue = [];

        $path = 'assets/';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'sid' => 'required|string|size:16',
            'identity_card_photo' => 'file|mimes:jpeg,png|max:2048',
            'photo' => 'file|mimes:jpeg,png|max:2048',
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