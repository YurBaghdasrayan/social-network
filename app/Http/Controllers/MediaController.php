<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class MediaController extends Controller
{

    /**
     * @OA\Get(
     * path="api/users-media",
     * summary="User photo and video",
     * description="User photo and video",
     * operationId="User photo and video",
     * tags={"Media"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User photo and video",
     *    @OA\JsonContent(
     *               required={"true"},
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User photo and video",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function MyMedia()
    {
        $usersMedia = Post::where('user_id', auth()->user()->id)->with('images')->get();

        foreach ($usersMedia as $usersMedias) {
            if (!$usersMedias->images->isEmpty()) {

                $data[] = $usersMedias->images;
            }
        }
        if (isset($data)) {
            return response()->json([
                'success' => true,
                'message' => 'this your photo and video',
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you do not have photos and videos'
            ], 404);

        }
    }
}
