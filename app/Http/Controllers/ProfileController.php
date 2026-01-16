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
use App\Models\ProfileLike;
use App\Models\SocialSiteInfo;
use App\Models\LocationInfo;
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
        if (!$id) {
            return response()->json([
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $user = User::Where('id', $id)->first();
            if ($user) {
                return response()->json(new UserResource($user));
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not registered'
                ], Response::HTTP_BAD_REQUEST);
            }
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
     *          @OA\Property(property="location", type="object", 
     *          @OA\Property(property="latitude", type="integer", example="198"),
     *          @OA\Property(property="longitude", type="integer", example="123"),
     *          ),
     * 
     *          @OA\Property(property="personalInfo", type="object"),
     * 
     *          @OA\Property(property="socialInfo", type="object", 
     *          @OA\Property(property="facebook", type="string",example="No"),
     *          @OA\Property(property="instagram", type="string",example="No"),
     *          @OA\Property(property="snapchat", type="string",example="No"),
     *          @OA\Property(property="linkedIn", type="string",example="No"),
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
        if (!$id) {
            return response()->json([
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                $dataSocial['linkedIn'] = !empty($request->socialInfo['linkedIn']) ? $request->socialInfo['linkedIn'] : '';
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

                if ($dataSocial['linkedIn']) {
                    $name = "linkedIn";
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
                                'social_siteUsername' => $dataSocial['linkedIn']
                            ]
                        );
                    } else {
                        $userSocialInfo = SocialSiteInfo::create(
                            [
                                'user_id' => $id,
                                'socila_site_row_id' => $social_name->id,
                                'social_siteUsername' => $dataSocial['linkedIn']
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
                        $data['dob'] =  $dob_obj->format("Y-m-d H:m:s");
                    }
                }
                $data['locationTimestamp'] = $actionTime;
                $data['gender'] = !empty($request->personalInfo['gender']) ? $request->personalInfo['gender'] : '';
                $data['bio'] = !empty($request->personalInfo['bio']) ? $request->personalInfo['bio'] : '';
                $data['education'] = !empty($request->personalInfo['education']) ? $request->personalInfo['education'] : '';
                $data['occupation'] = !empty($request->personalInfo['occupation']) ? $request->personalInfo['occupation'] : '';
                $data['politics'] = !empty($request->personalInfo['politics']) ? $request->personalInfo['politics'] : '';
                $data['religion'] = !empty($request->personalInfo['religion']) ? $request->personalInfo['religion'] : '';
                $data['sexuality'] = !empty($request->personalInfo['sexuality']) ? $request->personalInfo['sexuality'] : '';
                $data['relationship'] = !empty($request->personalInfo['relationship']) ? $request->personalInfo['relationship'] : '';
                $data['city'] = !empty($request->personalInfo['city']) ? $request->personalInfo['city'] : '';

                $user->update([
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
                ]);
                return response()->json(new UserResource($user));
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not registered'
                ], Response::HTTP_BAD_REQUEST);
            }
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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                    'massage' => 'Fail',
                    'details' => 'User is not registered'
                ], Response::HTTP_BAD_REQUEST);
            }
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
     *          @OA\Property(property="location", type="object", 
     *          @OA\Property(property="latitude", type="integer", example="198"),
     *          @OA\Property(property="longitude", type="integer", example="123"),
     *          ),
     * 
     *          @OA\Property(property="personalInfo", type="object"),
     * 
     *          @OA\Property(property="socialInfo", type="object", 
     *          @OA\Property(property="facebook", type="string",example="No"),
     *          @OA\Property(property="instagram", type="string",example="No"),
     *          @OA\Property(property="snapchat", type="string",example="No"),
     *          @OA\Property(property="linkedIn", type="string",example="No"),
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
        if (!$id) {
            return response()->json([
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                $dataSocial['linkedIn'] = !empty($request->socialInfo['linkedIn']) ? $request->socialInfo['linkedIn'] : '';
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

                if ($dataSocial['linkedIn']) {
                    $name = "linkedIn";
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
                                'social_siteUsername' => $dataSocial['linkedIn']
                            ]
                        );
                    } else {
                        $userSocialInfo = SocialSiteInfo::create(
                            [
                                'user_id' => $id,
                                'socila_site_row_id' => $social_name->id,
                                'social_siteUsername' => $dataSocial['linkedIn']
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
                    $name = "Cover Letter";
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
                        $data['dob'] =  $dob_obj->format("Y-m-d H:m:s");
                    }
                }
                $data['locationTimestamp'] = $actionTime;
                $data['gender'] = !empty($request->personalInfo['gender']) ? $request->personalInfo['gender'] : '';
                $data['bio'] = !empty($request->personalInfo['bio']) ? $request->personalInfo['bio'] : '';
                $data['education'] = !empty($request->personalInfo['education']) ? $request->personalInfo['education'] : '';
                $data['occupation'] = !empty($request->personalInfo['occupation']) ? $request->personalInfo['occupation'] : '';
                $data['politics'] = !empty($request->personalInfo['politics']) ? $request->personalInfo['politics'] : '';
                $data['religion'] = !empty($request->personalInfo['religion']) ? $request->personalInfo['religion'] : '';
                $data['sexuality'] = !empty($request->personalInfo['sexuality']) ? $request->personalInfo['sexuality'] : '';
                $data['relationship'] = !empty($request->personalInfo['relationship']) ? $request->personalInfo['relationship'] : '';
                $data['city'] = !empty($request->personalInfo['city']) ? $request->personalInfo['city'] : '';

                $user->update([
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
                ]);
                return response()->json(new UserResource($user));
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not registered'
                ], Response::HTTP_BAD_REQUEST);
            }
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
        if (!$id) {
            return response()->json([
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $rela_list = Relationship::where('user_id', '=', $id)->get();
            if (count($rela_list) > 0) {
                return response()->json(RelationshipResource::collection($rela_list));
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not reglationship'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Get Relationship
     *
     * @return json
     */

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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $rela = Relationship::where('user_id', '=', $userId)
                ->where('friend_id', '=', $friendId)
                ->first();
            if ($rela) {
                return response()->json(new RelationshipResource($rela));
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not relationships'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Request Friend
     *
     * @return json
     */

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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                    'Success'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not relationships'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Update Friend
     *
     * @return json
     */

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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                    "Success"
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not requested relationships'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Unblock User
     *
     * @return json
     */

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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
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
                    "Success"
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is blocked. Unblock to remove'
                ], Response::HTTP_BAD_REQUEST);
            }
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
        if (!$id) {
            return 0;
        } else {
            $user = ProfileLike::Where('profile_id', $id)->get();
            if ($user) {
                return count($user);
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not regiter'
                ], Response::HTTP_BAD_REQUEST);
            }
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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $profileLikeId = $request->profileLikeId;
            $user = ProfileLike::where('user_id', $id)->where('profile_id', $profileLikeId)->get();
            if (count($user) > 0) {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User and friend is liked'
                ], Response::HTTP_BAD_REQUEST);
            } else {
                $user = ProfileLike::create(
                    [
                        'user_id' => $id,
                        'profile_id' => $profileLikeId,
                    ],
                );
                return response()->json([
                    "Success",
                ], Response::HTTP_OK);
            }
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
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $profileLikeId = $request->profileLikeId;
            $user = ProfileLike::where('user_id', $id)->where('profile_id', $profileLikeId)->first();
            if ($user) {
                $user->delete($user);
                return response()->json([
                    "Success",
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User and friend is not liked'
                ], Response::HTTP_BAD_REQUEST);
            }
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
        if (!$id) {
            return response()->json([
                'massage' => 'Fail',
                'details' => 'Id not found'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $userReport = Report::where('user_id', $id)->get();
            if (count($userReport) > 0) {
                return response()->json(ReportResource::collection($userReport));
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not reported'
                ], Response::HTTP_BAD_REQUEST);
            }
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
        if (!$id) {
            return response()->json([
                'massage' => 'Fail',
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
                    "Success",
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
                    "Success",
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'massage' => 'Fail',
                    'details' => 'User is not reported'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }
}
