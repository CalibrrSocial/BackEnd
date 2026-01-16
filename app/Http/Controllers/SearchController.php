<?php

namespace App\Http\Controllers;

use App\Http\Resources\DistanceResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Exceptions\Exception;
use App\Http\Requests\User\SearchRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
     * security={{"bearerAuth":{}}},
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
     *     )
     * )
     */

    public function searchByDistance(Request $request)
    {
        $lat = $request->position['latitude'];
        $lon = $request->position['longitude'];
        // $min_amount = $request->minDistance['amount'];
        $max_amount = $request->maxDistance['amount'];
        $my_id = Auth::user()->id;

        $type = $request->maxDistance['type'];
        $type_value = 6371000 * 0.000621371;
        // switch ($type) {
        //     case "Feet":
        //         $type_value = 6371000 * 3.2808399;
        //         break;
        //     case "Meters":
        //         $type_value = 6371000;
        //         break;
        //     case "Miles":
        //         $type_value = 6371000 * 0.000621371;
        //         break;
        //     default:
        //         $type_value = 6371;
        // }
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
            // ->having("distance", ">=", $min_amount)
            ->orderBy("distance")
            ->get();
        if (count($result) > 0) {
            for ($i = 0; $i < count($result); $i++) {
                unset($result[$i]->distance);
                if ($result[$i]->user_id == $my_id) {
                    $result[$i]->user_id = 0;
                }
            }
            $arr_user = json_decode(json_encode($result), true);
            $arr_users = [];
            foreach ($arr_user as $user) {
                $arr_use = array_values($user);
                $arr_users = array_merge($arr_users, $arr_use);
            }
            $tempStr = implode(',', $arr_users);
            $user_dob_list = DB::table('users')
                ->select('*')
                ->where('ghost_mode_flag', false)
                ->whereIn('id', $arr_user)
                ->orderByRaw(DB::raw("FIELD(id, $tempStr)"))
                ->get();
            if(count($user_dob_list) > 0){
                $now = Carbon::now();
                $hide_user = [];
                foreach($user_dob_list as $user_dob){
                    $year = Carbon::parse($user_dob->dob)->age;
                    if($year >= 25){
                        $hour = $now->diffInHours($user_dob->updated_at);
                        if($hour >= 1){
                            $hide_user[] = $user_dob->id;
                        }
                    }
                }
            }
            $users = DB::table('users')
                ->select('*')
                ->where('ghost_mode_flag', false)
                ->whereIn('id', $arr_user)
                ->whereNotIn('id', $hide_user)
                ->orderByRaw(DB::raw("FIELD(id, $tempStr)"))
                ->get();

            return response()->json(UserResource::collection($users));
        } else {
            return response()->json([
                'message' => 'fail',
                'details' => 'User not found'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Post(
     * path="/search/name",
     * summary="Searches users by name",
     * description="Searches users by name (Search at least 3 letters)",
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
     *     )
     * )
     */

    public function searchByName(SearchRequest $request)
    {
        $my_id = Auth::user()->id;
        $data = $request->all();

        if (strlen($request->name) < 3) {
            return response()->json([
                'message' => 'fail',
                'details' => 'User not found'
            ], Response::HTTP_BAD_REQUEST);
        } else {
            $query = User::select("*")->where('ghost_mode_flag', false);

            $query = $query->where(function ($q) use ($request) {
                $q = $q->orWhere(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%" . $request->name . "%");

                if ($request->search_in_course) {
                    $q = $q->orWhereHas('courses', function (Builder $qr) use ($request) {
                        return $qr->where('name', 'LIKE', "%" . $request->name . "%");
                    });
                }

                if ($request->search_in_studying) {
                    $q = $q->orWhere('studying', 'LIKE', "%" . $request->name . "%");
                }

                return $q;
            });

            $user = $query->get();

            for ($i = 0; $i < count($user); $i++) {
                if ($user[$i]->id == $my_id) {
                    unset($user[$i]);
                }
            }

            if (count($user) > 0) {
                return response()->json(UserResource::collection($user));
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'User not found'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }
}
