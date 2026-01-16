<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'blocker_id',
        'blocked_id', 
        'reason',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    /**
     * Get the user who is doing the blocking
     */
    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }
    
    /**
     * Get the user who is being blocked
     */
    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
    
    /**
     * Check if user A has blocked user B
     */
    public static function isBlocked($blockerId, $blockedId)
    {
        return self::where('blocker_id', $blockerId)
            ->where('blocked_id', $blockedId)
            ->where('is_active', true)
            ->exists();
    }
    
    /**
     * Check if two users have blocked each other (mutual block)
     */
    public static function isMutuallyBlocked($userId1, $userId2)
    {
        return self::isBlocked($userId1, $userId2) || self::isBlocked($userId2, $userId1);
    }
    
    /**
     * Get all users blocked by a specific user
     */
    public static function getBlockedUsers($userId)
    {
        return self::where('blocker_id', $userId)
            ->where('is_active', true)
            ->with('blocked')
            ->get();
    }
}