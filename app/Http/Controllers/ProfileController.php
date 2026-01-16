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
use App\Models\UserBlock;
use App\Models\SocialSiteInfo;
use App\Models\LocationInfo;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LambdaNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

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

                    $dataSocial['facebook'] = $request->input('socialInfo.facebook','');
                    $dataSocial['instagram'] = $request->input('socialInfo.instagram','');
                    $dataSocial['snapchat'] = $request->input('socialInfo.snapchat','');
                    $dataSocial['vsco'] = $request->input('socialInfo.vsco','');
                    $dataSocial['tiktok'] = $request->input('socialInfo.tiktok','');
                    $dataSocial['twitter'] = $request->input('socialInfo.twitter','');
                    $dataSocial['linkedin'] = $request->input('socialInfo.linkedIn','');

                    // Persist socials to actual schema social_site_infos(social_id, social_username)
                    foreach (['facebook','instagram','snapchat','vsco','tiktok','twitter','linkedin'] as $site) {
                        $username = $dataSocial[$site] ?? '';
                        if ($username === '') { continue; }
                        $siteRow = DB::table('social_sites')->where('social_site_name', $site)->first();
                        if (!$siteRow) { continue; }
                        $existing = DB::table('social_site_infos')
                            ->where('user_id',$id)
                            ->where('social_id',$siteRow->id)
                            ->first();
                        if ($existing) {
                            DB::table('social_site_infos')
                              ->where('id',$existing->id)
                              ->update(['social_username'=>$username,'updated_at'=>now()]);
                        } else {
                            DB::table('social_site_infos')->insert([
                                'user_id'=>$id,
                                'social_id'=>$siteRow->id,
                                'social_username'=>$username,
                                'created_at'=>now(),
                                'updated_at'=>now(),
                            ]);
                        }
                    }

                    // Social media persistence is already handled above using correct schema (lines 193-216)

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
                    // Accept both snake_case and camelCase from clients and preserve existing when omitted
                    // Important: use input() to correctly read nested JSON payloads
                    $pi = (array) $request->input('personalInfo', []);
                    $data['gender'] = $pi['gender'] ?? $user->gender;
                    $data['bio'] = $pi['bio'] ?? $user->bio;
                    $data['occupation'] = $pi['occupation'] ?? $user->occupation;
                    $data['politics'] = $pi['politics'] ?? $user->politics;
                    $data['religion'] = $pi['religion'] ?? $user->religion;
                    $data['sexuality'] = $pi['sexuality'] ?? $user->sexuality;
                    $data['relationship'] = $pi['relationship'] ?? $user->relationship;
                    $data['city'] = $pi['city'] ?? $user->city;
                    // Additional camelCase -> snake_case mappings
                    $data['hometown'] = $pi['hometown'] ?? $user->hometown;
                    $data['high_school'] = $pi['highSchool'] ?? $pi['high_school'] ?? $user->high_school;
                    // Track whether client explicitly sent this field to avoid overwriting with stale values
                    $classYearProvided = array_key_exists('classYear', $pi) || array_key_exists('class_year', $pi);
                    \Log::info('ClassYear debug', [
                        'classYearProvided' => $classYearProvided,
                        'pi_classYear' => $pi['classYear'] ?? 'not_set',
                        'pi_class_year' => $pi['class_year'] ?? 'not_set',
                        'personalInfo_keys' => array_keys($pi)
                    ]);
                    if ($classYearProvided) {
                        $data['class_year'] = $pi['classYear'] ?? $pi['class_year'];
                    }
                    $data['campus'] = $pi['campus'] ?? $user->campus;
                    $data['career_aspirations'] = $pi['careerAspirations'] ?? $pi['career_aspirations'] ?? $user->career_aspirations;
                    $data['postgraduate'] = $pi['postgraduate'] ?? $user->postgraduate;
                    $data['postgraduate_plans'] = $pi['postgraduatePlans'] ?? $pi['postgraduate_plans'] ?? $user->postgraduate_plans;
                    $data['favorite_music'] = $pi['favorite_music'] ?? ($pi['favoriteMusic'] ?? ($user->favorite_music ?? null));
                    $data['favorite_tv'] = $pi['favorite_tv'] ?? ($pi['favoriteTV'] ?? ($user->favorite_tv ?? null));
                    $data['favorite_games'] = $pi['favorite_games'] ?? ($pi['favoriteGame'] ?? $pi['favoriteGames'] ?? ($user->favorite_games ?? null));
                    $data['greek_life'] = $pi['greek_life'] ?? ($pi['greekLife'] ?? ($user->greek_life ?? null));
                    
                    // Handle education and studying fields properly
                    if (\Schema::hasColumn('users', 'studying')) {
                        // Both columns exist - handle them separately
                        $data['education'] = $pi['education'] ?? $user->education;
                        $data['studying'] = $pi['studying'] ?? $user->studying;
                    } else {
                        // Only education column exists - prioritize studying input for education field
                        if (isset($pi['studying']) && !empty($pi['studying'])) {
                            $data['education'] = $pi['studying'];
                        } else {
                            $data['education'] = $pi['education'] ?? $user->education;
                        }
                    }
                    
                    $club = $pi['club'] ?? [];
                    $data['club'] = null;
                    $data['jersey_number'] = null;
                    if (\Schema::hasColumn('users', 'club')) {
                        $data['club'] = isset($club['club']) ? $club['club'] : $user->club;
                    }
                    if (\Schema::hasColumn('users', 'jersey_number')) {
                        $data['jersey_number'] = isset($club['jersey_number']) ? $club['jersey_number'] : (isset($club['number']) ? $club['number'] : $user->jersey_number);
                    }
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
                            'dob' => $data['dob'] ?? $user->dob,
                            'locationTimestamp' => $data['locationTimestamp'],
                            'gender' => $data['gender'] ?? null,
                            'bio' => $data['bio'] ?? null,
                            'education' => $data['education'] ?? null,
                            'occupation' => $data['occupation'] ?? null,
                            'politics' => $data['politics'] ?? null,
                            'religion' => $data['religion'] ?? null,
                            'sexuality' => $data['sexuality'] ?? null,
                            'relationship' => $data['relationship'] ?? null,
                            'city' => $data['city'] ?? null,
                            'favorite_music' => $data['favorite_music'] ?? null,
                            'favorite_tv' => $data['favorite_tv'] ?? null,
                            'favorite_games' => $data['favorite_games'] ?? null,
                            'greek_life' => $data['greek_life'] ?? null,
                            'club' => $data['club'] ?? null,
                            'jersey_number' => $data['jersey_number'] ?? null,
                            'ghost_mode_flag' => $ghost_mode_flag,
                            // extra profile fields
                            'hometown' => $data['hometown'] ?? null,
                            'high_school' => $data['high_school'] ?? null,
                            'campus' => $data['campus'] ?? null,
                            'career_aspirations' => $data['career_aspirations'] ?? null,
                            'postgraduate' => $data['postgraduate'] ?? null,
                            'postgraduate_plans' => $data['postgraduate_plans'] ?? null,
                        ];
                        // Only add studying if the column exists
                        if (isset($data['studying'])) {
                            $updateData['studying'] = $data['studying'];
                        }
                        if ($classYearProvided) {
                            $updateData['class_year'] = $data['class_year'];
                        }
                        // Only update columns that exist in the users table
                        $safeUpdate = [];
                        foreach ($updateData as $column => $value) {
                            if (Schema::hasColumn('users', $column)) {
                                $safeUpdate[$column] = $value;
                            }
                        }
                        \Log::info('Profile update data', [
                            'user_id' => $id,
                            'updateData_keys' => array_keys($updateData),
                            'safeUpdate_keys' => array_keys($safeUpdate),
                            'safeUpdate' => $safeUpdate
                        ]);
                        if (!empty($safeUpdate)) {
                            DB::table('users')->where('id', $id)->update($safeUpdate);
                        }
                        DB::commit();
                        $user = User::Where('id', $id)->first();
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        \Log::error('updateUserProfile exception', ['message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['facebook']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['facebook']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['instagram']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['instagram']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['snapchat']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['snapchat']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['vsco']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['vsco']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['tiktok']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['tiktok']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['twitter']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['twitter']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['resume']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['resume']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['coverLetter']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['coverLetter']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['email']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['email']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['website']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['website']
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
                            ->where('social_id', $social_id)
                            ->first();

                        if ($userSocialInfo) {
                            $userSocialInfo->update(
                                [
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['contact']
                                ]
                            );
                        } else {
                            $userSocialInfo = SocialSiteInfo::create(
                                [
                                    'user_id' => $id,
                                    'social_id' => $social_name->id,
                                    'social_username' => $dataSocial['contact']
                                ]
                            );
                        }
                    }

                    // Important: use input() to correctly read nested JSON payloads
                    $pi2 = (array) $request->input('personalInfo', []);
                    if (!empty($pi2['dob'])) {
                        $dob_txt = $pi2['dob'];
                        $dob_obj = Carbon::parse($dob_txt);
                        if (!empty($dob_obj)) {
                            $data['dob'] =  $dob_obj->format("Y-m-d");
                        }
                    } else {
                        $data['dob'] = $user->dob;
                    }

                    $data['locationTimestamp'] = $actionTime;
                    $data['gender'] = $pi2['gender'] ?? $user->gender;
                    $data['bio'] = $pi2['bio'] ?? $user->bio;
                    $data['education'] = $pi2['education'] ?? $user->education;
                    $data['occupation'] = $pi2['occupation'] ?? $user->occupation;
                    $data['politics'] = $pi2['politics'] ?? $user->politics;
                    $data['religion'] = $pi2['religion'] ?? $user->religion;
                    $data['sexuality'] = $pi2['sexuality'] ?? $user->sexuality;
                    $data['relationship'] = $pi2['relationship'] ?? $user->relationship;
                    $data['city'] = $pi2['city'] ?? $user->city;
                    
                    // Additional camelCase -> snake_case mappings
                    $data['hometown'] = $pi2['hometown'] ?? $user->hometown;
                    $data['high_school'] = $pi2['highSchool'] ?? $pi2['high_school'] ?? $user->high_school;
                    // Track whether client explicitly sent this field to avoid overwriting with stale values
                    $classYearProvided = array_key_exists('classYear', $pi2) || array_key_exists('class_year', $pi2);
                    if ($classYearProvided) {
                        $data['class_year'] = $pi2['classYear'] ?? $pi2['class_year'];
                    }
                    $data['campus'] = $pi2['campus'] ?? $user->campus;
                    $data['career_aspirations'] = $pi2['careerAspirations'] ?? $pi2['career_aspirations'] ?? $user->career_aspirations;
                    $data['postgraduate'] = $pi2['postgraduate'] ?? $user->postgraduate;
                    $data['postgraduate_plans'] = $pi2['postgraduatePlans'] ?? $pi2['postgraduate_plans'] ?? $user->postgraduate_plans;
                    $data['favorite_music'] = $pi2['favorite_music'] ?? ($pi2['favoriteMusic'] ?? ($user->favorite_music ?? null));
                    $data['favorite_tv'] = $pi2['favorite_tv'] ?? ($pi2['favoriteTV'] ?? ($user->favorite_tv ?? null));
                    $data['favorite_games'] = $pi2['favorite_games'] ?? ($pi2['favoriteGame'] ?? $pi2['favoriteGames'] ?? ($user->favorite_games ?? null));
                    $data['greek_life'] = $pi2['greek_life'] ?? ($pi2['greekLife'] ?? ($user->greek_life ?? null));
                    
                    // Handle education and studying fields properly
                    if (\Schema::hasColumn('users', 'studying')) {
                        // Both columns exist - handle them separately
                        $data['education'] = $pi2['education'] ?? $user->education;
                        $data['studying'] = $pi2['studying'] ?? $user->studying;
                    } else {
                        // Only education column exists - prioritize studying input for education field
                        if (isset($pi2['studying']) && !empty($pi2['studying'])) {
                            $data['education'] = $pi2['studying'];
                        } else {
                            $data['education'] = $pi2['education'] ?? $user->education;
                        }
                    }
                    
                    $club = $pi2['club'] ?? [];
                    $data['club'] = null;
                    $data['jersey_number'] = null;
                    if (\Schema::hasColumn('users', 'club')) {
                        $data['club'] = isset($club['club']) ? $club['club'] : $user->club;
                    }
                    if (\Schema::hasColumn('users', 'jersey_number')) {
                        $data['jersey_number'] = isset($club['jersey_number']) ? $club['jersey_number'] : (isset($club['number']) ? $club['number'] : $user->jersey_number);
                    }
                    $ghost_mode_flag = 0;
                    if (!empty($request->ghostMode)) {
                        $ghost_mode_flag = ($request->ghostMode == 'true') ? 1 : 0;
                    }
                    $updateBlock = [
                        'dob' => $data['dob'] ?? $user->dob,
                        'locationTimestamp' => $data['locationTimestamp'],
                        'gender' => $data['gender'] ?? null,
                        'bio' => $data['bio'] ?? null,
                        'education' => $data['education'] ?? null,
                        'occupation' => $data['occupation'] ?? null,
                        'politics' => $data['politics'] ?? null,
                        'religion' => $data['religion'] ?? null,
                        'sexuality' => $data['sexuality'] ?? null,
                        'relationship' => $data['relationship'] ?? null,
                        'city' => $data['city'] ?? null,
                        'favorite_music' => $data['favorite_music'] ?? null,
                        'favorite_tv' => $data['favorite_tv'] ?? null,
                        'favorite_games' => $data['favorite_games'] ?? null,
                        'greek_life' => $data['greek_life'] ?? null,
                        'club' => $data['club'] ?? null,
                        'jersey_number' => $data['jersey_number'] ?? null,
                        'ghost_mode_flag' => $ghost_mode_flag,
                        // extra profile fields
                        'hometown' => $data['hometown'] ?? null,
                        'high_school' => $data['high_school'] ?? null,
                        'campus' => $data['campus'] ?? null,
                        'career_aspirations' => $data['career_aspirations'] ?? null,
                        'postgraduate' => $data['postgraduate'] ?? null,
                        'postgraduate_plans' => $data['postgraduate_plans'] ?? null,
                    ];
                    // Only add studying if the column exists
                    if (isset($data['studying'])) {
                        $updateBlock['studying'] = $data['studying'];
                    }
                    if ($classYearProvided) {
                        $updateBlock['class_year'] = $data['class_year'];
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
                        
                        $safeUpdate2 = [];
                        foreach ($updateBlock as $column => $value) {
                            if (Schema::hasColumn('users', $column)) {
                                $safeUpdate2[$column] = $value;
                            }
                        }
                        \Log::info('updateUserLocation data', [
                            'user_id' => $id,
                            'updateBlock_keys' => array_keys($updateBlock),
                            'safeUpdate2_keys' => array_keys($safeUpdate2),
                            'personalInfo' => $pi2
                        ]);
                        if (!empty($safeUpdate2)) {
                            \DB::table('users')->where('id', $id)->update($safeUpdate2);
                        }
                        
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Log::error('Profile update failed: ' . $e->getMessage());
                        return response()->json([
                            'message' => 'fail',
                            'details' => 'Profile update failed'
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $user = User::where('id', $id)->first();
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
        $avatarSelect = \Schema::hasColumn('users', 'profile_pic')
            ? 'users.profile_pic as avatarUrl'
            : (\Schema::hasColumn('users', 'pictureProfile')
                ? 'users.pictureProfile as avatarUrl'
                : 'NULL as avatarUrl');
        $p = DB::table('profile_likes')
            ->join('users', 'profile_likes.user_id', '=', 'users.id')
            ->where('profile_likes.profile_id', $id)
            ->where('profile_likes.is_deleted', 0)
            ->orderBy('profile_likes.id', 'desc')
            ->selectRaw('CAST(users.id AS CHAR) as id, users.first_name as firstName, users.last_name as lastName, ' . $avatarSelect)
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
        $avatarSelect = \Schema::hasColumn('users', 'profile_pic')
            ? 'users.profile_pic as avatarUrl'
            : (\Schema::hasColumn('users', 'pictureProfile')
                ? 'users.pictureProfile as avatarUrl'
                : 'NULL as avatarUrl');
        $p = DB::table('profile_likes')
            ->join('users', 'profile_likes.profile_id', '=', 'users.id')
            ->where('profile_likes.user_id', $id)
            ->where('profile_likes.is_deleted', 0)
            ->orderBy('profile_likes.id', 'desc')
            ->selectRaw('CAST(users.id AS CHAR) as id, users.first_name as firstName, users.last_name as lastName, ' . $avatarSelect)
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
                // Check if like record already exists (including soft-deleted)
                $existingLike = ProfileLike::where('user_id', $id)
                    ->where('profile_id', $profileLikeId)
                    ->first();
                
                $created = false;
                
                if ($existingLike) {
                    if ($existingLike->is_deleted == 1) {
                        // Reactivate soft-deleted like
                        $existingLike->update(['is_deleted' => 0]);
                        $created = true;
                        \Log::info('likeProfile reactivated soft-deleted like', [
                            'authId' => (string)$id,
                            'profileLikedId' => (string)$profileLikeId,
                        ]);
                    } else {
                        // Already liked and active
                        \Log::info('likeProfile already liked and active', [
                            'authId' => (string)$id,
                            'profileLikedId' => (string)$profileLikeId,
                        ]);
                    }
                } else {
                    // Create new like record
                    try {
                        ProfileLike::create(['user_id' => $id, 'profile_id' => $profileLikeId]);
                        $created = true;
                        \Log::info('likeProfile created new like', [
                            'authId' => (string)$id,
                            'profileLikedId' => (string)$profileLikeId,
                        ]);
                    } catch (\Throwable $e) {
                        \Log::error('likeProfile failed to create like', [
                            'authId' => (string)$id,
                            'profileLikedId' => (string)$profileLikeId,
                            'error' => $e->getMessage(),
                        ]);
                    }
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
                // Return success JSON response with like status
                return response()->json([
                    'success' => true,
                    'created' => $created,
                    'message' => $created ? 'Profile liked' : 'Already liked'
                ], $created ? Response::HTTP_CREATED : Response::HTTP_OK);
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
                $deleted = ProfileLike::where('user_id', $id)->where('profile_id', $profileLikeId)->update(['is_deleted' => 1]);
                return response()->json([
                    'success' => true,
                    'deleted' => $deleted > 0,
                    'message' => $deleted > 0 ? 'Profile unliked' : 'Not previously liked'
                ]);
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
     * Block a user
     * 
     * @OA\Post(
     * path="/profile/{id}/block",
     * summary="Block a user",
     * description="Block a user to prevent mutual visibility",
     * operationId="blockUser",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(type="string"),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=false,
     *    description="Block details",
     *    @OA\JsonContent(
     *       @OA\Property(property="reason", type="string", example="Inappropriate behavior"),
     *    ),
     * ),
     * @OA\Response(response=200, description="Success"),
     * @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function blockUser(Request $request, $id)
    {
        $currentUserId = Auth::user()->id;
        $userToBlockId = $id;
        
        // Prevent self-blocking
        if ($currentUserId == $userToBlockId) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Cannot block yourself'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Check if user exists
        $userToBlock = User::find($userToBlockId);
        if (!$userToBlock) {
            return response()->json([
                'message' => 'fail',
                'details' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        try {
            DB::beginTransaction();
            
            // Create or reactivate block
            $block = UserBlock::where('blocker_id', $currentUserId)
                ->where('blocked_id', $userToBlockId)
                ->first();
                
            if ($block) {
                if ($block->is_active) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'success',
                        'details' => 'User already blocked'
                    ], Response::HTTP_OK);
                } else {
                    // Reactivate existing block
                    $block->update([
                        'is_active' => true,
                        'reason' => $request->reason
                    ]);
                }
            } else {
                // Create new block
                UserBlock::create([
                    'blocker_id' => $currentUserId,
                    'blocked_id' => $userToBlockId,
                    'reason' => $request->reason,
                    'is_active' => true
                ]);
            }
            
            DB::commit();
            
            \Log::info('User blocked', [
                'blocker_id' => $currentUserId,
                'blocked_id' => $userToBlockId,
                'reason' => $request->reason
            ]);
            
            return response()->json([
                'message' => 'success',
                'details' => 'User blocked successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Block user error: ' . $e->getMessage());
            return response()->json([
                'message' => 'fail',
                'details' => 'Failed to block user'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Unblock a user
     * 
     * @OA\Delete(
     * path="/profile/{id}/block",
     * summary="Unblock a user",
     * description="Unblock a previously blocked user",
     * operationId="unblockUser",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(type="string"),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(response=200, description="Success"),
     * @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function unblockUser(Request $request, $id)
    {
        $currentUserId = Auth::user()->id;
        $userToUnblockId = $id;
        
        try {
            $block = UserBlock::where('blocker_id', $currentUserId)
                ->where('blocked_id', $userToUnblockId)
                ->where('is_active', true)
                ->first();
                
            if (!$block) {
                return response()->json([
                    'message' => 'fail',
                    'details' => 'User is not blocked'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $block->update(['is_active' => false]);
            
            \Log::info('User unblocked', [
                'blocker_id' => $currentUserId,
                'unblocked_id' => $userToUnblockId
            ]);
            
            return response()->json([
                'message' => 'success',
                'details' => 'User unblocked successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            \Log::error('Unblock user error: ' . $e->getMessage());
            return response()->json([
                'message' => 'fail',
                'details' => 'Failed to unblock user'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Report and block a user
     * 
     * @OA\Post(
     * path="/profile/{id}/report",
     * summary="Report and block a user",
     * description="Report a user for inappropriate behavior and automatically block them",
     * operationId="reportAndBlockUser",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(type="string"),
     *    in="path",
     *    required=true,
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Report details",
     *    @OA\JsonContent(
     *       required={"reason_category"},
     *       @OA\Property(property="reason_category", type="string", example="This user was sending inappropriate messages"),
     *       @OA\Property(property="info", type="string", example="Additional details (optional)"),
     *    ),
     * ),
     * @OA\Response(response=200, description="Success"),
     * @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function reportAndBlockUser(Request $request, $id)
    {
        $currentUserId = Auth::user()->id;
        $reportedUserId = $id;
        
        // Prevent self-reporting
        if ($currentUserId == $reportedUserId) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Cannot report yourself'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Validate required fields
        if (!$request->reason_category) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Reason is required'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Check if users exist
        $reporter = User::find($currentUserId);
        $reportedUser = User::find($reportedUserId);
        
        if (!$reportedUser) {
            return response()->json([
                'message' => 'fail',
                'details' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        try {
            DB::beginTransaction();
            
            $time_zone = env('TIME_ZONE');
            $createdTime = Carbon::now($time_zone)->format('Y-m-d H:i:s');
            
            // Create report
            $report = Report::create([
                'user_id' => $currentUserId,
                'reported_user_id' => $reportedUserId,
                'info' => $request->info ?? '', // Optional additional info
                'reason_category' => $request->reason_category, // User-typed reason
                'auto_blocked' => true,
                'reporter_email' => $reporter->email,
                'reported_user_email' => $reportedUser->email,
                'reporter_name' => $reporter->first_name . ' ' . $reporter->last_name,
                'reported_user_name' => $reportedUser->first_name . ' ' . $reportedUser->last_name,
                'dateCreated' => $createdTime
            ]);
            
            // Auto-block the reported user
            $block = UserBlock::where('blocker_id', $currentUserId)
                ->where('blocked_id', $reportedUserId)
                ->first();
                
            if ($block) {
                $block->update([
                    'is_active' => true,
                    'reason' => 'Reported: ' . $request->reason_category
                ]);
            } else {
                UserBlock::create([
                    'blocker_id' => $currentUserId,
                    'blocked_id' => $reportedUserId,
                    'reason' => 'Reported: ' . $request->reason_category,
                    'is_active' => true
                ]);
            }
            
            DB::commit();
            
            // Send email notifications to admins
            $this->sendReportNotificationEmails($report);
            
            \Log::info('User reported and blocked', [
                'reporter_id' => $currentUserId,
                'reported_id' => $reportedUserId,
                'reason_category' => $request->reason_category,
                'report_id' => $report->id
            ]);
            
            return response()->json([
                'message' => 'success',
                'details' => 'User reported and blocked successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Report and block user error: ' . $e->getMessage());
            return response()->json([
                'message' => 'fail',
                'details' => 'Failed to report user'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get blocked users list
     * 
     * @OA\Get(
     * path="/profile/{id}/blocked",
     * summary="Get blocked users list",
     * description="Get list of users blocked by the current user",
     * operationId="getBlockedUsers",
     * security={{"bearerAuth":{}}},
     * tags={"Profile"},
     * @OA\Parameter(
     *    name="id",
     *    @OA\Schema(type="string"),
     *    in="path",
     *    required=true,
     * ),
     * @OA\Response(response=200, description="Success"),
     * @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getBlockedUsers(Request $request, $id)
    {
        if (!$this->checkAuth($id)) {
            return response()->json([
                'message' => 'fail',
                'details' => 'Authorization'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        try {
            $blockedUsers = UserBlock::where('blocker_id', $id)
                ->where('is_active', true)
                ->with(['blocked' => function($query) {
                    $query->select('id', 'first_name', 'last_name', 'profile_pic');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($block) {
                    return [
                        'id' => $block->blocked->id,
                        'firstName' => $block->blocked->first_name,
                        'lastName' => $block->blocked->last_name,
                        'avatarUrl' => $block->blocked->profile_pic,
                        'blockedAt' => $block->created_at->toISOString(),
                        'reason' => $block->reason
                    ];
                });
            
            return response()->json([
                'message' => 'success',
                'data' => $blockedUsers
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            \Log::error('Get blocked users error: ' . $e->getMessage());
            return response()->json([
                'message' => 'fail',
                'details' => 'Failed to get blocked users'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send report notification emails to admins
     */
    private function sendReportNotificationEmails($report)
    {
        try {
            $adminEmails = ['nolan@calibrr.com', 'contact@calibrr.com', 'eliyoung4now@gmail.com'];
            $reasonName = $report->reason_category; // User-typed reason
            
            $emailData = [
                'reportId' => $report->id,
                'reporterName' => $report->reporter_name,
                'reporterEmail' => $report->reporter_email,
                'reportedUserName' => $report->reported_user_name,
                'reportedUserEmail' => $report->reported_user_email,
                'reasonCategory' => $reasonName,
                'description' => $report->info,
                'reportedAt' => $report->created_at->format('Y-m-d H:i:s T')
            ];
            
            // Use Lambda service if available, otherwise log for manual review
            if (class_exists('App\Services\LambdaNotificationService')) {
                $reportDataWithEmails = array_merge($emailData, ['adminEmails' => $adminEmails]);
                app(LambdaNotificationService::class)->notifyUserReported($reportDataWithEmails);
            } else {
                \Log::info('User Report Notification', [
                    'admin_emails' => $adminEmails,
                    'report_data' => $emailData
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send report notification emails: ' . $e->getMessage());
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

                    // Prefer S3 when configured; otherwise fall back to public storage
                    $disk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
                    $stored = $avatar->store('profile', $disk);
                    $avatar_path = $disk === 's3' ? Storage::disk('s3')->url($stored) : Storage::disk('public')->url($stored);
                    // Support legacy column names if present
                    $avatarColumn = Schema::hasColumn('users', 'profile_pic') ? 'profile_pic' : (Schema::hasColumn('users', 'pictureProfile') ? 'pictureProfile' : null);
                    if ($avatarColumn) { $user->update([$avatarColumn => $avatar_path]); }
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

                    // Prefer S3 when configured; otherwise fall back to public storage
                    $disk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
                    $stored = $avatar->store('banner', $disk);
                    $ci_path = $disk === 's3' ? Storage::disk('s3')->url($stored) : Storage::disk('public')->url($stored);
                    // Support legacy column names if present
                    $coverColumn = Schema::hasColumn('users', 'cover_image') ? 'cover_image' : (Schema::hasColumn('users', 'pictureCover') ? 'pictureCover' : null);
                    if ($coverColumn) { $user->update([$coverColumn => $ci_path]); }
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
