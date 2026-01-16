<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileLike extends Model
{
  use HasFactory;
  protected $guarded = [];
  
  // Map to the actual database column names
  const CREATED_AT = 'created_on';
  const UPDATED_AT = 'updated_on';
  
  protected $fillable = [
    'user_id',
    'profile_id', 
    'is_liked',
    'status',
    'is_deleted'
  ];
  
  protected $attributes = [
    'is_liked' => 1,
    'status' => 1,
    'is_deleted' => 0
  ];
}
