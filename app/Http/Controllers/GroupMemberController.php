<?php

namespace App\Http\Controllers;

use App\Models\GroupInvite;
use App\Models\GroupMember;
use App\Models\Group;
use App\Http\Requests\StoreGroupMemberRequest;
use App\Http\Requests\UpdateGroupMemberRequest;
use App\Models\User;
use App\Models\UserFollower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/group/notmember/{id}",
     *     tags={"UserGroupMember"},
     *     summary="Get users who are not members of the group",
     *     description="Retrieves users who are not members of the specified group and match the search query.",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the group",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query to filter users",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="pf_img_url", type="string"),
     *                     @OA\Property(property="is_following", type="boolean", description="Indicates if the authenticated user is following this user"),
     *                     @OA\Property(property="is_invited", type="boolean", description="Indicates if the user is invited to the group"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function getNotMembers(Request $request, $id)
    {
        $searchQuery = $request->query("q");
        $auth = Auth::user();
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['status' => 404, 'message' => 'Group not found'], 404);
        }

        $groupMembers = GroupMember::where("group_id", $id)->pluck("user_id")->toArray();

        $users = User::whereNotIn('id', $groupMembers)
            ->where(function ($query) use ($searchQuery) {
                $query->where('first_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%');
            })
            ->limit(50)
            ->get();

        $result = [];
        foreach ($users as $user) {
            $res = [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "email" => $user->email,
                "pf_img_url" => $user->pf_img_url,
                "is_following" => UserFollower::where("user_id", $user->id)->where("follower_id", $auth->id)->exists(),
                "is_invited" => GroupInvite::where("user_id", $user->id)->where("group_id", $id)->exists(),
            ];

            array_push($result, $res);
        }

        return response()->json(['status' => 200, 'users' => $result], 200);
    }



    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/group/member/{id}",
     *     operationId="getGroupMember",
     *     tags={"UserGroupMember"},
     *     summary="Get group member",
     *     description="Returns group member based on group",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found"
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $searchQuery = $request->query('q');
        $group = Group::find($id);

        if (!$group) {
            $data = [
                "status" => 404,
                "message" => "Group not found",
            ];

            return response()->json($data, 404);
        }

        $members = GroupMember::where("group_id", $id)->get();

        $result = [];
        foreach ($members as $member) {
            $user = User::where("id", $member->user_id)->where(function ($query) use ($searchQuery) {
                $query->where('first_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%');
            })->first();

            if (!$user) {
                continue;
            }

            $mem = GroupMember::where("group_id", $id)->where("user_id", $user->id)->first();
            if (!$mem) {
                continue;
            }

            $res = [
                "id" => $mem->id,
                "user_id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "email" => $user->email,
                "pf_img_url" => $user->pf_img_url,
                "group_role" => $mem->role
            ];


            array_push($result, $res);
        }

        $data = [
            'status' => 200,
            'members' => $result
        ];

        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     * @OA\Put(
     *     path="/api/group/member/{id}",
     *     operationId="updateGroupMember",
     *     tags={"UserGroupMember"},
     *     summary="Update group member",
     *     description="Updates a specific group member",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"user_id", "role"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found"
     *     )
     * )
     */
    public function update(UpdateGroupMemberRequest $request, $id)
    {
        $user = Auth::user();
        $userId = $user->id;
        $group = Group::find($id);
        if (!$group) {
            $data = [
                "status" => 404,
                "message" => "Group not found",
            ];
            return response()->json($data, 404);
        }

        if (!Gate::allows('update_member', $group)) {
            $data = [
                "status" => 403,
                "message" => "Unauthorized"
            ];
            return response()->json($data, 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'role' => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 400,
                "message" => $validator->messages()
            ];
            return response()->json($data, 400);
        }

        if ($userId == $request->user_id) {
            $data = [
                "status" => 403,
                "message" => "You can't change yourself"
            ];
            return response()->json($data, 403);
        }

        $member = GroupMember::where("group_id", $id)->where("user_id", $request->user_id)->first();
        if (!$member) {
            $data = [
                "status" => 400,
                "message" => "Member not found"
            ];
            return response()->json($data, 400);
        }

        if ($user->role != "admin" && $member->role == "admin") {
            $data = [
                "status" => 403,
                "message" => "Unauthorized"
            ];
            return response()->json($data, 403);
        }

        $member->role = $request->role;
        $member->save();

        $data = [
            'status' => 200,
            'message' => "Member updated successfully"
        ];
        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     * @OA\Delete(
     *     path="/api/group/member/{id}",
     *     operationId="deleteGroupMember",
     *     tags={"UserGroupMember"},
     *     summary="Delete group member",
     *     description="Deletes a specific group member",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member deleted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found"
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $userId = $user->id;
        $group = Group::find($id);
        if (!$group) {
            $data = [
                "status" => 404,
                "message" => "Group not found",
            ];
            return response()->json($data, 404);
        }

        if (!Gate::allows('update_member', $group)) {
            $data = [
                "status" => 403,
                "message" => "Unauthorized"
            ];
            return response()->json($data, 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 400,
                "message" => $validator->messages()
            ];
            return response()->json($data, 400);
        }

        $member = GroupMember::where("group_id", $id)->where("user_id", $request->user_id)->first();
        if (!$member) {
            $data = [
                "status" => 400,
                "message" => "Member not found"
            ];
            return response()->json($data, 400);
        }

        if ($user->role != "admin" && $member->role == "admin" && $userId != $request->user_id) {
            $data = [
                "status" => 403,
                "message" => "Unauthorized"
            ];
            return response()->json($data, 403);
        }

        $member->delete();
        

        $data = [
            'status' => 200,
            'message' => "Member removed successfully"
        ];
        return response()->json($data, 200);
    }
}