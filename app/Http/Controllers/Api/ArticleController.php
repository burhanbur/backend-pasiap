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
use Illuminate\Support\Str;

use Illuminate\Database\QueryException;

use App\Http\Controllers\Controller;

use App\Utilities\Response;
use App\Models\Article;

use Exception;
use ErrorException;

class ArticleController extends Controller
{
    use Response;

    protected $path = 'assets/';

    /**
     * @OA\Get(
     *    path="/articles",
     *    operationId="getAllArticles",
     *    tags={"Article"},
     *    description="Get all data articles",
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function index(Request $request)
    {
        $returnValue = [];
        $collection = [];
        $code = 400;

        try {
            $data = Article::orderBy('created_at', 'DESC')->get();

            foreach ($data as $key => $value) {
                $collection[] = [
                    'id' => $value->id,
                    'slug' => $value->slug,
                    'title' => $value->title,
                    'description' => $value->description,
                    'cover' => public_path('articles') . '/' . $value->cover,
                    'category' => $value->category,
                    'is_publish' => $value->is_publish,
                    'author_id' => $value->author,
                    'author_name' => $value->getAuthor->name,
                    'updated_by' => $value->updated_by,
                    'updated_name' => $value->getUpdatedBy->name,
                    'created_at' =>$value->created_at,
                    'updated_at' =>$value->updated_at
                ];
            }

            $code = 200;
            $returnValue = [
                'success' => true, 
                'data' => $collection,
                'url' => $this->endpoint()
            ];            
        } catch (Exception $ex) {
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    /**
     * @OA\Get(
     *    path="/articles/{slug}",
     *    operationId="getArticleBySlug",
     *    tags={"Article"},
     *    description="Get detail article by slug",
     *    @OA\Parameter(
     *        name="slug",
     *        in="path",
     *        required=true,
     *        @OA\Schema(type="string"),
     *        description="Slug data to show article",
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Success",
     *    )
     * )
     */
    public function show($slug)
    {
        $returnValue = [];
        $code = 400;

        try {
            $data = Article::where('slug', $slug)->first();

            if (!$data) {
                throw new Exception("Data not found", 404);
            }

            $collection = new \stdClass;
            $collection->id = $data->id;
            $collection->slug = $data->slug;
            $collection->title = $data->title;
            $collection->description = $data->description;
            $collection->cover = public_path('articles') . '/' . $data->cover;
            $collection->category = $data->category;
            $collection->is_publish = $data->is_publish;
            $collection->author_id = $data->author;
            $collection->author_name = $data->getAuthor->name;
            $collection->updated_by = $data->updated_by;
            $collection->updated_name = $data->getUpdatedBy->name;
            $collection->created_at = $data->created_at;
            $collection->updated_at = $data->updated_at;

            $code = 200;
            $returnValue = [
                'success' => true, 
                'data' => $collection,
                'url' => $this->endpoint()
            ];            
        } catch (Exception $ex) {
            return $this->error($ex);
        }

        return response()->json($returnValue, $code);
    }

    /**
     * @OA\Post(
     *    path="/articles",
     *    operationId="storeArticle",
     *    tags={"Article"},
     *    description="Create new article",
     *    security={{"bearerAuth": {}}},
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(property="title", type="string"),
     *                @OA\Property(property="description", type="string"),
     *                @OA\Property(property="cover", type="string"),
     *                @OA\Property(property="category", type="string"),
     *                @OA\Property(property="is_publish", type="boolean"),
     *            ),
     *        ),
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function store(Request $request)
    {
        $returnValue = [];
        $code = 400;

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
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
            $slug = Str::slug(strtolower($request->title), '-');

            // Mengecek apakah slug sudah ada di database
            $existingSlug = Article::where('slug', $slug)->first();

            if ($existingSlug) {
                $counter = 1;

                while ($existingSlug) {
                    $newSlug = $slug . '-' . $counter;
                    $existingSlug = Article::where('slug', $newSlug)->first();
                    $counter++;
                }

                $slug = $newSlug;
            }

            $data = new Article;
            $data->slug = $slug;
            $data->author = auth()->user()->id;
            $data->updated_by = auth()->user()->id;
            $data->title = $request->title;
            $data->description = $request->description;
            $data->category = $request->category;
            $data->is_publish = ($request->is_publish) ? 1 : 0;

            if ($request->cover) {
                $image = base64_decode($request->cover);

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
                    throw new Exception("The cover must be a file of type: jpeg, jpg, png.", 400);
                }

                $file_image .= '.' . $extension;

                $image_size = strlen($image);

                if ($image_size > 2097152) {
                    throw new Exception("The cover must not be greater than 2048 kilobytes.", 400);
                }

                $file_path = public_path('articles') . '/' . $file_image;
                file_put_contents($file_path, $image);
                $data->cover = $file_image;
            }

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

    /**
     * @OA\Put(
     *    path="/articles/{id}",
     *    operationId="updateArticle",
     *    tags={"Article"},
     *    description="Update article by ID",
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
     *                @OA\Property(property="title", type="string"),
     *                @OA\Property(property="description", type="string"),
     *                @OA\Property(property="category", type="string"),
     *                @OA\Property(property="cover", type="string"),
     *                @OA\Property(property="is_publish", type="boolean"),
     *            ),
     *        ),
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function update(Request $request, $id)
    {
        $returnValue = [];
        $code = 400;

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
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
            $data = Article::find($id);

            if (!$data) {
                throw new Exception("Data not found", 404);
            }

            $data->title = $request->title;
            $data->description = $request->description;
            $data->category = $request->category;
            $data->is_publish = ($request->is_publish) ? 1 : 0;

            if ($request->cover) {
                $image = base64_decode($request->cover);

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
                    throw new Exception("The cover must be a file of type: jpeg, jpg, png.", 400);
                }

                $file_image .= '.' . $extension;

                $image_size = strlen($image);

                if ($image_size > 2097152) {
                    throw new Exception("The cover must not be greater than 2048 kilobytes.", 400);
                }

                $file_path = public_path('articles') . '/' . $file_image;
                file_put_contents($file_path, $image);
                $data->cover = $file_image;
            }

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

    /**
     * @OA\Delete(
     *    path="/articles/{id}",
     *    operationId="deleteArticle",
     *    tags={"Article"},
     *    description="Delete article by ID",
     *    security={{"bearerAuth": {}}},
     *    @OA\Parameter(
     *        name="id",
     *        in="path",
     *        required=true,
     *        @OA\Schema(type="integer"),
     *        description="ID of the data to delete",
     *    ),
     *    @OA\Response(
     *        response=200, 
     *        description="Success",
     *    )
     * )
     */
    public function delete($id)
    {

        $returnValue = [];
        $code = 400;

        DB::beginTransaction();

        try {
            $data = Article::find($id);

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
