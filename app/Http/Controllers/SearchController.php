<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function advancedsearch(Request $request)
    {
        print_r("hi");
        // $title = $request->query('title');
        // $description = $request->query('description');

        // print_r($title);
        // print_r($description);

        // $query = Post::table('posts');

        // if ($title) {
        //     $query->where('title', 'LIKE', "%{$title}%");
        // }

        // if ($description) {
        //     $query->where('description', 'LIKE', "%{$description}%");
        // }

        // $posts = $query->paginate(10);

        // return response()->json($posts); 
        return response()->json("hi", 200);
    }

    public function searchUsers(Request $request)
    {
        $term = $request->query('term');

        $query = DB::table('users');

        if ($term) {
            // with first name or last name or email
            $query->select("id", "first_name", "last_name", "email", "pf_img_url", "created_at")->where('first_name', 'ilike', '%' . $term . '%')
                ->orWhere('last_name', 'ilike', '%' . $term . '%')
                ->orWhere('email', 'ilike', '%' . $term . '%');
        }

        $users = $query->limit(10)->get();

        $data = [
            "status" => 200,
            "users" => $users
        ];

        return response()->json($data, 200);
    }

    public function searchGroups(Request $request)
    {
        $term = $request->query('term');

        $query = DB::table('groups');

        if ($term) {
            $query->where('title', 'iLIKE', "%{$term}%");
        }

        $groups = $query->limit(10)->get();

        for ($i = 0; $i < count($groups); $i++) {
            //get members count
            $memberCount = GroupMember::where("group_id", $groups[$i]->id)->count();
            $groups[$i]->member_count = $memberCount;
        }

        $data = [
            "status" => 200,
            "groups" => $groups
        ];

        return response()->json($data, 200);
    }

    public function searchPosts(Request $request)
    {
        $term = $request->query('term');

        $query = DB::table('posts');

        if ($query) {
            $query->where('title', 'iLIKE', "%{$term}%")->where("status", "public");
        }

        $posts = $query->limit(20)->get();

        $data = [
            "status" => 200,
            "posts" => $posts
        ];

        return response()->json($data, 200);
    }

    public function getRandomUsers()
    {
        $users = DB::table('users')->inRandomOrder()->limit(10)->get();

        $data = [
            "status" => 200,
            "users" => $users
        ];

        return response()->json($data, 200);
    }

    public function getRandomGroups()
    {
        $groups = DB::table('groups')->inRandomOrder()->limit(10)->get();

        for ($i = 0; $i < count($groups); $i++) {
            $memberCount = GroupMember::where("group_id", $groups[$i]->id)->count();
            $groups[$i]->member_count = $memberCount;
        }

        $data = [
            "status" => 200,
            "groups" => $groups
        ];

        return response()->json($data, 200);
    }

    public function getRandomPosts()
    {
        $posts = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.first_name as first_name', 'users.last_name as last_name', 'users.pf_img_url as user_pf_img_url') // add other user fields if needed
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $data = [
            "status" => 200,
            "posts" => $posts
        ];

        return response()->json($data, 200);
    }
}
