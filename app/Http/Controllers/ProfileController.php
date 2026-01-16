<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Exceptions\Exception;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\RelationshipResource;
use App\Http\Resources\ReportResource;
use App\Models\Course;
use App\Models\Friend;
use App\Models\Relationship;
use App\Models\Report;
use App\Models\ProfileLike;
use App\Models\SocialSiteInfo;
use App\Models\LocationInfo;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LambdaNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public static function checkAuth($id)
    {
        $user_id = Auth::user()->id;
        if ($id == $user_id) {
            return true;
        } else {
            return false;
        }
    }

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
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $user = User::Where('id', $id)->first();
            if ($user) {
                return response()->json(new UserResource($user));
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'User is not registered'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

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
     *          @OA\Property(property="ghostMode", type="string",example="false"),
     *          @OA\Property(property="location", type="object",
     *          @OA\Property(property="latitude", type="integer", example="198"),
     *          @OA\Property(property="longitude", type="integer", example="123"),
     *          ),
     *
     *          @OA\Property(property="personalInfo", type="object",
     *          @OA\Property(property="dob", type="string",example="2000/01/01"),
     *          @OA\Property(property="gender", type="string",example="Male"),
     *          @OA\Property(property="bio", type="string",example="No"),
     *          @OA\Property(property="education", type="string",example="No"),
     *          @OA\Property(property="occupation", type="string",example="No"),
     *          @OA\Property(property="politics", type="string",example="No"),
     *          @OA\Property(property="religion", type="string",example="No"),
     *          @OA\Property(property="sexuality", type="string",example="No"),
     *          @OA\Property(property="relationship", type="string",example="No"),
     *          @OA\Property(property="city", type="string",example="No"),
     *          @OA\Property(property="favorite_music", type="string",example="hello"),
     *          @OA\Property(property="favorite_tv", type="string",example=""),
     *          @OA\Property(property="favorite_games", type="string",example=""),
     *          @OA\Property(property="greek_life", type="string",example=""),
     *          @OA\Property(property="studying", type="string",example=""),
     *          @OA\Property(property="club", type="object",
     *          @OA\Property(property="club", type="string",example="Cheerleading"),
     *          @OA\Property(property="jersey_number", type="string",example="23"),)
     *          ),
     *
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string",example="No"),
     *          @OA\Property(property="instagram", type="string",example="No"),
     *          @OA\Property(property="snapchat", type="string",example="No"),
     *          @OA\Property(property="vsco", type="string",example="No"),
     *          @OA\Property(property="tiktok", type="string",example="No"),
     *          @OA\Property(property="twitter", type="string",example="No"),
     *          @OA\Property(property="resume", type="string",example="No"),
     *          @OA\Property(property="coverLetter", type="string",example="No"),
     *          @OA\Property(property="email", type="string",example="No"),
     *          @OA\Property(property="website", type="string",example="No"),
     *          @OA\Property(property="contact", type="string",example="No"),
     *          )
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
        \Log::info('updateUserProfile payload', $request->all());
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $user = User::Where('id', $id)->first();
                $userLocationInfo = LocationInfo::Where('user_id', $id)->first();

                if ($user) {
                    $time_zone = env('TIME_ZONE');
                    $actionTime = Carbon::now($time_zone)->format('Y-m-d H:i:s');

                    $dataLocation['latitude'] = !empty($request->location['latitude']) ? (float)($request->location['latitude']) : 0;
                    $dataLocation['longitude'] = !empty($request->location['longitude']) ? (float)($request->location['longitude']) : 0;

                    if ($userLocationInfo) {
                        $userLocationInfo->update([
                            'latitude' => $dataLocation['latitude'],
                            'longitude' => $dataLocation['longitude']
                        ]);
                    } else {
                        $userLocationInfo = LocationInfo::create([
                            'user_id' => $id,
                            'latitude' => $dataLocation['latitude'],
                            'longitude' => $dataLocation['longitude']
                        ]);
                    }

                    $dataSocial['facebook'] = !empty($request->socialInfo['facebook']) ? $request->socialInfo['facebook'] : '';
                    $dataSocial['instagram'] = !empty($request->socialInfo['instagram']) ? $request->socialInfo['instagram'] : '';
                    $dataSocial['snapchat'] = !empty($request->socialInfo['snapchat']) ? $request->socialInfo['snapchat'] : '';
                    $dataSocial['vsco'] = !empty($request->socialInfo['vsco']) ? $request->socialInfo['vsco'] : '';
                    $dataSocial['tiktok'] = !empty($request->socialInfo['tiktok']) ? $request->socialInfo['tiktok'] : '';
                    $dataSocial['twitter'] = !empty($request->socialInfo['twitter']) ? $request->socialInfo['twitter'] : '';
                    $dataSocial['resume'] = !empty($request->socialInfo['resume']) ? $request->socialInfo['resume'] : '';
                    $dataSocial['coverLetter'] = !empty($request->socialInfo['coverLetter']) ? $request->socialInfo['coverLetter'] : '';
                    $dataSocial['email'] = !empty($request->socialInfo['email']) ? $request->socialInfo['email'] : '';
                    $dataSocial['website'] = !empty($request->socialInfo['website']) ? $request->socialInfo['website'] : '';
                    $dataSocial['contact'] = !empty($request->socialInfo['contact']) ? $request->socialInfo['contact'] : '';

                    if (isset($dataSocial['facebook'])) {
                        $name = "facebook";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['facebook']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['facebook']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['instagram'])) {
                        $name = "instagram";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['instagram']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['instagram']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['snapchat'])) {
                        $name = "snapchat";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['snapchat']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['snapchat']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['vsco'])) {
                        $name = "vsco";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['vsco']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['vsco']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['tiktok'])) {
                        $name = "tiktok";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['tiktok']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['tiktok']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['twitter'])) {
                        $name = "twitter";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['twitter']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['twitter']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['resume'])) {
                        $name = "resume";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['resume']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['resume']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['coverLetter'])) {
                        $name = "coverLetter";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['coverLetter']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['coverLetter']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['email'])) {
                        $name = "email";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['email']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['email']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['website'])) {
                        $name = "website";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['website']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['website']
                                ]
                            );
                        }
                    }

                    if (isset($dataSocial['contact'])) {
                        $name = "contact";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['contact']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['contact']
                                ]
                            );
                        }
                    }

                    if (!empty($request->personalInfo['dob'])) {
                        $dob_txt = $request->personalInfo['dob'];
                        $dob_obj = Carbon::parse($dob_txt);
                        if (!empty($dob_obj)) {
                            $data['dob'] =  $dob_obj->format("Y-m-d");
                        }
                    } else {
                        $data['dob'] = $user->dob;
                    }
                    $data['locationTimestamp'] = $actionTime;
                    // Accept both snake_case and camelCase from clients
                    $pi = $request->personalInfo ?? [];
                    $data['gender'] = $pi['gender'] ?? '';
                    $data['bio'] = $pi['bio'] ?? '';
                    $data['education'] = $pi['education'] ?? '';
                    $data['occupation'] = $pi['occupation'] ?? '';
                    $data['politics'] = $pi['politics'] ?? '';
                    $data['religion'] = $pi['religion'] ?? '';
                    $data['sexuality'] = $pi['sexuality'] ?? '';
                    $data['relationship'] = $pi['relationship'] ?? '';
                    $data['city'] = $pi['city'] ?? '';
                    // Additional camelCase -> snake_case mappings
                    $data['hometown'] = $pi['hometown'] ?? '';
                    $data['high_school'] = $pi['highSchool'] ?? $pi['high_school'] ?? '';
                    // Track whether client explicitly sent this field to avoid overwriting with stale values
                    $classYearProvided = array_key_exists('classYear', $pi) || array_key_exists('class_year', $pi);
                    if ($classYearProvided) {
                        $data['class_year'] = $pi['classYear'] ?? $pi['class_year'];
                    }
                    $data['campus'] = $pi['campus'] ?? '';
                    $data['career_aspirations'] = $pi['careerAspirations'] ?? $pi['career_aspirations'] ?? $user->career_aspirations;
                    $data['postgraduate'] = $pi['postgraduate'] ?? $user->postgraduate;
                    $data['postgraduate_plans'] = $pi['postgraduatePlans'] ?? $pi['postgraduate_plans'] ?? $user->postgraduate_plans;
                    $data['favorite_music'] = $pi['favorite_music'] ?? ($pi['favoriteMusic'] ?? '');
                    $data['favorite_tv'] = $pi['favorite_tv'] ?? ($pi['favoriteTV'] ?? '');
                    $data['favorite_games'] = $pi['favorite_games'] ?? ($pi['favoriteGame'] ?? $pi['favoriteGames'] ?? '');
                    $data['greek_life'] = $pi['greek_life'] ?? ($pi['greekLife'] ?? '');
                    $data['studying'] = $pi['studying'] ?? '';
                    $club = $pi['club'] ?? [];
                    $data['club'] = $club['club'] ?? '';
                    $data['jersey_number'] = $club['jersey_number'] ?? ($club['number'] ?? '');
                    $ghost_mode_flag = 0;
                    if (!empty($request->ghostMode)) {
                        $ghost_mode_flag = ($request->ghostMode == 'true') ? 1 : 0;
                    }

                    DB::beginTransaction();
                    try {
                        // update best friends
                        if (isset($request->bestFriends)) {
                            $this->updateBestFriends($request, $user);
                        }

                        // update courses
                        if (isset($request->courses)) {
                            $this->updateCourses($request, $user);
                        }

                        $updateData = [
                            'dob' => $data['dob'],
                            'locationTimestamp' => $data['locationTimestamp'],
                            'gender' => $data['gender'],
                            'bio' => $data['bio'],
                            'education' => $data['education'],
                            'occupation' => $data['occupation'],
                            'politics' => $data['politics'],
                            'religion' => $data['religion'],
                            'sexuality' => $data['sexuality'],
                            'relationship' => $data['relationship'],
                            'city' => $data['city'],
                            'favorite_music' => $data['favorite_music'],
                            'favorite_tv' => $data['favorite_tv'],
                            'favorite_games' => $data['favorite_games'],
                            'greek_life' => $data['greek_life'],
                            'studying' => $data['studying'],
                            'club' => $data['club'],
                            'jersey_number' => $data['jersey_number'],
                            'ghost_mode_flag' => $ghost_mode_flag,
                            // extra profile fields
                            'hometown' => $data['hometown'],
                            'high_school' => $data['high_school'],
                            'campus' => $data['campus'],
                            'career_aspirations' => $data['career_aspirations'],
                            'postgraduate' => $data['postgraduate'],
                            'postgraduate_plans' => $data['postgraduate_plans'],
                        ];
                        if ($classYearProvided) {
                            $updateData['class_year'] = $data['class_year'];
                        }
                        $user->update($updateData);
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollBack();
                    }

                    return response()->json(new UserResource($user));
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not registered'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Delete(
     * path="/profile/{id}",
     * summary="Removes a user's account",
     * description="Removes a user's account",
     * operationId="removeUserAccount",
     * security={{"bearerAuth":{}}},
     * tags={"Authentication"},
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
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $user = User::whereid($id)->first();
                if ($user) {
                    $user->delete($user);
                    $userLocation = LocationInfo::Where('user_id', $id)->delete();
                    $userSocial = SocialSiteInfo::Where('user_id', $id)->delete();
                    return response()->json([
                        'message' => Exception::DELETE_SUCCESS,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not registered'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

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
     *          @OA\Property(property="ghostMode", type="string",example="false"),
     *          @OA\Property(property="location", type="object",
     *          @OA\Property(property="latitude", type="integer", example="198"),
     *          @OA\Property(property="longitude", type="integer", example="123"),
     *          ),
     *
     *          @OA\Property(property="personalInfo", type="object",
     *          @OA\Property(property="dob", type="string",example="2000/01/01"),
     *          @OA\Property(property="gender", type="string",example="Male"),
     *          @OA\Property(property="bio", type="string",example="No"),
     *          @OA\Property(property="education", type="string",example="No"),
     *          @OA\Property(property="occupation", type="string",example="No"),
     *          @OA\Property(property="politics", type="string",example="No"),
     *          @OA\Property(property="religion", type="string",example="No"),
     *          @OA\Property(property="sexuality", type="string",example="No"),
     *          @OA\Property(property="relationship", type="string",example="No"),
     *          @OA\Property(property="city", type="string",example="No"),
     *          ),
     *
     *          @OA\Property(property="socialInfo", type="object",
     *          @OA\Property(property="facebook", type="string",example="No"),
     *          @OA\Property(property="instagram", type="string",example="No"),
     *          @OA\Property(property="snapchat", type="string",example="No"),
     *          @OA\Property(property="vsco", type="string",example="No"),
     *          @OA\Property(property="tiktok", type="string",example="No"),
     *          @OA\Property(property="twitter", type="string",example="No"),
     *          @OA\Property(property="resume", type="string",example="No"),
     *          @OA\Property(property="coverLetter", type="string",example="No"),
     *          @OA\Property(property="email", type="string",example="No"),
     *          @OA\Property(property="website", type="string",example="No"),
     *          @OA\Property(property="contact", type="string",example="No"),
     *          )
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
        \Log::info('updateUserLocation payload', $request->all());
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $user = User::Where('id', $id)->first();
                $userLocationInfo = LocationInfo::Where('user_id', $id)->first();

                if ($user) {
                    $time_zone = env('TIME_ZONE');
                    $actionTime = Carbon::now($time_zone)->format('Y-m-d H:i:s');

                    $dataLocation['latitude'] = !empty($request->location['latitude']) ? (float)($request->location['latitude']) : 0;
                    $dataLocation['longitude'] = !empty($request->location['longitude']) ? (float)($request->location['longitude']) : 0;

                    if ($userLocationInfo) {
                        $userLocationInfo->update([
                            'latitude' => $dataLocation['latitude'],
                            'longitude' => $dataLocation['longitude']
                        ]);
                    } else {
                        $userLocationInfo = LocationInfo::create([
                            'user_id' => $id,
                            'latitude' => $dataLocation['latitude'],
                            'longitude' => $dataLocation['longitude']
                        ]);
                    }

                    $dataSocial['facebook'] = !empty($request->socialInfo['facebook']) ? $request->socialInfo['facebook'] : '';
                    $dataSocial['instagram'] = !empty($request->socialInfo['instagram']) ? $request->socialInfo['instagram'] : '';
                    $dataSocial['snapchat'] = !empty($request->socialInfo['snapchat']) ? $request->socialInfo['snapchat'] : '';
                    $dataSocial['vsco'] = !empty($request->socialInfo['vsco']) ? $request->socialInfo['vsco'] : '';
                    $dataSocial['tiktok'] = !empty($request->socialInfo['tiktok']) ? $request->socialInfo['tiktok'] : '';
                    $dataSocial['twitter'] = !empty($request->socialInfo['twitter']) ? $request->socialInfo['twitter'] : '';
                    $dataSocial['resume'] = !empty($request->socialInfo['resume']) ? $request->socialInfo['resume'] : '';
                    $dataSocial['coverLetter'] = !empty($request->socialInfo['coverLetter']) ? $request->socialInfo['coverLetter'] : '';
                    $dataSocial['email'] = !empty($request->socialInfo['email']) ? $request->socialInfo['email'] : '';
                    $dataSocial['website'] = !empty($request->socialInfo['website']) ? $request->socialInfo['website'] : '';
                    $dataSocial['contact'] = !empty($request->socialInfo['contact']) ? $request->socialInfo['contact'] : '';

                    if ($dataSocial['facebook']) {
                        $name = "facebook";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['facebook']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['facebook']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['instagram']) {
                        $name = "instagram";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['instagram']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['instagram']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['snapchat']) {
                        $name = "snapchat";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['snapchat']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['snapchat']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['vsco']) {
                        $name = "vsco";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['vsco']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['vsco']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['tiktok']) {
                        $name = "tiktok";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['tiktok']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['tiktok']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['twitter']) {
                        $name = "twitter";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['twitter']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['twitter']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['resume']) {
                        $name = "resume";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['resume']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['resume']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['coverLetter']) {
                        $name = "coverLetter";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['coverLetter']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['coverLetter']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['email']) {
                        $name = "email";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['email']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['email']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['website']) {
                        $name = "website";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['website']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['website']
                                ]
                            );
                        }
                    }

                    if ($dataSocial['contact']) {
                        $name = "contact";
                        $social_name = DB::table('social_sites')
                            ->select("*")
                            ->where('social_sites.social_site_name', 'LIKE', '%' . $name . '%')
                            ->first();

                        $social_id = $social_name->id;

                        $userSocialInfo = SocialSiteInfo::select('*')
                            ->where('user_id', '=', $id)
                            ->where('socila_site_row_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['contact']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'socila_site_row_id' => $social_name->id,
                                    'social_siteUsername' => $dataSocial['contact']
                                ]
                            );
                        }
                    }


                    if (!empty($request->personalInfo['dob'])) {
                        $dob_txt = $request->personalInfo['dob'];
                        $dob_obj = Carbon::parse($dob_txt);
                        if (!empty($dob_obj)) {
                            $data['dob'] =  $dob_obj->format("Y-m-d");
                        }
                    } else {
                        $data['dob'] = $user->dob;
                    }

                    $data['locationTimestamp'] = $actionTime;
                    $pi2 = $request->personalInfo ?? [];
                    $data['gender'] = $pi2['gender'] ?? '';
                    $data['bio'] = $pi2['bio'] ?? '';
                    $data['education'] = $pi2['education'] ?? '';
                    $data['occupation'] = $pi2['occupation'] ?? '';
                    $data['politics'] = $pi2['politics'] ?? '';
                    $data['religion'] = $pi2['religion'] ?? '';
                    $data['sexuality'] = $pi2['sexuality'] ?? '';
                    $data['relationship'] = $pi2['relationship'] ?? '';
                    $data['city'] = $pi2['city'] ?? '';
                    $ghost_mode_flag = 0;
                    if (!empty($request->ghostMode)) {
                        $ghost_mode_flag = ($request->ghostMode == 'true') ? 1 : 0;
                    }
                    $user->update([
                        'dob' => $data['dob'],
                        'locationTimestamp' => $data['locationTimestamp'],
                        'gender' => $data['gender'],
                        'bio' => $data['bio'],
                        'education' => $data['education'],
                        'occupation' => $data['occupation'],
                        'politics' => $data['politics'],
                        'religion' => $data['religion'],
                        'sexuality' => $data['sexuality'],
                        'relationship' => $data['relationship'],
                        'city' => $data['city'],
                        'ghost_mode_flag' => $ghost_mode_flag,
                    ]);
                    return response()->json(new UserResource($user));
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not registered'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

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
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $rela_list = Relationship::where('user_id', '=', $id)->get();
                if (count($rela_list) > 0) {
                    return response()->json(RelationshipResource::collection($rela_list));
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not relationship'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Get(
     * path="/profile/{id}/relationships/{friendId}",
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
        if (!$userId) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($userId)) {
                $rela = Relationship::where('user_id', '=', $userId)
                    ->where('friend_id', '=', $friendId)
                    ->first();
                if ($rela) {
                    return response()->json(new RelationshipResource($rela));
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not relationships'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Put(
     * path="/profile/{id}/relationships/{friendId}",
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

    public function requestFriend($userId, $friendId)
    {
        if (!$userId) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($userId)) {
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
                        'message' => 'success',
                        'details' => 'Requested'
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not relationships'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Post(
     * path="/profile/{id}/relationships/{friendId}",
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
        if (!$userId) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($userId)) {
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
                        'message' => 'success',
                        'details' => 'Updated relationship'
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not requested relationships'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Delete(
     * path="/profile/{id}/relationships/{friendId}",
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
        if (!$userId) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($userId)) {
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
                        'message' => 'success',
                        'details' => 'Deleted relationship'
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is blocked. Unblock to remove'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

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
        if (!$id) {
            return 0;
        }
        // Anyone authenticated can view counts; do not restrict to self
        $count = ProfileLike::where('profile_id', $id)->count();
        return $count;
    }

    // GET /profile/{id}/likes/received
    public function likesReceived(Request $request, $id)
    {
        $page = max(1, (int)$request->query('cursor', 1));
        $perPage = max(1, min(100, (int)$request->query('limit', 20)));
        $p = DB::table('profile_likes')
            ->join('users', 'profile_likes.user_id', '=', 'users.id')
            ->where('profile_likes.profile_id', $id)
            ->orderBy('profile_likes.id', 'desc')
            ->selectRaw('CAST(users.id AS CHAR) as id, users.first_name as firstName, users.last_name as lastName, users.profile_pic as avatarUrl')
            ->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'data' => $p->items(),
            'nextCursor' => $p->hasMorePages() ? (string)($p->currentPage() + 1) : null,
        ]);
    }

    // GET /profile/{id}/likes/sent
    public function likesSent(Request $request, $id)
    {
        $page = max(1, (int)$request->query('cursor', 1));
        $perPage = max(1, min(100, (int)$request->query('limit', 20)));
        $p = DB::table('profile_likes')
            ->join('users', 'profile_likes.profile_id', '=', 'users.id')
            ->where('profile_likes.user_id', $id)
            ->orderBy('profile_likes.id', 'desc')
            ->selectRaw('CAST(users.id AS CHAR) as id, users.first_name as firstName, users.last_name as lastName, users.profile_pic as avatarUrl')
            ->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'data' => $p->items(),
            'nextCursor' => $p->hasMorePages() ? (string)($p->currentPage() + 1) : null,
        ]);
    }

    /**
     * @OA\Post(
     * path="/profile/{id}/likes",
     * summary="Like user profile",
     * description="Like user profile",
     * operationId="likeProfile",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="profileLikeId",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="query",
     *    required=true,
     * ),
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

    public function likeProfile(Request $request, $id)
    {
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $profileLikeId = $request->profileLikeId ?? $request->query('profileLikedId');
                \Log::info('likeProfile request', [
                    'authId' => (string)$id,
                    'profileLikedId' => (string)($profileLikeId ?? ''),
                    'LAMBDA_DEBUG' => env('LAMBDA_DEBUG'),
                    'lambda_function' => env('LAMBDA_PROFILE_LIKED_FUNCTION')
                ]);
                if (!$profileLikeId) {
                    return response()->json(['message' => 'fail','details' => 'profileLikedId missing'], Response::HTTP_BAD_REQUEST);
                }
                // Idempotent insert
                $created = false;
                try {
                    ProfileLike::create(['user_id' => $id, 'profile_id' => $profileLikeId]);
                    $created = true;
                } catch (\Throwable $e) {
                    // duplicate -> already liked
                    \Log::info('likeProfile duplicate like detected', [
                        'authId' => (string)$id,
                        'profileLikedId' => (string)$profileLikeId,
                        'error' => $e->getMessage(),
                    ]);
                }
                \Log::info('likeProfile state', ['created' => $created, 'self_like' => $id === $profileLikeId]);
                // Email notification policy: allow up to 2 notifications per A->B lifetime, suppress for self-like
                if ($created && $id !== $profileLikeId) {
                    $event = DB::table('profile_like_events')->where('user_id',$id)->where('profile_id',$profileLikeId)->first();
                    $notifyCount = $event->notify_count ?? 0;
                    if ($notifyCount < 2) {
                        if (!$event) {
                            DB::table('profile_like_events')->insert([
                                'user_id' => $id,
                                'profile_id' => $profileLikeId,
                                'notified_at' => now(),
                                'notify_count' => 1,
                            ]);
                        } else {
                            DB::table('profile_like_events')
                                ->where('user_id',$id)->where('profile_id',$profileLikeId)
                                ->update(['notified_at' => now(), 'notify_count' => $notifyCount + 1]);
                        }
                    // Include recipient email and sender name so Lambda doesn't need DB
                    $recipient = DB::table('users')->select('email','first_name','last_name')->where('id', $profileLikeId)->first();
                    $sender = DB::table('users')->select('first_name','last_name')->where('id', $id)->first();
                    $additional = [];
                    if ($recipient) {
                        $additional['recipientEmail'] = $recipient->email ?? null;
                        $additional['recipientFirstName'] = $recipient->first_name ?? null;
                        $additional['recipientLastName'] = $recipient->last_name ?? null;
                    }
                    if ($sender) {
                        $additional['senderFirstName'] = $sender->first_name ?? null;
                        $additional['senderLastName'] = $sender->last_name ?? null;
                    }
                    \Log::info('likeProfile invoking lambda', [
                        'recipientUserId' => (int)$profileLikeId,
                        'senderUserId' => (int)$id,
                        'hasRecipientEmail' => !empty($additional['recipientEmail']),
                    ]);
                    try { app(LambdaNotificationService::class)->notifyProfileLiked((int)$profileLikeId, (int)$id, $additional); } catch (\Throwable $e) { }
                    }
                }
                return $created ? response()->noContent(Response::HTTP_CREATED) : response()->noContent(Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Delete(
     * path="/profile/{id}/likes",
     * summary="Unlike user profile",
     * description="Unlike user profile",
     * operationId="unlikeProfile",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="profileLikeId",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="query",
     *    required=true,
     * ),
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

    public function unlikeProfile(Request $request, $id)
    {
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $profileLikeId = $request->profileLikeId ?? $request->query('profileLikedId');
                if (!$profileLikeId) {
                    return response()->json(['message' => 'fail','details' => 'profileLikedId missing'], Response::HTTP_BAD_REQUEST);
                }
                ProfileLike::where('user_id', $id)->where('profile_id', $profileLikeId)->delete();
                return response()->noContent();
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

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
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $userReport = Report::where('user_id', $id)->get();
                if (count($userReport) > 0) {
                    return response()->json(ReportResource::collection($userReport));
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'User is not reported'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

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
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                    'message' => 'success',
                    'details' => 'Updated report'
                ], Response::HTTP_OK);
            } else if ($noExistReport) {
                $noExistReport = Report::create(
                    [
                        'id' => $id,
                        'user_id' => "$user_id",
                        'info' => $request->info,
                        'dateCreated' => $createdTime
                    ]
                );
                return response()->json([
                    'message' => 'success',
                    'details' => 'Updated report'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'User is not reported'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Post(
     * path="/profile/{id}/upload",
     * summary="Upload user avatar",
     * description="Upload user avatar",
     * operationId="uploadAvatar",
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
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             @OA\Property(
     *                 description="Upload avatar",
     *                 property="avatar",
     *                 type="string",
     *                 format="binary",
     *             ),
     *         )
     *     )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function uploadAvatar(Request $request, $id)
    {
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $user = User::where('id', $id)->first();
                $avatar = $request->file('avatar');
                if ($avatar != null) {
                    $rules = [
                        'avatar' => 'mimes:png,jpg,jpeg',
                    ];

                    $validation = Validator::make($request->all(), $rules);

                    if ($validation->fails()) {
                        return response([
                            "message" => 'fail',
                            "details" => $validation->errors()->all()[0],
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    $avatar_path = $avatar->store('profile', 's3');
                    $avatar_path = Storage::disk('s3')->url($avatar_path);
                    $user->update(['profile_pic' => $avatar_path]);
                    return response()->json([
                        'message' => 'success',
                        'details' => 'Upload avatar success',
                        'url' => $avatar_path,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'Avatar is null'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @OA\Post(
     * path="/profile/{id}/coverImage",
     * summary="Upload user cover image",
     * description="Upload user cover image",
     * operationId="uploadCoverImage",
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
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             @OA\Property(
     *                 description="Upload cover image",
     *                 property="coverImage",
     *                 type="string",
     *                 format="binary",
     *             ),
     *         )
     *     )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *   )
     * )
     */

    public function uploadCoverImage(Request $request, $id)
    {
        if (!$id) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            if ($this->checkAuth($id)) {
                $user = User::where('id', $id)->first();
                $avatar = $request->file('coverImage');

                if ($avatar != null) {
                    $rules = [
                        'coverImage' => 'mimes:png,jpg,jpeg',
                    ];

                    $validation = Validator::make($request->all(), $rules);

                    if ($validation->fails()) {
                        return response([
                            "message" => 'fail',
                            "details" => $validation->errors()->all()[0],
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    $ci_path = $avatar->store('banner', 's3');
                    $ci_path = Storage::disk('s3')->url($ci_path);
                    $user->update(['cover_image' => $ci_path]);
                    return response()->json([
                        'message' => 'success',
                        'details' => 'Upload cover image success',
                        'url' => $ci_path,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'Cover image is null'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'Authorization'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    private function updateBestFriends($request, $user)
    {
        Validator::make($request->all(), [
            'bestFriends' => 'array|max:5',
            'bestFriends.*.first_name' => 'string|max:50',
            'bestFriends.*.last_name' => 'string|max:50',
        ])->validate();

        // delete old records
        $count = Friend::where('user_id', $user->id)->delete();

        // create new records
        $now = now();
        $friends = array_map(function($item) use ($user, $now) {
            return [
                'first_name' => $item['first_name'],
                'last_name' => $item['last_name'],
                'user_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }, $request->bestFriends);

        Friend::insert($friends);
    }

    private function updateCourses($request, $user)
    {
        Validator::make($request->all(), [
            'courses' => 'array|max:6',
            'courses.*.name' => 'string|max:50',
        ])->validate();

        // delete old records
        Course::where('user_id', $user->id)->delete();

        // create new records
        $now = now();
        $courses = array_map(function($item) use ($user, $now) {
            return [
                'name' => $item['name'],
                'user_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }, $request->courses);

        Course::insert($courses);
    }
}
