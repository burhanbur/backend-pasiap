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

use App\Models\Report;
use App\Models\LogReport;

use Exception;
use ErrorException;

class ReportController extends Controller
{
	use Response;

	protected $path = 'assets/';

	public function getReports(Request $request)
	{
    	$returnValue = [];
    	$code = 400;

		try {
			$data = [];
			$reports = Report::orderBy('created_at', 'desc')->get();

			foreach ($reports as $key => $value) {
				$data[] = [
					'id' => $value->id,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'photo' => URL::to('/') . ($value->photo) ? URL::to('/') . '/reports/' . $value->photo : '',
					'description' => $value->description,
					'status_id' => $value->status,
					'status_name' => $value->getStatus->name,
					'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at))
				];
			}

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

	public function getReportById(Request $request, $id)
	{
    	$returnValue = [];
    	$code = 400;

		try {
			$data = [];
			$value = Report::find($id);

			if ($value) {
				$data = [
					'id' => $value->id,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'photo' => URL::to('/') . ($value->photo) ? URL::to('/') . '/reports/' . $value->photo : '',
					'description' => $value->description,
					'status_id' => $value->status,
					'status_name' => $value->getStatus->name,
					'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at))
				];

				$code = 200;
	            $returnValue = [
	                'success' => true, 
	                'data' => $data,
	                'url' => $this->endpoint()
	            ];
			} else {
				return $this->notFound();
			}
		} catch (Exception $ex) {
			return $this->error($ex);	
		}

		return response()->json($returnValue, $code);		
	}

	public function getReportByStatus(Request $request, $id)
	{
		$returnValue = [];
    	$code = 400;

		try {
			$data = [];
			$reports = Report::orderBy('id', 'asc')->where('status', $id)->get();

			foreach ($reports as $key => $value) {
				$data[] = [
					'id' => $value->id,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'photo' => URL::to('/') . ($value->photo) ? URL::to('/') . '/reports/' . $value->photo : '',
					'description' => $value->description,
					'status_id' => $value->status,
					'status_name' => $value->getStatus->name,
					'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at))
				];
			}

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

	public function getReportByHandler(Request $request, $id)
	{
		$returnValue = [];
    	$code = 400;

		try {
			$data = [];
			$reports = Report::orderBy('created_at', 'desc')->where('taken_by', $id)->get();

			foreach ($reports as $key => $value) {
				$data[] = [
					'id' => $value->id,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'photo' => URL::to('/') . ($value->photo) ? URL::to('/') . '/reports/' . $value->photo : '',
					'description' => $value->description,
					'status_id' => $value->status,
					'status_name' => $value->getStatus->name,
					'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at))
				];
			}

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

	public function getReportByRequest(Request $request, $id)
	{
		$returnValue = [];
    	$code = 400;

		try {
			$data = [];
			$reports = Report::orderBy('created_at', 'desc')->where('reported_by', $id)->get();

			foreach ($reports as $key => $value) {
				$data[] = [
					'id' => $value->id,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'photo' => URL::to('/') . ($value->photo) ? URL::to('/') . '/reports/' . $value->photo : '',
					'description' => $value->description,
					'status_id' => $value->status,
					'status_name' => $value->getStatus->name,
					'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at))
				];
			}

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

	public function createReport(Request $request)
	{
		$returnValue = [];
		$code = 400;

        $validator = Validator::make($request->all(), [
            'cat_id' => 'required',
            'reported_by' => 'required',
            'description' => 'required|string',
            'photo' => 'file|mimes:jpeg,png|max:2048',
        ]);

        if($validator->fails()){
        	$returnValue = [
        		'success' => false,
        		'message' => $validator->errors()
        	];

            return response()->json($returnValue, $code);
        }

    	DB::beginTransaction();

		try {
			$report = new Report;
			$report->cat_id = $request->cat_id;
			$report->reported_by = $request->reported_by;
			$report->lat = $request->lat;
			$report->long = $request->long;
			$report->description = $request->description;
			$report->status = 1; // PROSES

            if ($request->file('photo')) {
                $image = $request->file('photo');
                $file_image = str_replace(' ', '_', strtotime(date('Y-m-d H:i:s')) . '_' . $image->getClientOriginalName());
                $image->move('reports', $file_image);
                $report->photo = $file_image;
            }

            $report->save();

            $log = new LogReport;
            $log->report_id = $report->id;
            $log->status = 1;
            $log->save(); 

			$data = [
				'id' => $report->id,
				'category_id' => $report->cat_id,
				'category_name' => $report->getCategory->name,
				'reporter_id' => $report->reported_by,
				'reporter_name' => $report->getReportedBy->name,
				'handler_id' => $report->taken_by,
				'handler_name' => @$report->getTakenBy->name,
				'lat' => $report->lat,
				'long' => $report->long,
				'photo' => ($report->photo) ? URL::to('/') . '/reports/' . $report->photo : '',
				'description' => $report->description,
				'status_id' => $report->status,
				'status_name' => $report->getStatus->name,
				'created_at' => date('Y-m-d H:i:s', strtotime($report->created_at))
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

	public function updateReport(Request $request, $id)
	{
		$returnValue = [];
		$code = 400;

        $validator = Validator::make($request->all(), [
            'cat_id' => 'required',
            'description' => 'required|string',
            'photo' => 'file|mimes:jpeg,png|max:2048',
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
			$report = Report::find($id);

			if (!$report) {
				return $this->notFound();
			}

			if ($report->status != 1) {
				return response()->json([
					'success' => false,
					'message' => 'Can not update report because report already proceed by officer',
					'url' => $this->endpoint()
				], $code);
			}

			$report->cat_id = $request->cat_id;

			if ($request->lat) {
				$report->lat = $request->lat;
			}

			if ($request->long) {
				$report->long = $request->long;
			}

			$report->description = $request->description;

            if ($request->file('photo')) {
                $image = $request->file('photo');
                $file_image = str_replace(' ', '_', strtotime(date('Y-m-d H:i:s')) . '_' . $image->getClientOriginalName());
                $image->move('reports', $file_image);
                $report->photo = $file_image;
            }

            $report->save();

			$data = [
				'id' => $report->id,
				'category_id' => $report->cat_id,
				'category_name' => $report->getCategory->name,
				'reporter_id' => $report->reported_by,
				'reporter_name' => $report->getReportedBy->name,
				'handler_id' => $report->taken_by,
				'handler_name' => @$report->getTakenBy->name,
				'lat' => $report->lat,
				'long' => $report->long,
				'photo' => URL::to('/') . ($report->photo) ? URL::to('/') . '/reports/' . $report->photo : '',
				'description' => $report->description,
				'status_id' => $report->status,
				'status_name' => $report->getStatus->name,
				'created_at' => date('Y-m-d H:i:s', strtotime($report->created_at))
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

	public function updateReportStatus(Request $request, $id)
	{
		$returnValue = [];
		$code = 400;

        $validator = Validator::make($request->all(), [
            'taken_by' => 'required',
            'status' => 'required'
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
			$report = Report::find($id);

			if (!$report) {
				return $this->notFound();
			}

            $report->taken_by = $request->taken_by;
			$report->status = $request->status;
            $report->save();

            $log = new LogReport;
            $log->report_id = $report->id;
            $log->taken_by = $request->taken_by;
            $log->status = $request->status;
            $log->save(); 

			$data = [
				'id' => $report->id,
				'category_id' => $report->cat_id,
				'category_name' => $report->getCategory->name,
				'reporter_id' => $report->reported_by,
				'reporter_name' => $report->getReportedBy->name,
				'handler_id' => $report->taken_by,
				'handler_name' => @$report->getTakenBy->name,
				'lat' => $report->lat,
				'long' => $report->long,
				'photo' => URL::to('/') . ($report->photo) ? URL::to('/') . '/reports/' . $report->photo : '',
				'description' => $report->description,
				'status_id' => $report->status,
				'status_name' => $report->getStatus->name,
				'created_at' => date('Y-m-d H:i:s', strtotime($report->created_at))
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
}