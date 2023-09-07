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

use App\Models\FcmToken;
use App\Models\Profile;
use App\Models\User;
use App\Models\RefReportStatus;
use App\Models\LogNotification;

use Exception;
use ErrorException;

class FirebaseService
{
	public function sendNotificationToOfficer($code = null)
	{
		$returnValue = [];

		try {
			$url = 'https://fcm.googleapis.com/fcm/send';
	        $sql = "
	            select ft.token from fcm_tokens as ft 
	            join users as usr on usr.id = ft.user_id 
	            join model_has_roles as mhr on mhr.model_id = usr.id 
	            where mhr.role_id = ?
	        ";

	        $query = DB::select($sql, [5]); // petugas

	        $tokens = collect($query)->map(function ($item) {
	        	return (array) $item;
	        });

	        $serverKey = env('FIREBASE_KEY');
	  
	        $data = [
	            "registration_ids" => $tokens,
	            "notification" => [
	                "title" => 'Pemberitahuan Kebakaran',
	                "body" => 'Ada laporan kebakaran di dekat lokasi Anda dengan kode ' . $code . '. Segera lakukan tindakan pencegahan!',
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

	        var_dump($result);
	        die();

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
			$url = 'https://fcm.googleapis.com/fcm/send';
	        $sql = "
	            select ft.token from fcm_tokens as ft 
	            join users as usr on usr.id = ft.user_id 
	            where usr.id = ?
	        ";

	        $query = DB::select($sql, [$reported_by]);

	        $tokens = collect($query)->map(function ($item) {
	        	return (array) $item;
	        });

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
	        		$message = 'Tim pemadam kebakaran telah tiba di lokasi. Mohon tetap tenang dan mengikuti petunjuk petugas.';
	        		break;
	        }
	  
	        $data = [
	            "registration_ids" => $tokens,
	            "notification" => [
	                "title" => 'Status Penanganan Kebakaran',
	                "body" => $message,
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