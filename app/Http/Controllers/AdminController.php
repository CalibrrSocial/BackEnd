<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserModerationAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
class AdminController extends Controller
{
    /**
     * Get paginated list of users with search
     */
    public function getUsers(Request $request)
    {
        try {
            $perPage = 50;
            $search = $request->get('search');
            $page = $request->get('page', 1);
            
            $query = User::select([
                'id', 
                'email', 
                'first_name', 
                'last_name', 
                'phone',
                'moderation_state',
                'suspension_ends_at',
                'moderation_reason',
                'created_at',
                'updated_at'
            ]);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('email', 'LIKE', "%{$search}%")
                      ->orWhere('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('id', '=', $search);
                });
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            return response()->json([
                'data' => $users->items(),
                'currentPage' => $users->currentPage(),
                'totalPages' => $users->lastPage(),
                'total' => $users->total()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Admin getUsers error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch users'], 500);
        }
    }
    
    /**
     * Get single user details
     */
    public function getUser($id)
    {
        try {
            $user = User::select([
                'id', 
                'email', 
                'first_name', 
                'last_name', 
                'phone',
                'city',
                'dob',
                'gender',
                'education',
                'occupation',
                'bio',
                'politics',
                'religion',
                'moderation_state',
                'suspension_ends_at',
                'moderation_reason',
                'created_at',
                'updated_at'
            ])->find($id);
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            return response()->json($user);
            
        } catch (\Exception $e) {
            Log::error('Admin getUser error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch user'], 500);
        }
    }
    
    /**
     * Moderate user (ban, suspend, unban, unsuspend)
     */
    public function moderateUser(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            $action = $request->input('action'); // ban, suspend, unban, unsuspend
            $reason = $request->input('reason');
            $expiresAt = $request->input('expiresAt');
            $adminEmail = $request->input('adminEmail');
            
            DB::beginTransaction();
            
            // Update user moderation state
            switch ($action) {
                case 'ban':
                    $user->moderation_state = 'banned';
                    $user->suspension_ends_at = null;
                    break;
                case 'suspend':
                    $user->moderation_state = 'suspended';
                    $user->suspension_ends_at = $expiresAt ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null;
                    break;
                case 'unban':
                case 'unsuspend':
                    $user->moderation_state = 'active';
                    $user->suspension_ends_at = null;
                    break;
                default:
                    return response()->json(['error' => 'Invalid action'], 400);
            }
            
            $user->moderation_reason = $reason;
            $user->save();
            
            // Log the action
            UserModerationAction::create([
                'user_id' => $id,
                'action' => $action,
                'reason' => $reason,
                'expires_at' => $action === 'suspend' ? $user->suspension_ends_at : null,
                'admin_email' => $adminEmail
            ]);
            
            DB::commit();
            
            Log::info("Admin action: {$action} on user {$id} by {$adminEmail}");
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($action) . ' action completed successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin moderateUser error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to moderate user'], 500);
        }
    }
    
    /**
     * Get moderation history
     */
    public function getModerationHistory(Request $request)
    {
        try {
            $perPage = 50;
            $userId = $request->get('user_id');
            $page = $request->get('page', 1);
            
            $query = UserModerationAction::select([
                'id',
                'user_id',
                'action',
                'reason',
                'expires_at',
                'admin_email',
                'created_at'
            ]);
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $history = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            return response()->json([
                'data' => $history->items(),
                'currentPage' => $history->currentPage(),
                'totalPages' => $history->lastPage(),
                'total' => $history->total()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Admin getModerationHistory error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch moderation history'], 500);
        }
    }
    
    /**
     * Get admin statistics
     */
    public function getStats()
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('moderation_state', 'active')->count();
            $suspendedUsers = User::where('moderation_state', 'suspended')->count();
            $bannedUsers = User::where('moderation_state', 'banned')->count();
            
            return response()->json([
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'suspendedUsers' => $suspendedUsers,
                'bannedUsers' => $bannedUsers
            ]);
            
        } catch (\Exception $e) {
            Log::error('Admin getStats error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetUserPassword(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            $newPassword = $request->input('password');
            $adminEmail = $request->input('adminEmail');
            
            if (!$newPassword || strlen($newPassword) < 6) {
                return response()->json(['error' => 'Password must be at least 6 characters long'], 400);
            }
            
            DB::beginTransaction();
            
            // Update password
            $user->password = Hash::make($newPassword);
            $user->save();
            
            // Log the action
            UserModerationAction::create([
                'user_id' => $id,
                'action' => 'password_reset',
                'reason' => 'Password reset by admin',
                'admin_email' => $adminEmail
            ]);
            
            DB::commit();
            
            Log::info("Admin password reset for user {$id} by {$adminEmail}");
            
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin resetUserPassword error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reset password'], 500);
        }
    }
    
    /**
     * Update user email
     */
    public function updateUserEmail(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            $newEmail = $request->input('email');
            $adminEmail = $request->input('adminEmail');
            
            if (!$newEmail || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Valid email address is required'], 400);
            }
            
            // Check if email already exists
            $existingUser = User::where('email', $newEmail)->where('id', '!=', $id)->first();
            if ($existingUser) {
                return response()->json(['error' => 'Email address already in use'], 400);
            }
            
            DB::beginTransaction();
            
            $oldEmail = $user->email;
            
            // Update email
            $user->email = $newEmail;
            $user->save();
            
            // Log the action
            UserModerationAction::create([
                'user_id' => $id,
                'action' => 'email_update',
                'reason' => "Email changed from {$oldEmail} to {$newEmail}",
                'admin_email' => $adminEmail
            ]);
            
            DB::commit();
            
            Log::info("Admin email update for user {$id} from {$oldEmail} to {$newEmail} by {$adminEmail}");
            
            return response()->json([
                'success' => true,
                'message' => 'Email updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin updateUserEmail error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update email'], 500);
        }
    }
}
