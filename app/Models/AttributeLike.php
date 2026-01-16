<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeLike extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'user_id',
        'profile_id', 
        'category',
        'attribute',
        'is_liked',
        'is_deleted'
    ];
    
    protected $attributes = [
        'is_liked' => 1,
        'is_deleted' => 0
    ];
    
    protected $casts = [
        'is_liked' => 'boolean',
        'is_deleted' => 'boolean',
    ];
}
