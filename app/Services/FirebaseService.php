<?php

namespace App\Services;

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

use App\Utilities\Response;

use App\Models\Category;
use App\Models\FcmToken;
use App\Models\Profile;
use App\Models\User;
use App\Models\RefReportStatus;
use App\Models\LogNotification;

use Exception;
use ErrorException;

class FirebaseService
{
	public $url;

	function __construct()
	{
		$this->url = 'https://fcm.googleapis.com/fcm/send';
	}

	public function sendNotificationToOfficer($code = null, $catId = null)
	{
		$returnValue = [];
		$tokens = [];

		try {
			$user = auth()->user();

			$category = Category::find($catId);

			$url = $this->url;
			$sql = "
	            select ft.token from fcm_tokens as ft 
	            join users as usr on usr.id = ft.user_id 
	            join model_has_roles as mhr on mhr.model_id = usr.id 
	            where mhr.role_id = ?
	        ";

			$query = DB::select($sql, [5]); // petugas

			foreach ($query as $key => $value) {
				$tokens[] = $value->token;
			}

			$serverKey = env('FIREBASE_KEY');

			$data = [
				"registration_ids" => $tokens,
				"notification" => [
					"title" => 'Pemberitahuan ' . @$category->name,
					"body" => 'Ada laporan ' . @$category->name . ' di dekat lokasi Anda dengan kode ' . $code . '. Segera lakukan tindakan pencegahan!',
				],
				"data" => [
					'user_id' => @$user->id,
					'role_id' => @$user->roles()->first()->id,
					'role_name' => @$user->roles()->first()->name,
				]
			];

			$encodedData = json_encode($data);

			$headers = [
				'Authorization:key=' . $serverKey,
				'Content-Type: application/json',
			];

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

			$result = curl_exec($ch);

			if ($result === FALSE) {
				throw new Exception(curl_error($ch), 1);
			}

			curl_close($ch);

			$returnValue = [
				'success' => true,
				'message' => 'success'
			];
		} catch (Exception $ex) {
			$returnValue = [
				'success' => false,
				'message' => $ex->getMessage()
			];
		}

		// save to log notification
		LogNotification::create([
			'payload' => json_encode([
				'code' => $code,
				'status' => $returnValue['success'],
				'message' => $returnValue['message']
			])
		]);

		return $returnValue;
	}

	public function sendNotificationReportStatus($reported_by = null, $status = null)
	{
		$returnValue = [];

		try {
			$user = auth()->user();

			$url = $this->url;
			$sql = "
	            select ft.token from fcm_tokens as ft 
	            join users as usr on usr.id = ft.user_id 
	            where usr.id = ?
	        ";

			$query = DB::select($sql, [$reported_by]);

			foreach ($query as $key => $value) {
				$tokens[] = $value->token;
			}

			$serverKey = env('FIREBASE_KEY');

			switch ($status) {
				case '1':
					$message = 'Laporan Anda berhasil diproses, mohon tetap tenang.';
					break;

				case '2':
					$message = 'Laporan Anda sedang ditangani oleh petugas, mohon tetap tenang dan mengikuti petunjuk petugas.';
					break;

				case '3':
					$message = 'Laporan Anda telah selesai ditangani oleh petugas.';
					break;

				default:
					$message = 'Tim telah tiba di lokasi. Mohon tetap tenang dan mengikuti petunjuk petugas.';
					break;
			}

			$data = [
				"registration_ids" => $tokens,
				"notification" => [
					"title" => 'Status Penanganan Laporan',
					"body" => $message,
				],
				"data" => [
					'user_id' => @$user->id,
					'role_id' => @$user->roles()->first()->id,
					'role_name' => @$user->roles()->first()->name,
				]
			];

			$encodedData = json_encode($data);

			$headers = [
				'Authorization:key=' . $serverKey,
				'Content-Type: application/json',
			];

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

			$result = curl_exec($ch);

			if ($result === FALSE) {
				throw new Exception(curl_error($ch), 1);
			}

			curl_close($ch);

			$returnValue = [
				'success' => true,
				'message' => 'success'
			];
		} catch (Exception $ex) {
			$returnValue = [
				'success' => false,
				'message' => $ex->getMessage()
			];
		}

		// save to log notification
		LogNotification::create([
			'payload' => json_encode([
				'reported_by' => $reported_by,
				'status' => $status,
				'status' => $returnValue['success'],
				'message' => $returnValue['message']
			])
		]);

		return $returnValue;
	}
}
