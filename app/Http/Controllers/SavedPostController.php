<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Post;
use App\Models\SavedPost;
use App\Http\Requests\StoreSavedPostRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class SavedPostController extends Controller
{
    /**
     * Get saved posts in a folder.
     * 
     * @OA\Get(
     *     path="/api/post/savedposts/{id}",
     *     summary="Get saved posts in a folder",
     *     tags={"UserSavedPost"},
     *     operationId="getAllSavePost",
     *     description="Return the SavePost",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the folder",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved saved posts"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Folder not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function getSavedPosts($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(["status" => 401, "message" => "Unauthorized"], 401);
        }

        $folder = Folder::where("user_id", $user->id)->where("id", $id)->first();
        if (!$folder) {
            return response()->json(["status" => 400, "message" => "Folder not found"], 400);
        }

        // Check if the user has access to the folder
        if ($folder->user_id !== $user->id) {
            return response()->json(["status" => 403, "message" => "Forbidden"], 403);
        }

        $savedPosts = SavedPost::where("folder_id", $id)->where("user_id", $user->id)->get();
        $allSavedPosts = [];

        foreach ($savedPosts as $savedPost) {
            $postDetail = Post::find($savedPost->post_id);
            if (!$postDetail) {
                continue;
            }

            $postOwner = User::find($postDetail->user_id);
            if (!$postOwner) {
                continue;
            }

            $detail = [
                "id" => $postDetail->id,
                "img_url" => $postDetail->img_url,
                "user_id" => $postDetail->user_id,
                "is_saved" => true,
                "user_name" => $postOwner->first_name . " " . $postOwner->last_name,
                "user_pf_img_url" => $postOwner->pf_img_url,
                "created_at" => $postDetail->created_at,
                "updated_at" => $postDetail->updated_at,
            ];

            $allSavedPosts[] = $detail;
        }

        return response()->json(["status" => 200, "message" => "All saved posts", "posts" => $allSavedPosts], 200);
    }

    /**
     * Save a post to one or more folders.
     * 
     * @OA\Post(
     *     path="/api/post/savepost",
     *     summary="Save a post to one or more folders",
     *     tags={"UserSavedPost"},
     *     operationId="InsertSavePost",
     *     description="Insert the SavePost",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post saved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request or post not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function store(StoreSavedPostRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(["status" => 401, "message" => "Unauthorized"], 401);
        }

        $userId = $user->id;

        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'folder_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => 400, "message" => $validator->messages()], 400);
        }

        $post = Post::find($request->post_id);
        if (!$post) {
            return response()->json(["status" => 404, "message" => "Post not found"], 404);
        }

        foreach ($request->folder_id as $folderId) {
            $folder = Folder::find($folderId);
            if (!$folder) {
                return response()->json(["status" => 400, "message" => "Folder not found"], 400);
            }

            // Check if the user has access to the folder
            if ($folder->user_id !== $userId) {
                return response()->json(["status" => 403, "message" => "Forbidden"], 403);
            }

            $existedSavedPost = SavedPost::where("user_id", $userId)
                ->where("post_id", $request->post_id)
                ->where("folder_id", $folderId)
                ->first();

            if ($existedSavedPost) {
                // Remove the saved post
                $existedSavedPost->delete();
            } else {
                $savedPost = new SavedPost;
                $savedPost->user_id = $userId;
                $savedPost->folder_id = $folderId;
                $savedPost->post_id = $request->post_id;
                $savedPost->save();
            }
        }

        return response()->json(["status" => 200, "message" => "Post saved successfully"], 200);
    }

    // Other controller methods...
}
