<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Requests\User\SearchRequest;
use App\Http\Resources\UserSearchResource;
use App\Models\Course;
use Carbon\Carbon;
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
        $authUser = Auth::user();
        $fields = ['users.id', 'first_name', 'last_name', 'profile_pic', 'location', 'city', 'dob', 'studying', 'education', 'club', 'jersey_number', 'greek_life'];
        $lat = $request->position['latitude'];
        $lon = $request->position['longitude'];
        // $min_amount = $request->minDistance['amount'];
        $max_amount = $request->maxDistance['amount'];
        $my_id = Auth::user()->id;
        $hide_user = [];

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
            $userQuery = DB::table('users')->select('*');
            // Guard for schema mismatch on ghost_mode_flag
            if (\Schema::hasColumn('users', 'ghost_mode_flag')) {
                $userQuery = $userQuery->where('ghost_mode_flag', false);
            }
            $user_dob_list = $userQuery
                ->whereIn('id', $arr_user)
                ->orderByRaw(DB::raw("FIELD(id, $tempStr)"))
                ->get();
            if(count($user_dob_list) > 0){
                $now = Carbon::now();
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

            // get auth course names
            $courseNames = Course::where('user_id', $authUser->id)->get();
            $courseNames = $courseNames->map(function ($i) {
                return $i->name;
            });

            $users = User::select($fields)
                ->with(['courses' => function ($q) use ($courseNames) {
                    return $q->whereIn('name', $courseNames);
                }])
                ->when(\Schema::hasColumn('users', 'ghost_mode_flag'), function ($q) {
                    return $q->where('ghost_mode_flag', false);
                })
                ->whereIn('id', $arr_user)
                ->whereNotIn('id', $hide_user)
                ->orderByRaw(DB::raw("FIELD(id, $tempStr)"))
                ->get();


            return response()->json(UserSearchResource::collection($users));
        } else {
            return response()->json([]);
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
        $user = Auth::user();

        $fields = ['users.id', 'first_name', 'last_name', 'profile_pic', 'location', 'city', 'dob', 'studying', 'education', 'club', 'jersey_number', 'greek_life'];

        $courseNames = Course::where('user_id', $user->id)->get();
        $courseNames = $courseNames->map(function ($i) {
            return $i->name;
        });

        if (!$request->search_in_course && !$request->search_in_studying) {
            $users = User::select($fields)->with(['courses' => function ($q) use ($courseNames) {
                return $q->whereIn('courses.name', $courseNames);
            }])
            ->when(\Schema::hasColumn('users', 'ghost_mode_flag'), function ($q) {
                return $q->where('ghost_mode_flag', false);
            })
            ->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%" . $request->name . "%")
            ->get();

            return response()->json(UserSearchResource::collection($users));
        }

        $query = '';

        if ($request->search_in_course) {
            $query = DB::table('users')->select('users.*')
                ->when(\Schema::hasColumn('users', 'ghost_mode_flag'), function ($q) {
                    return $q->where('ghost_mode_flag', false);
                })
                ->join('courses', function($join) use ($user) {
                return $join->on('users.id', '=', 'courses.user_id')->whereIn('courses.name', function ($q) use ($user) {
                    return $q->select('courses.name')->from('users')->where('users.id', $user->id)
                        ->join('courses', 'users.id', '=', 'courses.user_id');
                });
            });
        }

        if ($request->search_in_studying && $user->studying) {
            $secondQuery = DB::table('users')->select('users.*')
                ->when(\Schema::hasColumn('users', 'ghost_mode_flag'), function ($q) {
                    return $q->where('ghost_mode_flag', false);
                })
                ->where('studying', $user->studying);

            $query = $query ? $query->union($secondQuery) : $secondQuery;
        }

        if(!$query) {
            return response()->json([]);
        }



        $users = User::select($fields)->with(['courses' => function ($qr) use ($courseNames) {
            return $qr->whereIn('courses.name', $courseNames);
        }])->whereIn('users.id', function ($q) use ($query) {
            return $q->select('accounts.id')->from($query, 'accounts');
        })->where('users.id', '!=', $user->id)->get();

        return response()->json(UserSearchResource::collection($users));
    }
}
