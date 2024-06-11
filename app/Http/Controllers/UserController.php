<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Mail\SendMailExisted;
use App\Models\Folder;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\GroupMember;
use App\Models\GroupRequest;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\SavedPost;
use App\Models\User;
use App\Models\UserFollower;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Add this import statement

class UserController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     operationId="register/createUser",
     *     tags={"Register"},
     *     summary="register Insert",
     *     description="-",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username","email", "password"},
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="password", type="password"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="pf_img_url", type="string"),
     *                 @OA\Property(property="social_login_info", type="string")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Register Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => [
                        'required',
                        'min:8',
                        'regex:/[a-z]/',      // must contain at least one lowercase letter
                        'regex:/[A-Z]/',      // must contain at least one uppercase letter
                        'regex:/[0-9]/',      // must contain at least one digit
                        'regex:/[@$!%*#?&]/', // must contain a special character
                    ],
                    'pf_img_url' => 'nullable',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!$request->pf_img_url) {
                $pfImgUrl = "https://i.pinimg.com/564x/25/ee/de/25eedef494e9b4ce02b14990c9b5db2d.jpg";
            } else {
                $pfImgUrl = $request->pf_img_url;
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => "user",
                'pf_img_url' => $pfImgUrl,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function createAdmin(Request $request)
    {
        try {
            // Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email',
                    'pf_img_url' => 'nullable',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $existedUser = User::where('email', $request->email)->first();
            if ($existedUser) {
                if ($existedUser->role == "admin") {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Admin Already Exists',
                    ], 400);
                }


                $existedUser->role = "admin";
                $existedUser->save();

                $details = [
                    'first_name' => $existedUser->first_name,
                    'email' => $existedUser->email,
                ];

                Mail::to($details['email'])->send(new SendMailExisted($details));

                return response()->json([
                    'status' => 200,
                    'message' => 'Admin Assigned Successfully',
                ], 200);
            }

            // Generate password
            $password = $this->generatePassword();

            if (!$request->pf_img_url) {
                $pfImgUrl = "https://i.pinimg.com/564x/25/ee/de/25eedef494e9b4ce02b14990c9b5db2d.jpg";
            } else {
                $pfImgUrl = $request->pf_img_url;
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role' => "admin",
                'pf_img_url' => $pfImgUrl,
            ]);

            $user->save();

            $details = [
                'first_name' => $user->first_name,
                'email' => $user->email,
                'password' => $password
            ];

            Mail::to($details['email'])->send(new SendMail($details));

            return response()->json([
                'status' => 200,
                'message' => 'Admin Created Successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    private function generatePassword($length = 12)
    {
        $password = '';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '@$!%*#?&';

        // Ensure the password contains at least one character from each set
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];

        // Fill the remaining length with a random selection of all characters
        $allChars = $lowercase . $uppercase . $numbers . $specialChars;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }

        // Shuffle the password to ensure random order
        return str_shuffle($password); // Use PHP's built-in str_shuffle function
    }
    public function createUserMobile(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                print_r('Error' . $validateUser->errors()->first());


                if ($validateUser->errors()->first() == "The email has already been taken.") {
                    return response()->json([
                        "status" => 400,
                        "message" => "Email already taken"
                    ], 404);
                }
                return response()->json([
                    'status' => 401,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!$request->pf_img_url) {
                $pfImgUrl = "https://i.pinimg.com/736x/e7/fd/e7/e7fde7197f89cac7846e66ad629287cc.jpg";
            } else {
                $pfImgUrl = $request->pf_img_url;
            }

            if (!$request->social_login_info) {
                $socialLoginInfo = "{}";
            } else {
                $socialLoginInfo = $request->social_login_info;
            }

            $user = User::create([
                'first_name' => "First Name",
                'last_name' => "Last Name",
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => "user",
                'pf_img_url' => $pfImgUrl,
                'social_login_info' => $socialLoginInfo,
                'followers' => "[]",
                'followings' => "[]",
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     operationId="authLogin",
     *     tags={"Login"},
     *     summary="User Login",
     *     description="Login User Here",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="email"),
     *                 @OA\Property(property="password", type="password")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Login Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Incorrect Email or Password',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            $user->followers = UserFollower::where('user_id', $user->id)->count();
            $user->followings = UserFollower::where('follower_id', $user->id)->count();



            return response()->json([
                'status' => 200,
                'message' => 'User Logged In Successfully',
                'data' => [
                    'token' => $user->createToken("API TOKEN")->plainTextToken,
                    "user" => $user
                ]

            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
     * Get All Users
     * @OA\Get(
     *     path="/api/auth/users",
     *     operationId="getAllUsers",
     *     tags={"User"},
     *     summary="Get All Users",
     *     description="Retrieve all users. Only accessible to admin users.",
     *     @OA\Response(
     *         response=200,
     *         description="Users Retrieved Successfully",
     *         @OA\JsonContent(type="array", @OA\Items())
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getAllUsers(Request $request)
    {
        // Check if the authenticated user is an admin
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden: You do not have permission to access this resource.'
            ], 403);
        }


        // Get all users
        $users = User::all();

        return response()->json([
            'status' => 200,
            'message' => 'Users Retrieved Successfully',
            'data' => $users
        ], 200);
    }

    public function getUserData(Request $request)
    {
        $user = Auth::user();

        $invites = GroupInvite::where('user_id', $user->id)->get();

        $myGroupsAsAdmin = GroupMember::where('user_id', $user->id)
            ->where('role', 'admin')
            ->get();

        // Check if any group_id matches in GroupRequest
        $groupIds = $myGroupsAsAdmin->pluck('group_id');
        $joinRequests = GroupRequest::whereIn('group_id', $groupIds)->get();

        $followerCount = UserFollower::where('user_id', $user->id)->count();
        $followingCount = UserFollower::where('follower_id', $user->id)->count();

        $data = [
            "id" => $user->id,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "role" => $user->role,
            "pf_img_url" => $user->pf_img_url,
            "followers" => $followerCount,
            "followings" => $followingCount,
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
            "invites" => count($invites),
            "group_req" => count($joinRequests),
            "total_noti" => count($invites) + count($joinRequests),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'User Data',
            // 'data' => $data
            'data' => $data
        ], 200);
    }
    public function getUserDataMobile(Request $request)
    {
        $user = Auth::user();

        return response()->json($user, 200);
    }

    public function getUserDataById(Request $request, $id)
    {

        $loggedInUser = Auth::user();

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User Not Found'
            ], 404);
        }

        $followerCount = UserFollower::where('user_id', $id)->count();
        $followingCount = UserFollower::where('follower_id', $id)->count();

        $isFollowing = false;
        $follwing = UserFollower::where("user_id", $id)->where("follower_id", $loggedInUser->id)->first();
        if ($follwing) {
            $isFollowing = true;
        }

        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'is_following' => $isFollowing,
            'email' => $user->email,
            'role' => $user->role,
            'pf_img_url' => $user->pf_img_url,
            'followers' => $followerCount,
            'followings' => $followingCount,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];

        return response()->json([
            'status' => 200,
            'message' => 'User Data',
            'user' => $data
        ], 200);
    }

    public function editProfileMobile(Request $request)
    {
        //only username
        $loggedUser = Auth::user();

        if ($request->username == $loggedUser->username) {
            return response()->json([
                'status' => 200,
                'message' => 'No changes made'
            ], 200);
        }

        //Validated
        $validateUser = Validator::make(
            $request->all(),
            [
                'username' => 'required|unique:users',
            ]
        );

        if ($validateUser->fails()) {
            print_r('Error' . $validateUser->errors()->first());

            if ($validateUser->errors()->first() == "The username has already been taken.") {
                return response()->json([
                    "status" => 400,
                    "message" => "Username already taken"
                ], 403);
            }
            return response()->json([
                'status' => 401,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $user = User::find($loggedUser->id);

        $user->username = $request->username;

        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Profile Updated Successfully',
            'data' => $user
        ], 200);
    }
    public function editProfile(Request $request)
    {
        //only username
        $loggedUser = Auth::user();

        if ($request->username == $loggedUser->username && $request->first_name == $loggedUser->first_name && $request->last_name == $loggedUser->last_name) {
            return response()->json([
                'status' => 200,
                'message' => 'No changes made'
            ], 200);
        }

        //Validated
        if ($request->username != $loggedUser->username) {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                ]
            );
        } else {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                ]
            );
        }

        if ($validateUser->fails()) {
            print_r('Error' . $validateUser->errors()->first());

            return response()->json([
                'status' => 401,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $user = User::find($loggedUser->id);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Profile Updated Successfully',
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear Socialite session
        $request->session()->forget('state'); // Google OAuth state key
        $request->session()->forget('code'); // OAuth authorization code
        $request->session()->forget('oauth_token'); // OAuth token if present

        return response()->json([
            'status' => 200,
            'message' => 'User Logged Out Successfully'
        ], 200);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => 200,
            'message' => 'User Logged Out From All Devices Successfully'
        ], 200);
    }

    public function checkUserPassword(Request $request)
    {
        $loggedUser = Auth::user();

        $validateUser = Validator::make(
            $request->all(),
            [
                'password' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if (!Hash::check($request->password, $loggedUser->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Incorrect Password',
            ], 401);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Password Matched',
        ], 200);
    }

    public function updateUserPassword(Request $request)
    {
        $loggedUser = Auth::user();


        $validateUser = Validator::make(
            $request->all(),
            [
                'old_password' => 'required',
                'new_password' => [
                    'required',
                    'min:8',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ]
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if (!Hash::check($request->old_password, $loggedUser->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Incorrect Old Password',
            ], 401);
        }

        //check if new password is same as old password
        if (Hash::check($request->new_password, $loggedUser->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'New Password can not be same as Old Password',
            ], 401);
        }

        $loggedUser->password = Hash::make($request->new_password);
        $loggedUser->save();

        return response()->json([
            'status' => 200,
            'message' => 'Password Updated Successfully',
        ], 200);
    }


    public function updateUserPfImg(Request $request)
    {
        $loggedUser = Auth::user();

        $validateUser = Validator::make(
            $request->all(),
            [
                'pf_img_url' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $loggedUser->pf_img_url = $request->pf_img_url;
        $loggedUser->save();

        return response()->json([
            'status' => 200,
            'message' => 'Profile Image Updated Successfully',
        ], 200);
    }

    public function adminUpdateUserInfo(Request $request, $id)
    {
        $loggedUser = Auth::user();

        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'pf_img_url' => 'required',
        ]);

        // The validation check is handled by the `validate` method,
        // so you don't need to manually check for validation errors

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User Not Found'
            ], 404);
        }

        // Check if logged-in user is admin
        if ($loggedUser->role !== 'admin') {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden: You do not have permission to access this resource.'
            ], 403);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->pf_img_url = $request->pf_img_url;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'User Updated Successfully',
        ], 200);
    }

    public function removeAdmin(Request $request, $id)
    {
        $loggedUser = Auth::user();

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User Not Found'
            ], 404);
        }

        // Check if logged-in user is admin
        if ($loggedUser->role !== 'admin') {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden: You do not have permission to access this resource.'
            ], 403);
        }

        $user->role = 'user';
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Admin Removed Successfully',
        ], 200);
    }

    public function deleteUser(Request $request, $id)
    {
        $loggedUser = Auth::user();

        // Check if logged-in user is admin
        if ($loggedUser->role !== 'admin') {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden: You do not have permission to access this resource.'
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User Not Found'
            ], 404);
        }

        $user->delete();

        //remove all group invites
        GroupInvite::where('user_id', $id)->delete();

        //remove all group requests
        GroupRequest::where('user_id', $id)->delete();

        //remove all group members
        GroupMember::where('user_id', $id)->delete();

        $groups = Group::where('owner_id', $id)->get();

        foreach ($groups as $group) {
            Post::where('group_id', $group->id)->delete();
            $group->delete();
        }

        Post::where('user_id', $id)->delete();

        Folder::where('user_id', $id)->delete();

        PostLike::where('user_id', $id)->delete();

        SavedPost::where('user_id', $id)->delete();

        UserFollower::where('user_id', $id)->delete();
        UserFollower::where('follower_id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'User Deleted Successfully',
        ], 200);
    }
}
