<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Exceptions\Exception;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\RelationshipResource;
use App\Models\Relationship;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;
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
            return response()->json([
                'status' => "Success",
                'message' => Exception::SHOW,
                'user' => new UserResource($user),
            ], Response::HTTP_OK);
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
     *          @OA\Property(property="dob", type="string",example="2001/02/20"),
     *          @OA\Property(property="gender", type="string",example="Nam"),
     *          @OA\Property(property="bio", type="string",example="Không có"),
     *          @OA\Property(property="education", type="string",example="SGU"),
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
     *    name="friendId",
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
                'relationship' => new RelationshipResource($rela)
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
     *    name="friendId",
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

    public function requestFriend(Request $request, $userId, $friendId)
    {
        $status = 'requested';
        $requestedTime = Carbon::now('Asia/Ho_Chi_Minh');

        $rela = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->update(['status' => $status], ['dateRequested' => $requestedTime]);

        $rela = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->first();

        if ($rela) {
            return response()->json([
                'status' => "Success",
                'message' => Exception::UPDATE_SUCCESS,
                'relationship' => new RelationshipResource($rela)
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
     *    name="friendId",
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
        $rela = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->first();

        if ($rela) {
            $data = $request->all();
            $rela->update($data);
            return response()->json([
                'status' => "Success",
                'message' => Exception::UPDATE_SUCCESS,
                'relationship' => new RelationshipResource($rela)
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
     *    name="friendId",
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
        $rela = Relationship::where('user_id', '=', $userId)
            ->where('friend_id', '=', $friendId)
            ->first();

        if ($rela->status != 'blocked') {
            $rela->delete($rela);
            return response()->json([
                'status' => "Success",
                'message' => Exception::DELETE_SUCCESS,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'User is blocked'
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
     *    @OA\Property(
     *       property="count",
     *       type="integer",
     *    )
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
                'liked' => $user->liked
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
     *    name="likeProfileId",
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

    public function likeProfile(Request $request)
    {
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
     *    name="likeProfileId",
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

    public function unlikeProfile(Request $request)
    {
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
     *          @OA\Property(property="userId", type="string"),
     *          @OA\Property(property="info", type="string"),
     *          @OA\Property(property="dateCreated", type="string", format="date-time"),
     *        )
     *     )
     * )
     */

    public function getUserReports(Request $request)
    {
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

    public function reportUser(Request $request)
    {
    }
}
