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

class ReportController extends Controller
{
	use Response;

	protected $path = 'assets/';

	/**
	 * @OA\Get(
	 *    path="/reports",
	 *    operationId="getReports",
	 *    tags={"Reports"},
	 *    description="Get all reports",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Response(
	 *        response=200,
	 *        description="Success",
	 *    )
	 * )
	 */
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
					'code' => $value->code,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'location' => $value->location,
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
				'datetime' => now(),
				'url' => $this->endpoint()
			];
		} catch (Exception $ex) {
			return $this->error($ex);
		}

		return response()->json($returnValue, $code);
	}

	/**
	 * @OA\Get(
	 *    path="/reports/{id}",
	 *    operationId="getReportById",
	 *    tags={"Reports"},
	 *    description="Get detail report by ID",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Parameter(
	 *        name="id",
	 *        in="path",
	 *        required=true,
	 *        @OA\Schema(type="integer"),
	 *        description="ID of the data to show",
	 *    ),
	 *    @OA\Response(
	 *        response=200,
	 *        description="Success",
	 *    )
	 * )
	 */
	public function getReportById(Request $request, $id)
	{
		$returnValue = [];
		$code = 400;

		try {
			$data = [];
			$value = Report::find($id);

			if ($value) {
				$logs = LogReport::where('report_id', $value->id)->get();

				$arrLog = [];
				foreach ($logs as $key => $log) {
					$arrLog[] = [
						'id' => $log->id,
						'report_id' => $value->id,
						'description' => $value->description,
						'status_id' => $log->status,
						'status_name' => @$log->getStatus->name,
						'created_at' => date('Y-m-d H:i:s', strtotime($log->created_at)),
					];
				}

				$data = [
					'id' => $value->id,
					'code' => $value->code,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'location' => $value->location,
					'photo' => URL::to('/') . ($value->photo) ? URL::to('/') . '/reports/' . $value->photo : '',
					'description' => $value->description,
					'status_id' => $value->status,
					'status_name' => $value->getStatus->name,
					'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
					'logs' => $arrLog,
				];

				$code = 200;
				$returnValue = [
					'success' => true,
					'data' => $data,
					'datetime' => now(),
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

	/**
	 * @OA\Get(
	 *    path="/reports/status/{id}",
	 *    operationId="getReportByStatus",
	 *    tags={"Reports"},
	 *    description="Get detail report by ID status",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Parameter(
	 *        name="id",
	 *        in="path",
	 *        required=true,
	 *        @OA\Schema(type="integer"),
	 *        description="ID status of the data to show",
	 *    ),
	 *    @OA\Response(
	 *        response=200,
	 *        description="Success",
	 *    )
	 * )
	 */
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
					'code' => $value->code,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'location' => $value->location,
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
				'datetime' => now(),
				'url' => $this->endpoint()
			];
		} catch (Exception $ex) {
			return $this->error($ex);
		}

		return response()->json($returnValue, $code);
	}

	/**
	 * @OA\Get(
	 *    path="/reports/handler/{id}",
	 *    operationId="getReportByHandler",
	 *    tags={"Reports"},
	 *    description="Get detail report by ID handler reports",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Parameter(
	 *        name="id",
	 *        in="path",
	 *        required=true,
	 *        @OA\Schema(type="integer"),
	 *        description="ID handler reports of the data to show",
	 *    ),
	 *    @OA\Response(
	 *        response=200,
	 *        description="Success",
	 *    )
	 * )
	 */
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
					'code' => $value->code,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'location' => $value->location,
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
				'datetime' => now(),
				'url' => $this->endpoint()
			];
		} catch (Exception $ex) {
			return $this->error($ex);
		}

		return response()->json($returnValue, $code);
	}

	/**
	 * @OA\Get(
	 *    path="/reports/request/{id}",
	 *    operationId="getReportByRequest",
	 *    tags={"Reports"},
	 *    description="Get detail report by ID requestor reports",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Parameter(
	 *        name="id",
	 *        in="path",
	 *        required=true,
	 *        @OA\Schema(type="integer"),
	 *        description="ID requestor reports of the data to show",
	 *    ),
	 *    @OA\Response(
	 *        response=200,
	 *        description="Success",
	 *    )
	 * )
	 */
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
					'code' => $value->code,
					'category_id' => $value->cat_id,
					'category_name' => $value->getCategory->name,
					'reporter_id' => $value->reported_by,
					'reporter_name' => $value->getReportedBy->name,
					'handler_id' => $value->taken_by,
					'handler_name' => @$value->getTakenBy->name,
					'lat' => $value->lat,
					'long' => $value->long,
					'location' => $value->location,
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
				'datetime' => now(),
				'url' => $this->endpoint()
			];
		} catch (Exception $ex) {
			return $this->error($ex);
		}

		return response()->json($returnValue, $code);
	}

	/**
	 * @OA\Post(
	 *    path="/reports",
	 *    operationId="createReport",
	 *    tags={"Reports"},
	 *    description="Create new report",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\RequestBody(
	 *        required=true,
	 *        @OA\MediaType(
	 *            mediaType="application/json",
	 *            @OA\Schema(
	 *                @OA\Property(property="cat_id", type="string"),
	 *                @OA\Property(property="reported_by", type="string"),
	 *                @OA\Property(property="lat", type="string"),
	 *                @OA\Property(property="long", type="string"),
	 *                @OA\Property(property="location", type="string"),
	 *                @OA\Property(property="description", type="string"),
	 *                @OA\Property(property="status", type="integer"),
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
	public function createReport(Request $request)
	{
		$returnValue = [];
		$code = 400;

		$folderPath = public_path('reports');

		if (!File::isDirectory($folderPath)) {
			File::makeDirectory($folderPath, 0777, true);
		}

		$validator = Validator::make($request->all(), [
			'cat_id' => 'required',
			'reported_by' => 'required',
			'description' => 'required|string',
			'photo' => ['required_if: cat_id,2,3'],
			// 'photo' => 'file|mimes:jpeg,jpg,png|max:2048',
		]);

		if ($validator->fails()) {
			$returnValue = [
				'success' => false,
				'message' => $validator->errors(),
				'datetime' => now(),
				'url' => $this->endpoint()
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
			$report->location = $request->location;
			$report->description = $request->description;
			$report->status = 1; // PROSES

			if ($request->photo) {
				$image = base64_decode($request->photo);

				$file_image = str_replace(' ', '_', strtotime(date('Y-m-d H:i:s')));

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

				$file_path = public_path('reports') . '/' . $file_image;
				file_put_contents($file_path, $image);
				$report->photo = $file_image;
			}

			$report->save();

			$a = Report::find($report->id);
			$a->code = 'BE' . str_pad($report->id, 5, '0', STR_PAD_LEFT);
			$a->save();

			$log = new LogReport;
			$log->report_id = $report->id;
			$log->status = 1;
			$log->save();

			// send push notification
			$service = new FirebaseService;
			$service->sendNotificationToOfficer($a->code, $report->cat_id);
			$service->sendNotificationReportStatus($request->reported_by, $report->status);

			$data = [
				'id' => $report->id,
				'code' => $a->code,
				'category_id' => $report->cat_id,
				'category_name' => $report->getCategory->name,
				'reporter_id' => $report->reported_by,
				'reporter_name' => $report->getReportedBy->name,
				'handler_id' => $report->taken_by,
				'handler_name' => @$report->getTakenBy->name,
				'lat' => $report->lat,
				'long' => $report->long,
				'location' => $report->location,
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
				'datetime' => now(),
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
	 *    path="/reports/{id}",
	 *    operationId="updateReport",
	 *    tags={"Reports"},
	 *    description="Update report by ID",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Parameter(
	 *        name="id",
	 *        in="path",
	 *        required=true,
	 *        @OA\Schema(type="integer"),
	 *        description="ID of the data to update",
	 *    ),
	 *    @OA\RequestBody(
	 *        required=true,
	 *        @OA\MediaType(
	 *            mediaType="multipart/form-data",
	 *            @OA\Schema(
	 *                @OA\Property(property="cat_id", type="string"),
	 *                @OA\Property(property="description", type="string"),
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
	public function updateReport(Request $request, $id)
	{
		$returnValue = [];
		$code = 400;

		$folderPath = public_path('reports');

		if (!File::isDirectory($folderPath)) {
			File::makeDirectory($folderPath, 0777, true);
		}

		$validator = Validator::make($request->all(), [
			'cat_id' => 'required',
			'location' => 'required|string',
			'description' => 'required|string',
			'photo' => 'file|mimes:jpeg,jpg,png|max:2048',
		]);

		if ($validator->fails()) {
			$returnValue = [
				'success' => false,
				'message' => $validator->errors(),
				'datetime' => now(),
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
					'datetime' => now(),
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

			$report->location = $request->location;
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
				'code' => $report->code,
				'category_id' => $report->cat_id,
				'category_name' => $report->getCategory->name,
				'reporter_id' => $report->reported_by,
				'reporter_name' => $report->getReportedBy->name,
				'handler_id' => $report->taken_by,
				'handler_name' => @$report->getTakenBy->name,
				'lat' => $report->lat,
				'long' => $report->long,
				'long' => $report->location,
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
				'datetime' => now(),
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
	 *    path="/reports/status/{id}",
	 *    operationId="updateReportStatus",
	 *    tags={"Reports"},
	 *    description="Update report status by Report ID",
	 *    security={{"bearerAuth": {}}},
	 *    @OA\Parameter(
	 *        name="id",
	 *        in="path",
	 *        required=true,
	 *        @OA\Schema(type="integer"),
	 *        description="ID of the data to update",
	 *    ),
	 *    @OA\RequestBody(
	 *        required=true,
	 *        @OA\MediaType(
	 *            mediaType="application/json",
	 *            @OA\Schema(
	 *                @OA\Property(property="taken_by", type="integer"),
	 *                @OA\Property(property="status", type="integer"),
	 *            ),
	 *        ),
	 *    ),
	 *    @OA\Response(
	 *        response=200,
	 *        description="Success",
	 *    )
	 * )
	 */
	public function updateReportStatus(Request $request, $id)
	{
		$returnValue = [];
		$code = 400;

		$validator = Validator::make($request->all(), [
			'taken_by' => 'required',
			'status' => 'required'
		]);

		if ($validator->fails()) {
			$returnValue = [
				'success' => false,
				'message' => $validator->errors(),
				'datetime' => now(),
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

			// send push notification
			$service = new FirebaseService;
			$service->sendNotificationReportStatus($report->status);

			$data = [
				'id' => $report->id,
				'code' => $report->code,
				'category_id' => $report->cat_id,
				'category_name' => $report->getCategory->name,
				'reporter_id' => $report->reported_by,
				'reporter_name' => $report->getReportedBy->name,
				'handler_id' => $report->taken_by,
				'handler_name' => @$report->getTakenBy->name,
				'lat' => $report->lat,
				'long' => $report->long,
				'location' => $report->c,
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
				'datetime' => now(),
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
