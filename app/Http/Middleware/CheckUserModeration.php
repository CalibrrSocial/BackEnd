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
                    'message' => 'fail',
                    'details' => 'You have been banned from Calibrr Social.',
                    'moderation_state' => 'banned',
                    'moderation_reason' => $user->moderation_reason
                ], 403);
            }
            
            // Check if user is suspended
            if ($user->moderation_state === 'suspended') {
                $now = now();
                $suspensionEnds = $user->suspension_ends_at;
                
                if ($suspensionEnds && $now < $suspensionEnds) {
                    return response()->json([
                        'message' => 'fail',
                        'details' => 'You have been temporarily suspended from Calibrr Social.',
                        'moderation_state' => 'suspended',
                        'suspension_ends_at' => $suspensionEnds->format('Y-m-d H:i:s'),
                        'moderation_reason' => $user->moderation_reason
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
