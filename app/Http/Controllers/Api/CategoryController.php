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
use App\Models\Category;

use Exception;
use ErrorException;

class CategoryController extends Controller
{
    use Response;

    /**
     * @OA\Get(
     *    path="/categories",
     *    operationId="getAllCategories",
     *    tags={"Categories"},
     *    description="Register account",
     *    security={{"bearerAuth": {}}},
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function getAllCategories(Request $request)
    {
        $code = 400;

        try {
            $data = Category::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();

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
}
