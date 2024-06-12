<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFollower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFollowerController extends Controller
{
    /**
     * Follow a user.
     *
     * @OA\Put(
     *     path="/api/user/follow/{id}",
     *     operationId="followUser",
     *     tags={"UserFollower"},
     *     summary="Follow a user",
     *     description="Follow a user by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to follow",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User followed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *     ),
     * )
     */
    public function followUser($id)
    {
        $loggedInUser = Auth::user();

        if ($loggedInUser->id == $id) {
            return response()->json([
                'status' => 401,
                'message' => 'You can not follow yourself'
            ], 401);
        }
        $user = User::find($id);
        $follower = UserFollower::where('user_id', $user->id)->where('follower_id', $loggedInUser->id)->first();

        if ($follower) {
            return response()->json([
                'status' => 401,
                'message' => 'You are already following this user'
            ], 401);
        }

        $newFollow = new UserFollower;
        $newFollow->user_id = $id;
        $newFollow->follower_id = $loggedInUser->id;
        $newFollow->save();


        return response()->json([
            'status' => 200,
            'message' => 'User Followed Successfully',
        ], 200);
    }


    /**
     * Unfollow a user.
     *
     * @OA\Put(
     *     path="/api/user/unfollow/{id}",
     *     operationId="unfollowUser",
     *     tags={"UserFollower"},
     *     summary="Unfollow a user",
     *     description="Unfollow a user by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to unfollow",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User unfollowed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *     ),
     * )
     */
    public function unfollowUser($id)
    {
        $loggedInUser = Auth::user();

        if ($loggedInUser->id == $id) {
            return response()->json([
                'status' => 401,
                'message' => 'You can not unfollow yourself'
            ], 401);
        }

        $follower = UserFollower::where('user_id', $id)->where('follower_id', $loggedInUser->id)->first();
        if ($follower) {
            $follower->delete();
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'You are not following this user'
            ], 401);
        }

        return response()->json([
            'status' => 200,
            'message' => 'User Unfollowed Successfully'
        ], 200);
    }


    /**
     * Get user followers.
     *
     * @OA\Get(
     *     path="/api/user/follower/{id}",
     *     summary="Get user followers",
     *     tags={"UserFollower"},
     *     description="Get user followers by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=false,
     *         description="Search query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=200
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User followers"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="pf_img_url", type="string"),
     *                     @OA\Property(property="is_following", type="boolean"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *     )
     * )
     */
    
    public function getUserFollowers(Request $request, $id)
    {
        $searchQuery = $request->query('q');
        $loggedInUser = Auth::user();
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 404);
        }

        $followers = UserFollower::where('user_id', '=', $id)->get()->pluck('follower_id')->toArray();

        $data = [];

        foreach ($followers as $follower) {
            $f = "";
            if ($searchQuery != "") {
                $f = User::find($follower)->where("id", $follower)->where(function ($query) use ($searchQuery) {
                    $query->where('first_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%');
                })->first();
            } else {
                $f = User::find($follower);
            }
            if (!$f) {
                continue;
            }

            $isFollowing = false;

            $following = UserFollower::where('user_id', '=', $follower)->where("follower_id", $loggedInUser->id)->first();
            if ($following) {
                $isFollowing = true;
            }

            $userData = [
                'id' => $f->id,
                'first_name' => $f->first_name,
                'last_name' => $f->last_name,
                'email' => $f->email,
                'pf_img_url' => $f->pf_img_url,
                'is_following' => $isFollowing,
            ];
            array_push($data, $userData);
        }

        return response()->json([
            'status' => 200,
            'message' => 'User Followers',
            'data' => $data
        ], 200);
    }


        /**
 * @OA\Get(
 *     path="/api/user/following/{id}",
 *     summary="Get user following",
 *     tags={"UserFollower"},
 *     summary="getUserFollowing",
 *     description="getUserFollowing by ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="q",
 *         in="query",
 *         required=false,
 *         description="Search query",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status",
 *                 type="integer",
 *                 example=200
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User Followers"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="first_name", type="string"),
 *                     @OA\Property(property="last_name", type="string"),
 *                     @OA\Property(property="email", type="string"),
 *                     @OA\Property(property="pf_img_url", type="string"),
 *                     @OA\Property(property="is_following", type="boolean"),
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *     )
 * )
 */
    public function getUserFollowings(Request $request, $id)
    {
        $searchQuery = $request->query('q');
        $loggedInUser = Auth::user();
        $user = User::find($id);

        $followings = UserFollower::where('follower_id', $id)->get()->pluck('user_id')->toArray();

        $data = [];

        foreach ($followings as $following) {
            $f = "";
            if ($searchQuery != "") {
                $f = User::find($following)->where("id", $following)->where(function ($query) use ($searchQuery) {
                    $query->where('first_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%');
                })->first();
            } else {
                $f = User::find($following);
            }
            if (!$f) {
                continue;
            }

            $isFollowing = false;
            $followed = UserFollower::where('user_id', '=', $following)->where("follower_id", $loggedInUser->id)->first();
            if ($followed) {
                $isFollowing = true;
            }


            $userData = [
                'id' => $f->id,
                'first_name' => $f->first_name,
                'last_name' => $f->last_name,
                'email' => $f->email,
                'pf_img_url' => $f->pf_img_url,
                'is_following' => $isFollowing,
            ];
            array_push($data, $userData);
        }

        return response()->json([
            'status' => 200,
            'message' => 'User Followings',
            'data' => $data
        ], 200);
    }
}
