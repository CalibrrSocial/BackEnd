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
    
    /**
     * Predefined report reason categories
     */
    const REASON_CATEGORIES = [
        'inappropriate_content' => 'Inappropriate Content',
        'harassment' => 'Harassment or Bullying',
        'fake_profile' => 'Fake Profile',
        'spam' => 'Spam or Scam',
        'hate_speech' => 'Hate Speech',
        'violence' => 'Violence or Threats',
        'nudity' => 'Nudity or Sexual Content',
        'other' => 'Other'
    ];
    
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
     * Get the human-readable reason category
     */
    public function getReasonCategoryNameAttribute()
    {
        return self::REASON_CATEGORIES[$this->reason_category] ?? 'Unknown';
    }
}