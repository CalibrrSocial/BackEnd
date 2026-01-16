<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
   */
  public function toArray($request)
  {
    $user_id = $this->id;
    $like = DB::table('profile_likes')->select('*')->where('profile_id', '=', $user_id)->get();
    $countLike = count($like);

    $locationInfo = DB::table('location_infos')->select('*')->where('user_id', '=', $user_id)->first();
    $latitude = !empty($locationInfo->latitude) ? (float)($locationInfo->latitude) : 0;
    $longitude = !empty($locationInfo->longitude) ? (float)($locationInfo->longitude) : 0;


    $social_list = DB::table('social_sites')->select('*')->get();
    $socialInfo = DB::table('social_site_infos')->select('*')->where('user_id', '=', $user_id)->get();

    $socials = [];
    for ($i = 0; $i < count($social_list); $i++) {
      $socials[$i]['name'] = $social_list[$i]->social_site_name;
      $socials[$i]['id'] = $social_list[$i]->id;
    }
    // Ensure arrays are initialized to avoid undefined variable notices
    $social_info = [];
    $social_name = [];
    if (count($socialInfo) > 0) {
      for ($i = 0; $i < count($socialInfo); $i++) {
        $socila_site_row_id = $socialInfo[$i]->socila_site_row_id;
        $social_name[$i] = DB::table('social_sites')->select('social_site_name', 'id')->where('id', $socila_site_row_id)->first();
        $social_info[$i]['socila_site_row_id'] = $social_name[$i]->id;
        $social_info[$i]['info_name'] = $socialInfo[$i]->social_siteUsername;
      }
      $info = [];
      if (count($socials) > 0) {
        for ($i = 0; $i < count($socials); $i++) {
          $name = $socials[$i]['name'];
          $id = $socials[$i]['id'];
          for ($j = 0; $j < count($social_info); $j++) {
            if ($social_info[$j]['socila_site_row_id'] == $id) {
              $info_name = $social_info[$j]['info_name'];
              break;
            } else {
              $info_name = 'null';
            }
          }
          $ifo = [
            "$name" => $info_name,
          ];
          $info = $info + $ifo;
        }
      }
    } else {
      $info = [];
      for ($i = 0; $i < count($socials); $i++) {
        $name = $socials[$i]['name'];
        $id = $socials[$i]['id'];
        $ifo = [
          "$name" => 'null',
        ];
        $info = $info + $ifo;
      }
    }

    $ghostMode = $this->ghost_mode_flag == 1 ? true : false;
    // Whether the authenticated viewer liked this user
    $viewerId = auth()->check() ? auth()->id() : null;
    $like = false;
    if ($viewerId) {
      // Consider self-like as liked as well
      $liked = DB::table('profile_likes')->where('user_id', $viewerId)->where('profile_id', $user_id)->first();
      $like = $liked ? true : false;
    }
    $count_visit = 0;
    if (Schema::hasTable('profile_visit_analytics')) {
      $count_visit = DB::table('profile_visit_analytics')
        ->where('visited_profile_id', $user_id)
        ->count();
    }

    return [
      'id' => "$this->id",
      'firstName' => $this->first_name,
      'lastName' => $this->last_name,
      'email' => $this->email,
      'phone' => $this->phone,
      'ghostMode' => $ghostMode,
      'subscription' => "$this->subscription_type",
      'location' => [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ],
      'locationTimestamp' => $this->locationTimestamp,
      'pictureProfile' => $this->profile_pic,
      'pictureCover' => $this->cover_image,
      'personalInfo' => [
        'dob' => $this->dob,
        'gender' => $this->gender,
        'bio' => $this->bio,
        'education' => $this->education,
        'hometown' => $this->hometown,
        'highSchool' => $this->high_school,
        'classYear' => $this->class_year,
        'campus' => $this->campus,
        'careerAspirations' => $this->career_aspirations,
        'postgraduate' => $this->postgraduate,
        'postgraduatePlans' => $this->postgraduate_plans,
        'politics' => $this->politics,
        'religion' => $this->religion,
        'occupation' => $this->occupation,
        'sexuality' => $this->sexuality,
        'relationship' => $this->relationship,
        'city' => $this->city,
        'favorite_music' => $this->favorite_music,
        'favorite_tv' => $this->favorite_tv,
        'favorite_games' => $this->favorite_games,
        'greek_life' => $this->greek_life,
        'studying' => $this->studying,
        'club' => [
          'club' => $this->club,
          'jersey_number' => $this->jersey_number,
        ],
      ],
      'socialInfo' => array_map(function ($item) {
        return $item == 'null' ? '' : $item ;
      }, $info),
      'liked' => $like,
      'likeCount' => $countLike,
      'visitCount' => $count_visit,
      'bestFriends' => $this->bestFriends ?? [],
      'courses' => $this->courses ?? []
    ];
  }
}
