<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RelationshipResource extends JsonResource
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
      'user_id' => $this->user_id,
      'friend_id' => $this->friend_id,
      'status' => $this->status,
      'dateRequested' => $this->dateRequested,
      'dateAccepted' => $this->dateAccepted,
      'dateRejected' => $this->dateRejected,
      'dateBlocked' => $this->dateBlocked,
    ];
  }
}
