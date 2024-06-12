<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/getTotalUsers",
     *     operationId="AdmingetTotalUsers",
     *     tags={"AdminDashboard"},
     *     summary="Get total users",
     *     description="Returns the total number of users and the percentage of new users in the last week.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Total users fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_users", type="integer", example=1000),
     *                 @OA\Property(property="last_week_percent", type="number", format="float", example=10.5)
     *             )
     *         )
     *     ),
     * @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getTotalUsers(Request $request)
    {
        $totalUsers = User::count();
        $lastWeekUsers = User::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekUserPercentage = $lastWeekUsers / $totalUsers * 100;
        $data = [
            "status" => 200,
            "message" => "Total users fetched successfully",
            "data" => [
                "total_users" => $totalUsers,
                "last_week_percent" => $lastWeekUserPercentage
            ]
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/getTotalPosts",
     *     operationId="AdmingetTotalPosts",
     *     tags={"AdminDashboard"},
     *     summary="Get total posts",
     *     description="Returns the total number of posts and the percentage of new posts in the last week.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Total posts fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_posts", type="integer", example=1000),
     *                 @OA\Property(property="last_week_percent", type="number", format="float", example=10.5)
     *             )
     *         )
     *     ),
     * @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getTotalPosts(Request $request)
    {
        $totalPosts = Post::count();
        $lastWeekPosts = Post::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekPostPercentage = $lastWeekPosts / $totalPosts * 100;
        $data = [
            "status" => 200,
            "message" => "Total posts fetched successfully",
            "data" => [
                "total_posts" => $totalPosts,
                "last_week_percent" => $lastWeekPostPercentage
            ]
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/getTotalGroups",
     *     operationId="AdmingetTotalGroups",
     *     tags={"AdminDashboard"},
     *     summary="Get total groups",
     *     description="Returns the total number of groups and the percentage of new groups in the last week.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Total groups fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_groups", type="integer", example=1000),
     *                 @OA\Property(property="last_week_percent", type="number", format="float", example=10.5)
     *             )
     *         )
     *     ),
     * @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getTotalGroups(Request $request)
    {
        $totalGroups = Group::count();
        $lastWeekGroups = Group::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekGroupPercentage = $lastWeekGroups / $totalGroups * 100;
        $data = [
            "status" => 200,
            "message" => "Total groups fetched successfully",
            "data" => [
                "total_groups" => $totalGroups,
                "last_week_percent" => $lastWeekGroupPercentage
            ]
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/getWeeklyNewUsers",
     *     operationId="AdmingetWeeklyNewUsers",
     *     tags={"AdminDashboard"},
     *     summary="Get weekly new users",
     *     description="Returns the number of new users for the last week and the difference compared to the previous week.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Weekly new users fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="weekly_new_users", type="integer", example=50),
     *                 @OA\Property(property="difference", type="integer", example=10)
     *             )
     *         )
     *     ),
     * @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getWeeklyNewUsers(Request $request)
    {
        $endDateLastWeek = now();
        $startDateLastWeek = now()->subWeek();
        $endDatePreviousWeek = $startDateLastWeek;
        $startDatePreviousWeek = $startDateLastWeek->subWeek();
        $newUsersLastWeek = User::whereBetween('created_at', [$startDateLastWeek, $endDateLastWeek])->count();
        $newUsersPreviousWeek = User::whereBetween('created_at', [$startDatePreviousWeek, $endDatePreviousWeek])->count();
        $difference = $newUsersLastWeek - $newUsersPreviousWeek;
        $data = [
            "status" => 200,
            "message" => "Weekly new users fetched successfully",
            "data" => [
                "weekly_new_users" => $newUsersLastWeek,
                "difference" => $difference
            ]
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/get10NewUsers",
     *     operationId="Adminget10NewUsers",
     *     tags={"AdminDashboard"},
     *     summary="Get 10 newest users",
     *     description="Returns the 10 newest users.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="10 newest users fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="pf_img_url", type="string", example="http://example.com/profile.jpg")
     *                 )
     *             )
     *         )
     *     ),
     * @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function get10NewUsers(Request $request)
    {
        $newUsers = User::select(["id", "first_name", "last_name", "email", "pf_img_url"])->orderBy('created_at', 'desc')->take(10)->get();
        $data = [
            "status" => 200,
            "message" => "10 newest users fetched successfully",
            "data" => $newUsers
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/getTotalPostsOfLastSixMonths",
     *     operationId="AdmingetTotalPostsOfLastSixMonths",
     *     tags={"AdminDashboard"},
     *     summary="Get total posts of the last six months",
     *     description="Returns the total number of posts for each of the last six months.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Total posts of last six months fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="integer", example=100)
     *             )
     *         )
     *     ),
     * @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getTotalPostsOfLastSixMonths(Request $request)
    {
        $months = collect();
        for ($i = 0; $i < 6; $i++) {
            $months->push(now()->subMonths($i)->startOfMonth()->format('Y-m'));
        }
        $months = $months->reverse();

        $postCounts = Post::selectRaw('DATE_TRUNC(\'month\', created_at) AS month, COUNT(*) AS count')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [\Carbon\Carbon::parse($item->month)->format('Y-m') => $item->count];
            });

        $data = $months->map(function ($month) use ($postCounts) {
            return $postCounts->get($month, 0);
        })->values()->toArray();

        $response = [
            "status" => 200,
            "message" => "Total posts of last six months fetched successfully",
            "data" => $data
        ];

        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/users",
     *     operationId="AdmingetAllUsers",
     *     tags={"AdminDashboard"},
     *     summary="Get all users",
     *     description="Returns a list of all users with their posts, groups owned, and groups joined.",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Users Retrieved Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="pf_img_url", type="string", example="http://example.com/profile.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="posts", type="integer", example=10),
     *                     @OA\Property(property="group_own", type="integer", example=2),
     *                     @OA\Property(property="group_member", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getAllUsers(Request $request)
    {
        $searchQuery = $request->query("q");
        $loggedUser = Auth::user();
        if ($loggedUser->role !== 'admin') {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden: You do not have permission to access this resource.'
            ], 403);
        }

        $usersQuery = User::select(["id", "first_name", "last_name", "email", "pf_img_url", "created_at"])
            ->where("role", "!=", "admin")
            ->orderByDesc("created_at");

        if ($searchQuery) {
            $usersQuery->where(function ($query) use ($searchQuery) {
                $query->where('first_name', 'like', "%{$searchQuery}%")
                    ->orWhere('last_name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }

        $users = $usersQuery->get();

        foreach ($users as $user) {
            $posts = Post::where("user_id", $user->id)->count();
            $groupOwn = Group::where("owner_id", $user->id)->count();
            $groupMember = GroupMember::where("user_id", $user->id)->count();
            $user->posts = $posts;
            $user->group_own = $groupOwn;
            $user->group_member = $groupMember;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Users Retrieved Successfully',
            'data' => $users
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/admins",
     *     operationId="AdmingetAllAdmins",
     *     tags={"AdminDashboard"},
     *     summary="Get all admins",
     *     description="Returns a list of all admins.",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Admins Retrieved Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="pf_img_url", type="string", example="http://example.com/profile.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getAllAdmins(Request $request)
    {
        $searchQuery = $request->query('q');
        $loggedUser = Auth::user();
        if ($loggedUser->role !== 'admin') {
            return response()->json([
                'status' => 200,
                'message' => 'Forbidden: You do not have permission to access this resource.'
            ], 200);
        }

        $users = User::select(['id', 'first_name', 'last_name', 'email', 'pf_img_url', 'created_at'])
            ->where('role', '=', 'admin')
            ->where(function ($query) use ($searchQuery) {
                $query->where('first_name', 'ilike', '%' . $searchQuery . '%')
                    ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%');
            })
            ->orderByDesc('created_at')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Admins Retrieved Successfully',
            'data' => $users
        ], 200);
    }
}

