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

use App\Models\RefReligion;
use App\Models\RefReportStatus;

use Exception;
use ErrorException;

class DropdownController extends Controller
{
    use Response;

    public function getAllReligions(Request $request)
    {
        $returnValue = [];
        $code = 400;

        try {
            $data = RefReligion::select('id', 'name')->orderBy('id', 'ASC')->get()->toArray();

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

    public function getAllStatus(Request $request)
    {
        $returnValue = [];
        $code = 400;

        try {
            $data = RefReportStatus::select('id', 'name')->orderBy('id', 'ASC')->get()->toArray();

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

    public function getAllSexs()
    {
        $data = [];
        $data[0] = ['name' => 'Laki-laki'];
        $data[1] = ['name' => 'Wanita'];

        $code = 200;
        $returnValue = [
            'success' => true, 
            'data' => $data,
            'url' => $this->endpoint()
        ];

        return response()->json($returnValue, $code);
    }

    public function getAllMaritalStatus()
    {
        $data = [];
        $data[0] = ['name' => 'Lajang'];
        $data[1] = ['name' => 'Menikah'];
        $data[2] = ['name' => 'Cerai Hidup'];
        $data[3] = ['name' => 'Cerai Mati'];

        $code = 200;
        $returnValue = [
            'success' => true, 
            'data' => $data,
            'url' => $this->endpoint()
        ];

        return response()->json($returnValue, $code);
    }
}