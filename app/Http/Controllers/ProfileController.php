<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Exceptions\Exception;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\RelationshipResource;
use App\Http\Resources\ReportResource;
use App\Models\Relationship;
use App\Models\Report;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Get User
     *
     * @return json
     */

    /**
     * @OA\Get(
     * path="/profile/{id}",
     * summary="Get a user's profile",
     * description="Get a user's profile",
     * operationId="getUser",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function getUser($id)
    {
        $user = User::Where('id', $id)->first();
        if ($user) {
            // return response()->json([
            //     'status' => "Success",
            //     'message' => Exception::SHOW,
            //     'user' => new UserResource($user),
            // ], Response::HTTP_OK);
            return response()->json(new UserResource($user), Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update User Profile
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/profile/{id}",
     * summary="Update a user's personalInfo and socialInfo",
     * description="Update a user's personalInfo and socialInfo",
     * operationId="updateUserProfile",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Profile",
     *    @OA\JsonContent(
     *          @OA\Property(property="dob", type="string",example="2000/02/20"),
     *          @OA\Property(property="gender", type="string",example="Nam"),
     *          @OA\Property(property="bio", type="string",example="Không có"),
     *          @OA\Property(property="education", type="string",example="University"),
     *          @OA\Property(property="politics", type="string",example="Không có"),
     *          @OA\Property(property="religion", type="string",example="Không có"),
     *          @OA\Property(property="occupation", type="string",example="Không có"),
     *          @OA\Property(property="sexuality", type="string",example="Không có"),
     *          @OA\Property(property="relationship", type="string",example="Không có"),
     *          @OA\Property(property="facebook", type="string",example="Không có"),
     *          @OA\Property(property="instagram", type="string",example="Không có"),
     *          @OA\Property(property="snapchat", type="string",example="Không có"),
     *          @OA\Property(property="linkedIn", type="string",example="Không có"),
     *          @OA\Property(property="twitter", type="string",example="Không có"),
     *          @OA\Property(property="resume", type="string",example="Không có"),
     *          @OA\Property(property="coverLetter", type="string",example="Không có"),
     *          @OA\Property(property="email_2", type="string",example="Không có"),
     *          @OA\Property(property="website", type="string",example="Không có"),
     *          @OA\Property(property="contact", type="string",example="Không có"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function updateUserProfile(Request $request, $id)
    {
        $user = User::Where('id', $id)->first();
        if ($user) {
            $data = $request->all();
            $user->update($data);
            return response()->json([
                'status' => "Success",
                'message' => Exception::UPDATE_SUCCESS,
                'user' => new UserResource($user),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete User.
     *
     * @return json
     */

    /**
     * @OA\Delete(
     * path="/profile/{id}",
     * summary="Removes a user's account",
     * description="Removes a user's account",
     * operationId="removeUserAccount",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="id", type="string"),
     *        )
     *     )
     * )
     */

    public function removeUserAccount($id)
    {
        $user = User::whereid($id)->first();
        if ($user) {
            $user->delete($user);
            return response()->json([
                'status' => "Success",
                'message' => Exception::DELETE_SUCCESS,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update User Profile Location
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/profile/{id}/location",
     * summary="Update a user's location",
     * description="Update a user's location",
     * operationId="updateUserLocation",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Profile",
     *    @OA\JsonContent(
     *          @OA\Property(property="latitude", type="integer", example="198"),
     *          @OA\Property(property="longitude", type="integer", example="123"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function updateUserLocation(Request $request, $id)
    {
        $user = User::Where('id', $id)->first();
        if ($user) {
            $data = $request->all();
            $user->update($data);
            return response()->json([
                'status' => "Success",
                'message' => Exception::UPDATE_SUCCESS,
                'user' => new UserResource($user),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get User Relationships
     *
     * @return json
     */

    /**
     * @OA\Get(
     * path="/profile/{id}/relationships",
     * summary="Gets user's relationships",
     * description="Gets user's relationships",
     * operationId="getUserRelationships",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *     )
     * )
     */

    public function getUserRelationships($id)
    {
        $rela_list = Relationship::where('user_id', '=', $id)->get();
        if ($rela_list) {
            return RelationshipResource::collection($rela_list);
        } else {
            return response()->json([
                'message' => 'User is not relationships'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get Relationship
     *
     * @return json
     */

    /**
     * @OA\Get(
     * path="/profile/{id}/relationships/{friend}",
     * summary="Gets the relationship",
     * description="Gets the relationship",
     * operationId="getRelationship",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Parameter(
     *    name="friend",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    )
     * )
     */

    public function getRelationship($userId, $friendId)
    {
        $rela = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->first();
        if ($rela) {
            return response()->json([
                'status' => "Success",
                'message' => Exception::GET_ALL_DATA,
                'relationship' => new RelationshipResource($rela),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not relationships'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Request Friend
     *
     * @return json
     */

    /**
     * @OA\Put(
     * path="/profile/{id}/relationships/{friend}",
     * summary="Request user as friend",
     * description="Request user as friend",
     * operationId="requestFriend",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Parameter(
     *    name="friend",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function requestFriend($userId, $friendId)
    {
        $relaUserToFriend = $relaFriendToUser = '';
        $status = 'requested';
        $time_zone = env('TIME_ZONE');
        $requestedTime = Carbon::now($time_zone)->format('Y-m-d H:i:s');

        $countRela = Relationship::where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->get();


        if (count($countRela) > 0) {
            $relaUserToFriend = Relationship::where('user_id', '=', $userId)
                ->where('friend_id', '=', $friendId)
                ->update([
                    'status' => $status,
                    'dateRequested' => $requestedTime
                ]);

            $relaFriendToUser = Relationship::where('user_id', '=', $friendId)
                ->where('friend_id', '=', $userId)
                ->update([
                    'status' => $status,
                    'dateRequested' => $requestedTime
                ]);

            $relaUserToFriend = Relationship::where('user_id', '=', $userId)
                ->where('friend_id', '=', $friendId)
                ->first();

            $relaFriendToUser = Relationship::where('user_id', '=', $friendId)
                ->where('friend_id', '=', $userId)
                ->first();
        } else {
            $relaUserToFriend = Relationship::create(
                [
                    'user_id' => $userId,
                    'friend_id' => $friendId,
                    "status" => $status,
                    "dateRequested" => $requestedTime,
                ],
            );

            $relaFriendToUser = Relationship::create(
                [
                    'user_id' => $friendId,
                    'friend_id' => $userId,
                    "status" => $status,
                    "dateRequested" => $requestedTime,
                ],
            );
        }

        if ($relaFriendToUser && $relaUserToFriend) {
            return response()->json([
                'status' => "Success",
                'message' => Exception::UPDATE_SUCCESS,
                'relationship_user_to_friend' => new RelationshipResource($relaUserToFriend),
                'relationship_friend_to_user' => new RelationshipResource($relaFriendToUser),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not relationships'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update Friend
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/profile/{id}/relationships/{friend}",
     * summary="Update user's friend status - accept/reject/block",
     * description="Update user's friend status - accept/reject/block",
     * operationId="updateFriend",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Parameter(
     *    name="friend",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Profile",
     *    @OA\JsonContent(
     *          @OA\Property(property="status", type="string",example="blocked"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function updateFriend(Request $request, $userId, $friendId)
    {
        $time_zone = env('TIME_ZONE');
        $actionTime = Carbon::now($time_zone)->format('Y-m-d H:i:s');

        $relaUserToFriend = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->first();

        $relaFriendToUser = Relationship::where('user_id', '=', $friendId)
            ->where('friend_id', '=', $userId)
            ->first();

        if ($relaUserToFriend && $relaFriendToUser) {

            $data = $request->all();

            $relaUserToFriend->update($data);
            $relaFriendToUser->update($data);

            if ($request->status === 'accepted') {
                $relaUserToFriend->update(['dateAccepted' => $actionTime]);
                $relaFriendToUser->update(['dateAccepted' => $actionTime]);
            }
            if ($request->status === 'rejected') {
                $relaUserToFriend->update(['dateRejected' => $actionTime]);
                $relaFriendToUser->update(['dateRejected' => $actionTime]);
            }
            if ($request->status === 'blocked') {
                $relaUserToFriend->update(['dateBlocked' => $actionTime]);
                $relaFriendToUser->update(['dateBlocked' => $actionTime]);
            }

            return response()->json([
                'status' => "Success",
                'message' => Exception::UPDATE_SUCCESS,
                'relationship_user_to_friend' => new RelationshipResource($relaUserToFriend),
                'relationship_friend_to_user' => new RelationshipResource($relaFriendToUser),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not relationships'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Unblock User
     *
     * @return json
     */

    /**
     * @OA\Delete(
     * path="/profile/{id}/relationships/{friend}",
     * summary="Unblock user's friend - removing their relationship",
     * description="Unblock user's friend - removing their relationship",
     * operationId="unblockUser",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Parameter(
     *    name="friend",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function unblockAndDeleteUserRelationship($userId, $friendId)
    {
        $relaUserToFriend = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->first();

        $relaFriendToUser = Relationship::where('user_id', '=', $friendId)
            ->where('friend_id', '=', $userId)
            ->first();

        if ($relaUserToFriend->status != 'blocked' && $relaFriendToUser->status != 'blocked') {
            $relaUserToFriend->delete($relaUserToFriend);
            $relaFriendToUser->delete($relaFriendToUser);
            return response()->json([
                'status' => "Success",
                'message' => Exception::DELETE_SUCCESS,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is blocked. Unblock do delete'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get Likes
     *
     * @return json
     */

    /**
     * @OA\Get(
     * path="/profile/{id}/likes",
     * summary="Gets the number of likes a user's profile has",
     * description="Gets the number of likes a user's profile has",
     * operationId="getLikes",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Count",
     *   ),
     * )
     */

    public function getLikes($id)
    {
        $user = User::Where('id', $id)->first();
        if ($user) {
            return response()->json([
                'status' => "Success",
                'message' => Exception::SHOW,
                'likeCount' => $user->likeCount
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Like Profile
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/profile/{id}/likes",
     * summary="Like user profile",
     * description="Like user profile",
     * operationId="likeProfile",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Profile",
     *    @OA\JsonContent(
     *       required={"profileLikeId"},
     *       @OA\Property(property="profileLikeId", type="string",example="2"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function likeProfile(Request $request, $id)
    {
        $user = User::Where('id', $id)->first();
        $currentLiked = $user->liked;
        $userLikeFullname = $user->firstname . ' ' . $user->lastname;
        $user->update(['liked' => $currentLiked + 1]);

        $data = $request->all();
        $profileLikeId = $data['profileLikeId'];
        $profileLike = User::Where('id', $profileLikeId)->first();
        $currentProfileLiked = $profileLike->likeCount;
        $profileLike->update(['likeCount' => $currentProfileLiked + 1]);

        if ($user) {
            return response()->json([
                'status' => "Success",
                'message' => Exception::SHOW,
                'username like' => $userLikeFullname,
                'current liked' => $currentLiked,
                'liked' => $user->liked,
                'current profile like' => $currentProfileLiked,
                'likeCount' => $profileLike->likeCount
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Unlike Profile
     *
     * @return json
     */

    /**
     * @OA\Delete(
     * path="/profile/{id}/likes",
     * summary="Unlike user profile",
     * description="Unlike user profile",
     * operationId="unlikeProfile",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Profile",
     *    @OA\JsonContent(
     *       required={"profileLikeId"},
     *       @OA\Property(property="profileLikeId", type="string",example="2"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function unlikeProfile(Request $request, $id)
    {
        $user = User::Where('id', $id)->first();
        $currentLiked = $user->liked;
        $user->update(['liked' => $currentLiked - 1]);

        $data = $request->all();
        $profileLikeId = $data['profileLikeId'];
        $profileLike = User::Where('id', $profileLikeId)->first();
        $currentProfileLiked = $profileLike->likeCount;
        $profileLike->update(['likeCount' => $currentProfileLiked - 1]);

        if ($user) {
            return response()->json([
                'status' => "Success",
                'message' => Exception::SHOW,
                'current liked' => $currentLiked,
                'liked' => $user->liked,
                'current profile like' => $currentProfileLiked,
                'likeCount' => $profileLike->likeCount
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get User Report
     *
     * @return json
     */

    /**
     * @OA\Get(
     * path="/profile/{id}/reports",
     * summary="Gets the reports that were submitted by the user",
     * description="Gets the reports that were submitted by the user",
     * operationId="getUserReports",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *          @OA\Property(property="userId", type="string",example="1"),
     *       @OA\Property(property="info", type="string",example="Buôn bán hàng kém chất lượng"),
     *       @OA\Property(property="dateCreated", type="string", example="2022/05/10"),
     *        )
     *     )
     * )
     */

    public function getUserReports($id)
    {
        $userReport = Report::where('user_id', $id)->get();
        if ($userReport) {
            return ReportResource::collection($userReport);
        } else {
            return response()->json([
                'message' => 'User is not reported'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Report User 
     *
     * @return json
     */

    /**
     * @OA\Put(
     * path="/profile/{id}/reports",
     * summary="Report a user",
     * description="Report a user",
     * operationId="reportUser",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Profile",
     *    @OA\JsonContent(
     *       required={"info","dateCreated"},
     *       @OA\Property(property="info", type="string",example="Buôn bán hàng kém chất lượng"),
     *       @OA\Property(property="dateCreated", type="string", example="2022/05/10"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function reportUser(Request $request, $id)
    {
        $time_zone = env('TIME_ZONE');
        $createdTime = Carbon::now($time_zone)->format('Y-m-d H:i:s');
        $user_id = Auth::user()->id;
        $existReport = Report::where('id', $id)
            ->where('user_id', $user_id)
            ->first();

        $noExistReport = Report::where('user_id', $user_id)
            ->where('id', '!=', $id)
            ->first();

        if ($existReport) {
            $existReport->update(
                [
                    'info' => $request->info,
                    'dateCreated' => $createdTime,
                ]
            );
            return response()->json([
                'status' => "Success",
                'message' => Exception::SHOW,
                'report' => new ReportResource($existReport),
            ], Response::HTTP_OK);
        } else if ($noExistReport) {
            $noExistReport->create(
                [
                    'id' => $id,
                    'user_id' => $user_id,
                    'info' => $request->info,
                    'dateCreated' => $createdTime
                ]
            );
            return response()->json([
                'status' => "Success",
                'message' => Exception::SHOW,
                'report' => new ReportResource($noExistReport),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
