<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search By Distance
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/search/distance",
     * summary="Searches users by distance from given point",
     * description="Searches users by distance from given point",
     * operationId="searchByDistance",
     * tags={"Search"},
     * @OA\Parameter(
     *    name="position",
     *    @OA\Schema(
     *      type="object",
     *    ),
     *    in="query",
     *    required=true,
     *    example={"latitude":0, "longitude":0},
     * ),
     * @OA\Parameter(
     *    name="minDistance",
     *    @OA\Schema(
     *      type="object",
     *    ),
     *    in="query",
     *    required=true,
     *    example={"type":"feet", "amount":0},
     * ),
     * @OA\Parameter(
     *    name="maxDistance",
     *    @OA\Schema(
     *      type="object",
     *    ),
     *    in="query",
     *    required=true,
     *    example={"type":"feet", "amount":0},
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

    public function searchByDistance(Request $request)
    {
    }

    /**
     * Search By Name
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/search/name",
     * summary="Searches users by name",
     * description="Searches users by name",
     * operationId="searchByName",
     * tags={"Search"},
     * @OA\Parameter(
     *    name="name",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="query",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="name", type="string"),
     *        )
     *     )
     * )
     */

    public function searchByName(Request $request)
    {
    }
}
