<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
    return [
      'id' => "$this->id",
      'firstName' => $this->firstname,
      'lastName' => $this->lastname,
      'email' => $this->email,
      'phone' => $this->phone,
      'ghostMode' => $this->ghostMode,
      'subscription' => $this->subscription,
      'location' => [
        'latitude' => $this->latitude,
        'longitude' => $this->longitude,
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
      ],
      'socialInfo' => [
        'facebook' => $this->facebook,
        'instagram' => $this->instagram,
        'linkedIn' => $this->linkedIn,
        'twitter' => $this->twitter,
        'resume' => $this->resume,
        'coverLetter' => $this->coverLetter,
        'email' => $this->email_2,
        'website' => $this->website,
        'contact' => $this->contact,
      ],
      'liked' => $this->liked,
      'likeCount' => $this->likeCount,
      'visitCount' => $this->visitCount,
    ];
  }
}
