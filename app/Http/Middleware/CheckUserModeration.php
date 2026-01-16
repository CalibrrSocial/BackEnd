<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserModeration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user) {
            // Check if user is banned
            if ($user->moderation_state === 'banned') {
                return response()->json([
                    'error' => 'Account has been banned',
                    'message' => $user->moderation_reason ?: 'Your account has been permanently banned.'
                ], 403);
            }
            
            // Check if user is suspended
            if ($user->moderation_state === 'suspended') {
                $now = now();
                $suspensionEnds = $user->suspension_ends_at;
                
                if ($suspensionEnds && $now < $suspensionEnds) {
                    $timeRemaining = $now->diffForHumans($suspensionEnds, true);
                    $suspensionMessage = $user->moderation_reason 
                        ? $user->moderation_reason . " Your suspension will be lifted in {$timeRemaining}."
                        : "Your account is temporarily suspended. Your suspension will be lifted in {$timeRemaining}.";
                    
                    return response()->json([
                        'error' => 'Account is suspended',
                        'message' => $suspensionMessage,
                        'suspension_ends_at' => $suspensionEnds->toISOString()
                    ], 403);
                } elseif ($suspensionEnds && $now >= $suspensionEnds) {
                    // Auto-unsuspend if suspension period has ended
                    $user->moderation_state = 'active';
                    $user->suspension_ends_at = null;
                    $user->save();
                }
            }
        }
        
        return $next($request);
    }
}
