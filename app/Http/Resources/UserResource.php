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
    $like = DB::table('likes')->select('*')->where('friend_id', '=', $user_id)->get();
    $countLike = count($like);

    $locationInfo = DB::table('location_infos')->select('*')->where('user_id', '=', $user_id)->first();
    $latitude = !empty($locationInfo->latitude) ? (float)($locationInfo->latitude) : 0;
    $longitude = !empty($locationInfo->longitude) ? (float)($locationInfo->longitude) : 0;

    $socialInfo = DB::table('social_infos')->select('*')->where('user_id', '=', $user_id)->first();
    $facebook = !empty($socialInfo->facebook) ? $socialInfo->facebook : '';
    $instagram = !empty($socialInfo->instagram) ? $socialInfo->instagram : '';
    $snapchat = !empty($socialInfo->snapchat) ? $socialInfo->snapchat : '';
    $linkedIn = !empty($socialInfo->linkedIn) ? $socialInfo->linkedIn : '';
    $twitter = !empty($socialInfo->twitter) ? $socialInfo->twitter : '';
    $resume = !empty($socialInfo->resume) ? $socialInfo->resume : '';
    $coverLetter = !empty($socialInfo->coverLetter) ? $socialInfo->coverLetter : '';
    $email = !empty($socialInfo->email) ? $socialInfo->email : '';
    $website = !empty($socialInfo->website) ? $socialInfo->website : '';
    $contact = !empty($socialInfo->contact) ? $socialInfo->contact : '';
    $ghostMode = $this->ghostMode == 1 ? true : false;
    $liked = $this->liked == 1 ? true : false;

    return [
      'id' => "$this->id",
      'firstName' => $this->firstname,
      'lastName' => $this->lastname,
      'email' => $this->email,
      'phone' => $this->phone,
      'ghostMode' => $ghostMode,
      'subscription' => $this->subscription,
      'location' => [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ],
      'locationTimestamp' => $this->locationTimestamp,
      'pictureProfile' => $this->pictureProfile,
      'pictureCover' => $this->pictureCover,
      'personalInfo' => [
        'dob' => $this->dob,
        'gender' => $this->gender,
        'bio' => $this->bio,
        'education' => $this->education,
        'politics' => $this->politics,
        'religion' => $this->religion,
        'occupation' => $this->occupation,
        'sexuality' => $this->sexuality,
        'city' => $this->city,
      ],
      'socialInfo' => [
        'facebook' => $facebook,
        'instagram' => $instagram,
        'linkedIn' => $linkedIn,
        'twitter' => $twitter,
        'resume' => $resume,
        'coverLetter' => $coverLetter,
        'email' => $email,
        'website' => $website,
        'contact' => $contact,
      ],
      'liked' => $liked,
      'likeCount' => $countLike,
      'visitCount' => $this->visitCount,
    ];
  }
}
