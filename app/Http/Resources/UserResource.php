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


    // Build social info keyed object based on actual schema: social_site_infos(social_id,social_username)
    $desiredKeys = ['instagram','facebook','snapchat','linkedIn','twitter','vsco','tiktok'];
    $rows = DB::table('social_site_infos')
      ->leftJoin('social_sites','social_site_infos.social_id','=','social_sites.id')
      ->select('social_sites.social_site_name as name','social_site_infos.social_username as username')
      ->where('user_id', '=', $user_id)
      ->get();
    $info = [];
    foreach ($desiredKeys as $k) { $info[$k] = ''; }
    foreach ($rows as $row) {
      $name = $row->name ?? null;
      $username = $row->username ?? '';
      if (!$name) { continue; }
      // Normalize names to client keys
      if ($name === 'linkedin') { $name = 'linkedIn'; }
      if ($name === 'x') { $name = 'twitter'; }
      if (array_key_exists($name, $info)) {
        $info[$name] = $username ?: '';
      } else {
        // Include any extra names as-is
        $info[$name] = $username ?: '';
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
      // Read from either new or legacy columns
      'pictureProfile' => $this->profile_pic ?? $this->pictureProfile ?? null,
      'pictureCover' => $this->cover_image ?? $this->pictureCover ?? null,
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
      // Keep socialInfo as an object with site-name keys; normalize 'null' → ''
      'socialInfo' => (function($arr) {
        $normalized = [];
        foreach ($arr as $k => $v) {
          $normalized[$k] = ($v === 'null' || $v === null) ? '' : $v;
        }
        return $normalized;
      })($info),
      'liked' => $like,
      'likeCount' => $countLike,
      'visitCount' => $count_visit,
      'bestFriends' => $this->bestFriends ?? [],
      'courses' => $this->courses ?? []
    ];
  }
}
