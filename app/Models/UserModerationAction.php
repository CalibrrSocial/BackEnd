<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModerationAction extends Model
{
    use HasFactory;
    
    protected $table = 'user_moderation_actions';
    
    protected $fillable = [
        'user_id',
        'action',
        'reason',
        'expires_at',
        'admin_email'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime'
    ];
    
    /**
     * Get the user that was moderated
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
