<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

    for ($i = 0; $i < count($social_list); $i++) {
      $socials[$i]['name'] = $social_list[$i]->social_site_name;
      $socials[$i]['id'] = $social_list[$i]->id;
    }
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
    $liked = DB::table('profile_likes')->where('user_id', $user_id)->first();
    if ($liked) {
      $like = true;
    } else {
      $like = false;
    }
    $visited = DB::table('profile_visit_analytics')->where('visited_profile_id', $user_id)->get();
    $count_visit = count($visited);

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
      'socialInfo' => $info,
      'liked' => $like,
      'likeCount' => $countLike,
      'visitCount' => $count_visit,
      'bestFriends' => $this->bestFriends,
      'courses' => $this->courses
    ];
  }
}
