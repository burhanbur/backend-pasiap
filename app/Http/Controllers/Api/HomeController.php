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

use App\Services\FirebaseService;

use App\Models\Report;
use App\Models\LogReport;

use Exception;
use ErrorException;

class HomeController extends Controller
{
	use Response;

    /**
     * @OA\Get(
     *    path="/apps",
     *    operationId="getApp",
     *    tags={"Apps"},
     *    description="Get application info",
     *    @OA\Response(
     *        response=200,
     *        description="Success",
     *    )
     * )
     */
	public function app(Request $request)
	{
    	$returnValue = [];
    	$code = 400;

		try {
			$data = new \stdClass();

			$data->contact_person = [
				[
					'id' => '1',
					'name' => 'Kontak Organisasi',
					'contact' => '06355110003'
				],
				[
					'id' => '2',
					'name' => 'Kontak Email',
					'contact' => 'pasiappaluta@gmail.com'
				],
			];
			$data->version = 'PASIAP V.01';

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