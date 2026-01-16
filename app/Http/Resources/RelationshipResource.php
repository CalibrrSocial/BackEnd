<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Relationship;

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

    $status = $this->status;
    $dateAction = '';
    if ($status == 'requested') {
      $dateAction = 'dateRequested';
    } else if ($status == 'accepted') {
      $dateAction = 'dateAccepted';
    } else if ($status == 'rejected') {
      $dateAction = 'dateRejected';
    } else if ($status == 'blocked') {
      $dateAction = 'dateBlocked';
    } else {
      $dateAction = null;
    }

    return [
      'user_id' => $this->user_id,
      'friend_id' => $this->friend_id,
      'status' => $this->status,
      $dateAction => $this->$dateAction,
    ];
  }
}
