<?php

namespace App\Utilities;

use Illuminate\Support\Facades\URL;

trait Response
{
	public function errMessage($ex = null)
	{
		$returnValue = 'Something went wrong';

		if ($ex) {
			if (env('APP_ENV') == 'local') {
				$returnValue = $ex->getMessage() . ' in file ' . $ex->getFile() . ' at line ' . $ex->getLine();
			}

			$returnValue = $ex->getMessage();
		}

		return $returnValue;
	}

	public function endpoint()
	{
		return URL::current();
	}

	public function unauthorized()
	{
		$code = 401;
		$returnValue = [
			'success' => false,
			'message' => 'Invalid credentials',
			'datetime' => now(),
			'url' => $this->endpoint()
		];

		return response()->json($returnValue, $code);
	}

	public function notVerified()
	{
		$code = 401;
		$returnValue = [
			'success' => false,
			'message' => 'Please verify your email first',
			'datetime' => now(),
			'url' => $this->endpoint()
		];

		return response()->json($returnValue, $code);
	}

	public function notFound()
	{
		$code = 404;
		$returnValue = [
			'success' => false,
			'message' => 'Data not found',
			'datetime' => now(),
			'url' => $this->endpoint()
		];

		return response()->json($returnValue, $code);
	}

	public function error($ex = null)
	{
		$code = 500;
		$returnValue = [
			'success' => false,
			'message' => $this->errMessage($ex),
			'datetime' => now(),
			'url' => $this->endpoint()
		];

		return response()->json($returnValue, $code);
	}
}
