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

    public function getAllFaq(Request $request)
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
}
