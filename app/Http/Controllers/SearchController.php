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
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="name", type="string"),
     *        )
     *     )
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
