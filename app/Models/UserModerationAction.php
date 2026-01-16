<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModerationAction extends Model
{
    use HasFactory;
    
    // Disable updated_at since our table only has created_at
    const UPDATED_AT = null;
    protected $table = 'user_moderation_actions';
    
    protected $fillable = [
        'user_id',
        'action',
        'reason',
        'duration_hours',
        'admin_email'
    ];
    
    protected $casts = [
        'duration_hours' => 'integer',
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
