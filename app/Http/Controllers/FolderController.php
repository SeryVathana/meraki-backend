<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Post;
use App\Models\SavedPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/folder",
     *     operationId="getFolder",
     *     tags={"UserFolder"},
     *     summary="Get list of Folders",
     *     description="Returns list of Folders",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=200
     *             ),
     *             @OA\Property(
     *                 property="folders",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="saved_posts", type="array", @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="img_url", type="string"),
     *                     )),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $folders = Folder::where("user_id", $userId)->get();

        $data = [];
        foreach ($folders as $folder) {
            $savedPosts = SavedPost::where("user_id", $userId)->where("folder_id", $folder->id)->limit(3)->get();

            $allSavedPosts = [];
            foreach ($savedPosts as $savedPost) {
                $post = Post::find($savedPost->post_id);
                if (!$post) {
                    continue;
                }

                $allSavedPosts[] = [
                    "id" => $post->id,
                    "img_url" => $post->img_url,
                ];
            }

            $data[] = [
                "id" => $folder->id,
                "title" => $folder->title,
                "saved_posts" => $allSavedPosts,
                "created_at" => $folder->created_at,
                "updated_at" => $folder->updated_at,
            ];
        }

        $result = [
            "status" => 200,
            "folders" => $data,
        ];

        return response()->json($result, 200);
    }

    /**
     * Get folders by post ID.
     *
     * @OA\Get(
     *     path="/api/folder/post/{id}",
     *     operationId="getFoldersByPostId",
     *     tags={"UserFolder"},
     *     summary="Get folders by post ID",
     *     description="Returns folders containing a specific post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=200
     *             ),
     *             @OA\Property(
     *                 property="folders",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="is_saved", type="boolean"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function getFoldersByPostId($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $post = Post::find($id);
        if (!$post) {
            return response()->json(["status" => 404, "message" => "Post not found"], 404);
        }

        $folders = Folder::where("user_id", $userId)->get();

        $data = [];
        foreach ($folders as $folder) {
            $isSaved = SavedPost::where("user_id", $userId)->where("folder_id", $folder->id)->where("post_id", $id)->exists();

            $data[] = [
                "id" => $folder->id,
                "title" => $folder->title,
                "is_saved" => $isSaved,
                "created_at" => $folder->created_at,
                "updated_at" => $folder->updated_at,
            ];
        }

        return response()->json(["status" => 200, "folders" => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/folder",
     *     operationId="storeFolder",
     *     tags={"UserFolder"},
     *     summary="Create Folder",
     *     description="Creates a Folder",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "status"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"public", "private"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Folder created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function store(StoreFolderRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable|max:255',
            'status' => 'required|in:public,private',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => 400, "message" => $validator->messages()], 400);
        }

        $folder = new Folder;
        $folder->user_id = $userId;
        $folder->title = $request->title;
        $folder->description = $request->description;
        $folder->status = $request->status;
        $folder->save();

        return response()->json(["status" => 200, "message" => "Folder created successfully"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/folder/{id}",
     *     operationId="getFolderById",
     *     tags={"UserFolder"},
     *     summary="Get Folder by ID",
     *     description="Returns a Folder by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="folder",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function show($id)
    {
        $user = Auth::user();

        $folder = Folder::where("id", $id)->where("user_id", $user->id)->first();
        if (!$folder) {
            return response()->json(["status" => 404, "message" => "Folder not found"], 404);
        }

        return response()->json(["status" => 200, "folder" => $folder], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/folder/{id}",
     *     operationId="updateFolder",
     *     tags={"UserFolder"},
     *     summary="Update Folder",
     *     description="Updates a specific Folder",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "status"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"public", "private"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Folder updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function update(UpdateFolderRequest $request, $id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable|max:255',
            'status' => 'required|in:public,private',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => 400, "message" => $validator->messages()], 400);
        }

        $folder = Folder::find($id);
        if (!$folder) {
            return response()->json(["status" => 404, "message" => "Folder not found"], 404);
        }

        if ($folder->user_id != $userId) {
            return response()->json(["status" => 403, "message" => "Unauthorized"], 403);
        }

        $folder->title = $request->title;
        $folder->description = $request->description;
        $folder->status = $request->status;
        $folder->save();

        return response()->json(["status" => 200, "message" => "Folder updated successfully"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/folder/{id}",
     *     operationId="deleteFolder",
     *     tags={"UserFolder"},
     *     summary="Delete Folder",
     *     description="Deletes a specific Folder",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Folder deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $folder = Folder::find($id);
        if (!$folder) {
            return response()->json(["status" => 404, "message" => "Folder not found"], 404);
        }

        if ($folder->user_id != $userId) {
            return response()->json(["status" => 403, "message" => "Unauthorized"], 403);
        }

        $folder->delete();
        SavedPost::where("user_id", $userId)->where("folder_id", $id)->delete();

        return response()->json(["status" => 200, "message" => "Folder deleted successfully"], 200);
    }
}
