<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     *    description="Login Payload",
     *    @OA\JsonContent(
     *          @OA\Property(property="id", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="password", type="string"),
     *          @OA\Property(property="phone", type="string"),
     *          @OA\Property(property="firstname", type="string"),
     *          @OA\Property(property="lastname", type="string"),
     *          @OA\Property(property="ghostMode", type="boolean"),
     *          @OA\Property(property="subscription", type="string"),
     *          @OA\Property(property="location", type="object",
     *              @OA\Property(property="latitude", type="integer"),
     *              @OA\Property(property="longitude", type="integer"),
     *          ),
     *          @OA\Property(property="locationTimestamp", type="string",format ="date-time"),
     *          @OA\Property(property="pictureProfile", type="string"),
     *          @OA\Property(property="pictureCover", type="string"),
     *          @OA\Property(property="personalInfo", type="object",
     *              @OA\Property(property="dob", type="string",format="date"),
     *              @OA\Property(property="gender", type="string"),
     *              @OA\Property(property="bio", type="string"),
     *              @OA\Property(property="education", type="string"),
     *              @OA\Property(property="politics", type="string"),
     *              @OA\Property(property="religion", type="string"),
     *              @OA\Property(property="occupation", type="string"),
     *              @OA\Property(property="sexuality", type="string"),
     *              @OA\Property(property="relationship", type="string"),
     *          ),
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string"),
     *          @OA\Property(property="instagram", type="string"),
     *          @OA\Property(property="snapchat", type="string"),
     *          @OA\Property(property="linkedIn", type="string"),
     *          @OA\Property(property="twitter", type="string"),
     *          @OA\Property(property="resume", type="string"),
     *          @OA\Property(property="coverLetter", type="string"),
     *          @OA\Property(property="sexuality", type="string"),
     *          @OA\Property(property="relationship", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="website", type="string"),
     *          @OA\Property(property="contact", type="string"),
     *          ),
     *          @OA\Property(property="liked", type="boolean"),
     *          @OA\Property(property="likeCount", type="integer"),
     *          @OA\Property(property="visitCount", type="integer"),
     *        )
     *    ),
     * )
     */

    public function getUser(Request $request)
    {
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
     *          @OA\Property(property="location", type="object",
     *              @OA\Property(property="latitude", type="integer"),
     *              @OA\Property(property="longitude", type="integer"),
     *          ),
     *          @OA\Property(property="personalInfo", type="object"),
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string"),
     *          @OA\Property(property="instagram", type="string"),
     *          @OA\Property(property="snapchat", type="string"),
     *          @OA\Property(property="linkedIn", type="string"),
     *          @OA\Property(property="twitter", type="string"),
     *          @OA\Property(property="resume", type="string"),
     *          @OA\Property(property="coverLetter", type="string"),
     *          @OA\Property(property="sexuality", type="string"),
     *          @OA\Property(property="relationship", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="website", type="string"),
     *          @OA\Property(property="contact", type="string"),
     *          ),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Login Payload",
     *    @OA\JsonContent(
     *          @OA\Property(property="id", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="password", type="string"),
     *          @OA\Property(property="phone", type="string"),
     *          @OA\Property(property="firstname", type="string"),
     *          @OA\Property(property="lastname", type="string"),
     *          @OA\Property(property="ghostMode", type="boolean"),
     *          @OA\Property(property="subscription", type="string"),
     *          @OA\Property(property="location", type="object",
     *              @OA\Property(property="latitude", type="integer"),
     *              @OA\Property(property="longitude", type="integer"),
     *          ),
     *          @OA\Property(property="locationTimestamp", type="string",format ="date-time"),
     *          @OA\Property(property="pictureProfile", type="string"),
     *          @OA\Property(property="pictureCover", type="string"),
     *          @OA\Property(property="personalInfo", type="object",
     *              @OA\Property(property="dob", type="string",format="date"),
     *              @OA\Property(property="gender", type="string"),
     *              @OA\Property(property="bio", type="string"),
     *              @OA\Property(property="education", type="string"),
     *              @OA\Property(property="politics", type="string"),
     *              @OA\Property(property="religion", type="string"),
     *              @OA\Property(property="occupation", type="string"),
     *              @OA\Property(property="sexuality", type="string"),
     *              @OA\Property(property="relationship", type="string"),
     *          ),
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string"),
     *          @OA\Property(property="instagram", type="string"),
     *          @OA\Property(property="snapchat", type="string"),
     *          @OA\Property(property="linkedIn", type="string"),
     *          @OA\Property(property="twitter", type="string"),
     *          @OA\Property(property="resume", type="string"),
     *          @OA\Property(property="coverLetter", type="string"),
     *          @OA\Property(property="sexuality", type="string"),
     *          @OA\Property(property="relationship", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="website", type="string"),
     *          @OA\Property(property="contact", type="string"),
     *          ),
     *          @OA\Property(property="liked", type="boolean"),
     *          @OA\Property(property="likeCount", type="integer"),
     *          @OA\Property(property="visitCount", type="integer"),
     *        )
     *    ),
     * )
     */

    public function updateUserProfile(Request $request)
    {
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

    public function removeUserAccount(Request $request)
    {
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
     *          @OA\Property(property="location", type="object",
     *              @OA\Property(property="latitude", type="integer"),
     *              @OA\Property(property="longitude", type="integer"),
     *          ),
     *          @OA\Property(property="personalInfo", type="object"),
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string"),
     *          @OA\Property(property="instagram", type="string"),
     *          @OA\Property(property="snapchat", type="string"),
     *          @OA\Property(property="linkedIn", type="string"),
     *          @OA\Property(property="twitter", type="string"),
     *          @OA\Property(property="resume", type="string"),
     *          @OA\Property(property="coverLetter", type="string"),
     *          @OA\Property(property="sexuality", type="string"),
     *          @OA\Property(property="relationship", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="website", type="string"),
     *          @OA\Property(property="contact", type="string"),
     *          ),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Login Payload",
     *    @OA\JsonContent(
     *          @OA\Property(property="id", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="password", type="string"),
     *          @OA\Property(property="phone", type="string"),
     *          @OA\Property(property="firstname", type="string"),
     *          @OA\Property(property="lastname", type="string"),
     *          @OA\Property(property="ghostMode", type="boolean"),
     *          @OA\Property(property="subscription", type="string"),
     *          @OA\Property(property="location", type="object",
     *              @OA\Property(property="latitude", type="integer"),
     *              @OA\Property(property="longitude", type="integer"),
     *          ),
     *          @OA\Property(property="locationTimestamp", type="string",format ="date-time"),
     *          @OA\Property(property="pictureProfile", type="string"),
     *          @OA\Property(property="pictureCover", type="string"),
     *          @OA\Property(property="personalInfo", type="object",
     *              @OA\Property(property="dob", type="string",format="date"),
     *              @OA\Property(property="gender", type="string"),
     *              @OA\Property(property="bio", type="string"),
     *              @OA\Property(property="education", type="string"),
     *              @OA\Property(property="politics", type="string"),
     *              @OA\Property(property="religion", type="string"),
     *              @OA\Property(property="occupation", type="string"),
     *              @OA\Property(property="sexuality", type="string"),
     *              @OA\Property(property="relationship", type="string"),
     *          ),
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string"),
     *          @OA\Property(property="instagram", type="string"),
     *          @OA\Property(property="snapchat", type="string"),
     *          @OA\Property(property="linkedIn", type="string"),
     *          @OA\Property(property="twitter", type="string"),
     *          @OA\Property(property="resume", type="string"),
     *          @OA\Property(property="coverLetter", type="string"),
     *          @OA\Property(property="sexuality", type="string"),
     *          @OA\Property(property="relationship", type="string"),
     *          @OA\Property(property="email", type="string"),
     *          @OA\Property(property="website", type="string"),
     *          @OA\Property(property="contact", type="string"),
     *          ),
     *          @OA\Property(property="liked", type="boolean"),
     *          @OA\Property(property="likeCount", type="integer"),
     *          @OA\Property(property="visitCount", type="integer"),
     *        )
     *    ),
     * )
     */

    public function updateUserLocation(Request $request)
    {
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
     *    @OA\JsonContent(
     *          @OA\Property(property="userId", type="string"),
     *          @OA\Property(property="friendId", type="string"),
     *          @OA\Property(property="status", type="string"),
     *          @OA\Property(property="dateRequested", type="string", format="date"),
     *          @OA\Property(property="dateAccepted", type="string", format="date"),
     *          @OA\Property(property="dateRejected", type="string", format="date"),
     *          @OA\Property(property="dateBlocked", type="string", format="date"),
     *        )
     *     )
     * )
     */

    public function getUserRelationships(Request $request)
    {
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
     *    @OA\JsonContent(
     *          @OA\Property(property="userId", type="string"),
     *          @OA\Property(property="friendId", type="string"),
     *          @OA\Property(property="status", type="string"),
     *          @OA\Property(property="dateRequested", type="string", format="date"),
     *          @OA\Property(property="dateAccepted", type="string", format="date"),
     *          @OA\Property(property="dateRejected", type="string", format="date"),
     *          @OA\Property(property="dateBlocked", type="string", format="date"),
     *        )
     *     )
     * )
     */

    public function getRelationship(Request $request)
    {
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

    public function requestFriend(Request $request)
    {
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
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function updateFriend(Request $request)
    {
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

    public function unblockUser(Request $request)
    {
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

    public function getLikes(Request $request)
    {
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
