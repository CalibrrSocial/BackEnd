<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserSearchResource extends JsonResource
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
        $locationInfo = DB::table('location_infos')->select('*')->where('user_id', '=', $user_id)->first();
        $latitude = !empty($locationInfo->latitude) ? (float)($locationInfo->latitude) : 0;
        $longitude = !empty($locationInfo->longitude) ? (float)($locationInfo->longitude) : 0;

        return [
            'id' => "$this->id",
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'pictureProfile' => $this->profile_pic,
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'personalInfo' => [
                'dob' => $this->dob,
                'education' => $this->education,
                'city' => $this->city,
                'greek_life' => $this->greek_life,
                'studying' => $this->studying,
                'club' => [
                    'club' => $this->club,
                    'jersey_number' => $this->jersey_number,
                ],
            ],
            'courses' => $this->courses ?? []
        ];
    }
}
