<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'reported_user_id',
        'info',
        'reason_category',
        'auto_blocked',
        'reporter_email',
        'reported_user_email', 
        'reporter_name',
        'reported_user_name',
        'dateCreated'
    ];
    
    protected $casts = [
        'auto_blocked' => 'boolean',
        'dateCreated' => 'datetime'
    ];
    
    // Removed predefined categories - users can now type their own reasons
    
    /**
     * Get the user who made the report
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the user who was reported
     */
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
    
    /**
     * Get the reason category (now just returns the user-typed reason)
     */
    public function getReasonCategoryNameAttribute()
    {
        return $this->reason_category ?? 'No reason provided';
    }
}