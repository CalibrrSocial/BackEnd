<?php

namespace App\Http\Controllers;

use App\Http\Resources\DistanceResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Exceptions\Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
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
     *    example={"position" = {"latitude":0, "longitude":0}},
     * ),
     * @OA\Parameter(
     *    name="minDistance",
     *    @OA\Schema(
     *      type="object",
     *    ),
     *    in="query",
     *    required=true,
     *    example={"minDistance" = {"type":"Feet", "amount":0}},
     * ),
     * @OA\Parameter(
     *    name="maxDistance",
     *    @OA\Schema(
     *      type="object",
     *    ),
     *    in="query",
     *    required=true,
     *    example={"maxDistance" = {"type":"Feet", "amount":0}},
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
        $lat = $request->position['latitude'];
        $lon = $request->position['longitude'];
        $min_amount = $request->minDistance['amount'];
        $max_amount = $request->maxDistance['amount'];

        if ($request->maxDistance['type'] != $request->minDistance['type']) {
            return response()->json([
                'status' => 'Error',
                'message' => 'type is not the same'
            ], 500);
        } else {
            $type = $request->maxDistance['type'];
        }

        switch ($type) {
            case "Feet":
                $type_value = 6371000 * 3.2808399;
                break;
            case "Meters":
                $type_value = 6371000;
                break;
            default:
                $type_value = 6371;
        }
        $result = DB::table("location_infos")
            ->select(
                "user_id",
                DB::raw("$type_value * acos(cos(radians(" . $lat . ")) 
                * cos(radians(location_infos.latitude)) 
                * cos(radians(location_infos.longitude) - radians(" . $lon . ")) 
                + sin(radians(" . $lat . ")) 
                * sin(radians(location_infos.latitude))) AS distance")
            )
            ->having("distance", "<=", $max_amount)
            ->having("distance", ">=", $min_amount)
            ->orderBy("distance")
            ->get();
        foreach ($result as $re) {
            unset($re->distance);
        }
        $arr_user = json_decode(json_encode($result), true);

        $arr_users = [];
        foreach ($arr_user as $user) {
            $arr_use = array_values($user);
            $arr_users = array_merge($arr_users, $arr_use);
        }
        $tempStr = implode(',', $arr_users);
        $users = DB::table('users')
            ->select('*')
            ->whereIn('id', $arr_user)
            ->orderByRaw(DB::raw("FIELD(id, $tempStr)"))
            ->get();

        if (count($result) > 0) {
            return response()->json(UserResource::collection($users));
        } else {
            return response()->json([
                'message' => 'Search failed',
                'details' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Post(
     * path="/search/name",
     * summary="Searches users by name",
     * description="Searches users by name",
     * operationId="searchByName",
     * security={{"bearerAuth":{}}},
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
        $data = $request->all();
        $name = $data['name'];

        $user = User::select("*")
            ->Where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%" . $name . "%")
            ->orWhere(DB::raw("concat(first_name, last_name)"), 'LIKE', "%" . $name . "%")
            ->get();

        if (count($user) > 0) {
            return response()->json(UserResource::collection($user));
        } else {
            return response()->json([
                'message' => 'Search failed',
                'details' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
