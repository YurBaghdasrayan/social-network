<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Post;
use App\Models\Groupmember;
use Illuminate\Support\Facades\Gate;
use App\Events\GroupRequestEvent;


class GroupController extends Controller
{
    public function index()
    {
        $data = Groupmember::where('receiver_id', auth()->user()->id)
            ->where('user_status', 'unconfirm')
            ->with('sender')
            ->get();

        $userData = [];

        foreach ($data as $datum) {
            $int = (int)$datum['sender_id'];

            $userss = User::where('id', $int)->get();
            $userData[] = $userss;
        }
        event(new GroupRequestEvent($userData));

        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'sent you a request',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ]);
        }
    }

    public function YourGroup(Request $request)
    {
        $user = Groupmember::where('receiver_id', auth()->user()->id)
            ->where('user_status', 'true')
            ->with('group')
            ->get();
//        dd($user);
//        $groupData = Group::where()

        if ($user->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'you dont have a group'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }
    }

    /**
     * @OA\Post(
     * path="api/create-group",
     * summary="Group created successfully",
     * description="Group created successfully",
     * operationId="Groups created successfully",
     * tags={"Groups"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Group created successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="name", type="string",format="text", example="some"),
     *               @OA\Property(property="image", type="file",format="file", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Group created successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $image = $request->file('image');

        if ($image) {
            $destinationPath = 'uploads';
            $user_image = time() . $image->getClientOriginalName();
            $image->move($destinationPath, $user_image);
        } else {
            $user_image = null;
        }

        $createGroups = [
            'name' => $request->name,
            'user_id' => auth()->user()->id,
            'image' => $user_image,
        ];

        $data = Group::create($createGroups);
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'group successfully created'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/create-moderator",
     * summary="Moderator created successfully",
     * description="Moderator created successfully",
     * operationId="Moderators created successfully",
     * tags={"Group Moderator"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Moderator created successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="receiver_id", type="integer",format="integer", example=1),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Moderator created successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function ModeratorCreate(Request $request)
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {
            $createModerator = Groupmember::where('receiver_id', $request->moderator_id)
                ->update(['role' => 'moderator']);

            if ($createModerator) {
                return response()->json([
                    'success' => true,
                    'message' => 'moderator successfully assigned'
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you are not an administrator'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/admin-update-posts",
     * summary="Admin updated successfully",
     * description="Admin updated successfully",
     * operationId="Admin updated successfully",
     * tags={"Group Admin"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Admins updated successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="post_id", type="integer",format="integer", example=1),
     *               @OA\Property(property="description", type="string",format="string", example="some text"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Admin updated successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function updatePosts(Request $request)
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {

            $group = Post::where('id', $request->post_id)->first();

            if ($isAdmin[0]->id == $group['group_id']) {
                if (isset($request->description)) {
                    $update = Post::where('id', $request->post_id)->update(['description' => $request->description]);
                    if ($update) {
                        return response()->json([
                            'success' => true,
                            'message' => 'post description successfully updated'
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'something was wrong'
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'something was wrong'
                    ], 422);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you are not an administrator'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/admin-delete-posts",
     * summary="Post deleted successfully",
     * description="Post deleted successfully",
     * operationId="Post deleted successfully",
     * tags={"Group Admin"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Post deleted successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="post_id", type="integer",format="integer", example=1),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Post deleted successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function deletePosts(Request $request)
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {

            $group = Post::where('id', $request->post_id)->first();

            if ($isAdmin[0]->id == $group['group_id']) {
                $delete = Post::where('id', $request->post_id)->delete();
                if ($delete) {
                    return response()->json([
                        'success' => true,
                        'message' => 'post successfully deleted'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'something was wrong'
                    ], 422);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you are not an administrator'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/moderator-update-posts",
     * summary="Post updated successfully",
     * description="Post updated successfully",
     * operationId="Post updated successfully",
     * tags={"Group Moderator"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Posts updated successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="post_id", type="integer",format="integer", example=1),
     *               @OA\Property(property="description", type="string",format="string", example="some text"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Posts updated successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function ModeratotupdatePosts(Request $request)
    {
        $isModerator = Groupmember::where('receiver_id', auth()->user()->id)->where('role', 'moderator')->get();

        if (!$isModerator->isEmpty()) {
            $group = Post::where('id', $request->post_id)->first();

            if ($isModerator[0]->group_id == $group['group_id']) {
                if (isset($request->description)) {
                    $update = Post::where('id', $request->post_id)->update(['description' => $request->description]);
                    if ($update) {
                        return response()->json([
                            'success' => true,
                            'message' => 'post description successfully updated'
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'something was wrong'
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'something was wrong'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/moderator-delete-posts",
     * summary="Postsss deleted successfully",
     * description="Postsss deleted successfully",
     * operationId="Postsss deleted successfully",
     * tags={"Group Moderator"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Postss deleted successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="post_id", type="integer",format="integer", example=1),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Postsss deleted successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function ModeratordeletePosts(Request $request)
    {
        $isModerator = Groupmember::where('receiver_id', auth()->user()->id)->get();

        if (!$isModerator->isEmpty()) {

            $group = Post::where('id', $request->post_id)->first();

            if ($isModerator[0]->group_id == $group['group_id']) {
                $delete = Post::where('id', $request->post_id)->delete();
                if ($delete) {
                    return response()->json([
                        'success' => true,
                        'message' => 'post successfully deleted'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'something was wrong'
                    ], 422);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you are not an administrator'
            ], 422);
        }
    }

    /**
     * @OA\Get(
     * path="api/admin-delete-user",
     * summary="User deleted successfully",
     * description="User deleted successfully",
     * operationId="User deleted successfully",
     * tags={"Group Admin"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User deleted successfully",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="id", type="integer",format="integer", example=1),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User deleted successfully",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function AdminDeleteUsers($id)
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {

            $deleteUsers = Groupmember::where('user_id', $id)->delete();

            if ($deleteUsers) {
                return response()->json([
                    'success' => true,
                    'message' => 'user successfully deleted on group'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/update-group-name",
     * summary="group name successfully changed",
     * description="group name successfully changed",
     * operationId="group name successfully changed",
     * tags={"Group Admin"},
     * @OA\RequestBody(
     *    required=true,
     *    description="group name successfully changed",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="id", type="integer",format="integer", example=1),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="group name successfully changed",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function UpdateGroup(Request $request)
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {
            if ($request->name) {
                $updateGroupName = Group::where('user_id', auth()->user()->id)->update(['name' => $request->name]);
                if ($updateGroupName) {
                    return response()->json([
                        'success' => true,
                        'message' => 'group name successfully changed'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'something was wrong'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        }
    }

    /**
     * @OA\Get(
     * path="api/delete-group",
     * summary="group successfully deleted",
     * description="group successfully deleted",
     * operationId="group successfully deleted",
     * tags={"Group Admin"},
     * @OA\RequestBody(
     *    required=true,
     *    description="group successfully deleted",
     *    @OA\JsonContent(
     *               required={"true"},
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="group successfully deleted",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function deleteGroup()
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {
            $deleteGroup = Group::where('user_id', auth()->user()->id)->delete();
            if ($deleteGroup) {
                return response()->json([
                    'success' => true,
                    'message' => 'group successfully deleted'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/delete-moderator",
     * summary="moderator successfully deleted",
     * description="moderator successfully deleted",
     * operationId="moderator successfully deleted",
     * tags={"Group Admin"},
     * @OA\RequestBody(
     *    required=true,
     *    description="moderator successfully deleted",
     *    @OA\JsonContent(
     *               required={"true"},
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="moderator successfully deleted",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function DeleteModerator(Request $request)
    {
        $isAdmin = Group::where('user_id', auth()->user()->id)->get();

        if (!$isAdmin->isEmpty()) {
            if ($request->receiver_id) {
                $updateGroupName = Groupmember::where('receiver_id', $request->receiver_id)->update(['role' => 'participant']);
                if ($updateGroupName) {
                    return response()->json([
                        'success' => true,
                        'message' => 'moderator successfully deleted'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'something was wrong'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        }
    }
}
