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

use Illuminate\Database\QueryException;

use App\Http\Controllers\Controller;

use App\Utilities\Response;

use App\Models\Faq;

use Exception;
use ErrorException;

class FaqController extends Controller
{
    use Response;

    public function index(Request $request)
    {
        $returnValue = [];
        $code = 400;

        try {
            $data = Faq::orderBy('id', 'ASC')->get()->toArray();

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

    public function store(Request $request)
    {
        $returnValue = [];
        $code = 400;

        $validator = Validator::make($request->all(), [
            'question' => 'required',
            'answer' => 'required',
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
            $data = new Faq;
            $data->question = $request->question;
            $data->answer = $request->answer;
            $data->save();

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
        }

        return response()->json($returnValue, $code);
    }

    public function update(Request $request, $id)
    {
        $returnValue = [];
        $code = 400;

        $validator = Validator::make($request->all(), [
            'question' => 'required',
            'answer' => 'required',
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
            $data = Faq::find($id);

            if (!$data) {
                throw new Exception("Data not found", 404);
            }

            $data->question = $request->question;
            $data->answer = $request->answer;
            $data->save();

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
        }

        return response()->json($returnValue, $code);
    }

    public function delete($id)
    {
        $returnValue = [];
        $code = 400;

        DB::beginTransaction();

        try {
            $data = Faq::find($id);

            if (!$data) {
                throw new Exception("Data not found", 404);
            }

            $data->delete();

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
        }

        return response()->json($returnValue, $code);
    }
}
