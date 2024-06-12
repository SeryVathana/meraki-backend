<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostLikeController extends Controller
{
     /**
     * @OA\Post(
     *     path="api/post/like/{id}",
     *     tags={"UserPostLikes"},
     *     summary="Like a post",
     *     operationId="getAllPostLikes",
     *     description="Insert the Like to Post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to like",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully liked the post"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    public function likePost($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $post = Post::find($id);
        if (!$post) {
            $data = [
                "status" => 404,
                "message" => "Post not found",
            ];
            return response()->json($data, 404);
        }

        if ($post->status == "private" && $post->user_id != $userId && $user->role != "admin") {
            $data = [
                "status" => 401,
                "message" => "Unauthorized",
            ];

            return response()->json($data, 403);
        }

        $postLike = PostLike::where("user_id", $userId)->where("post_id", $post->id)->first();
        if ($postLike) {
            $data = [
                "status" => 400,
                "message" => "Post already liked",
            ];
            return response()->json($data, 400);
        }

        $like = new PostLike();
        $like->user_id = $userId;
        $like->post_id = $post->id;
        $like->save();

        $data = [
            "status" => 200,
            "message" => "Liked post successfully",
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Delete(
     *     path="api/post/unlike/{id}",
     *     summary="Unlike a post",
     *     tags={"UserPostLikes"},
     *     operationId="deleteAllPostLikes",
     *     description="delete the Like to Post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to unlike",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully unliked the post"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized to unlike the post"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    public function unlikePost($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $post = Post::find($id);
        if (!$post) {
            $data = [
                "status" => 404,
                "message" => "Post not found",
            ];
            return response()->json($data, 404);
        }

        if ($post->status == "private" && $post->user_id != $userId && $user->role != "admin") {
            $data = [
                "status" => 401,
                "message" => "Unauthorized",
            ];

            return response()->json($data, 403);
        }

        $postLike = PostLike::where("user_id", $userId)->where("post_id", $post->id)->first();
        if (!$postLike) {
            $data = [
                "status" => 400,
                "message" => "Post not liked",
            ];
            return response()->json($data, 400);
        }

        $postLike->delete();

        $data = [
            "status" => 200,
            "message" => "Unliked post successfully",
        ];
        return response()->json($data, 200);
    }
}
