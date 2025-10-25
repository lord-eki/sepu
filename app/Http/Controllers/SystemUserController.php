<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SystemUserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->whereIn('role', ['admin', 'loan_officer', 'accountant', 'management'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        // Filter by status
        if ($request->has('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => User::whereIn('role', ['admin', 'loan_officer', 'accountant', 'management'])->count(),
            'active' => User::whereIn('role', ['admin', 'loan_officer', 'accountant', 'management'])->where('is_active', true)->count(),
            'inactive' => User::whereIn('role', ['admin', 'loan_officer', 'accountant', 'management'])->where('is_active', false)->count(),
            'by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'loan_officer' => User::where('role', 'loan_officer')->count(),
                'accountant' => User::where('role', 'accountant')->count(),
                'management' => User::where('role', 'management')->count(),
            ],
        ];

        return Inertia::render('Admin/SystemUsers/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new system user.
     */
    public function create()
    {
        $roles = [
            'admin' => 'Administrator',
            'loan_officer' => 'Loan Officer',
            'accountant' => 'Accountant',
            'management' => 'Management',
        ];

        return Inertia::render('Admin/SystemUsers/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created system user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'loan_officer', 'accountant', 'management'])],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('system-users.index')
            ->with('success', 'System user created successfully.');
    }

    /**
     * Display the specified system user.
     */
    public function show(User $systemUser)
    {
        if ($systemUser->role === 'member') {
            abort(403, 'Unauthorized access.');
        }

        $systemUser->load(['loans', 'transactions' => function($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Admin/SystemUsers/Show', [
            'user' => $systemUser,
        ]);
    }

    /**
     * Show the form for editing the specified system user.
     */
    public function edit(User $systemUser)
    {
        if ($systemUser->role === 'member') {
            abort(403, 'Unauthorized access.');
        }

        $roles = [
            'admin' => 'Administrator',
            'loan_officer' => 'Loan Officer',
            'accountant' => 'Accountant',
            'management' => 'Management',
        ];

        return Inertia::render('Admin/SystemUsers/Edit', [
            'user' => $systemUser,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified system user.
     */
    public function update(Request $request, User $systemUser)
    {
        if ($systemUser->role === 'member') {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($systemUser->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($systemUser->id)],
            'role' => ['required', Rule::in(['admin', 'loan_officer', 'accountant', 'management'])],
            'is_active' => ['boolean'],
        ]);

        $systemUser->update($validated);

        return redirect()->route('system-users.index')
            ->with('success', 'System user updated successfully.');
    }

    /**
     * Update the password for the specified system user.
     */
    public function updatePassword(Request $request, User $systemUser)
    {
        if ($systemUser->role === 'member') {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $systemUser->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Toggle the active status of a system user.
     */
    public function toggleStatus(User $systemUser)
    {
        if ($systemUser->role === 'member') {
            abort(403, 'Unauthorized access.');
        }

        // Prevent admins from deactivating themselves
        if ($systemUser->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $systemUser->update([
            'is_active' => !$systemUser->is_active,
        ]);

        $status = $systemUser->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Remove the specified system user.
     */
    public function destroy(User $systemUser)
    {
        // Ensure we're only deleting system users, not regular members
        if ($systemUser->role === 'member') {
            abort(403, 'Unauthorized access.');
        }

        // Prevent admins from deleting themselves
        if ($systemUser->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has any critical associations
        if ($systemUser->loans()->exists() || $systemUser->transactions()->exists()) {
            return back()->with('error', 'Cannot delete user with existing loans or transactions.');
        }

        $systemUser->delete();

        return redirect()->route('system-users.index')
            ->with('success', 'System user deleted successfully.');
    }

    /**
     * Display roles and permissions management.
     */
    public function roles()
    {
        $roles = [
            [
                'name' => 'admin',
                'label' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => [
                    'Manage all users',
                    'Manage members',
                    'Approve loans',
                    'Manage accounts',
                    'Manage transactions',
                    'Generate reports',
                    'System configuration',
                    'Manage budgets',
                    'Manage vouchers',
                    'Manage dividends',
                ],
                'count' => User::where('role', 'admin')->count(),
            ],
            [
                'name' => 'management',
                'label' => 'Management',
                'description' => 'Strategic oversight and approval authority',
                'permissions' => [
                    'View all reports',
                    'Approve loans (final)',
                    'View financial summaries',
                    'Approve budgets',
                    'View member data',
                    'Approve dividends',
                ],
                'count' => User::where('role', 'management')->count(),
            ],
            [
                'name' => 'loan_officer',
                'label' => 'Loan Officer',
                'description' => 'Manages loan applications and approvals',
                'permissions' => [
                    'View loan applications',
                    'Approve/reject loans',
                    'Manage loan products',
                    'View member loans',
                    'Generate loan reports',
                    'Calculate loan payments',
                ],
                'count' => User::where('role', 'loan_officer')->count(),
            ],
            [
                'name' => 'accountant',
                'label' => 'Accountant',
                'description' => 'Handles financial transactions and accounting',
                'permissions' => [
                    'Record transactions',
                    'Manage accounts',
                    'Process vouchers',
                    'Generate financial reports',
                    'Manage budgets',
                    'Process dividends',
                    'View member accounts',
                ],
                'count' => User::where('role', 'accountant')->count(),
            ],
        ];

        $totalSystemUsers = User::whereIn('role', ['admin', 'loan_officer', 'accountant', 'management'])->count();
        $activeUsers = User::whereIn('role', ['admin', 'loan_officer', 'accountant', 'management'])
            ->where('is_active', true)
            ->count();

        return Inertia::render('Admin/SystemUsers/Roles', [
            'roles' => $roles,
            'stats' => [
                'total_system_users' => $totalSystemUsers,
                'active_users' => $activeUsers,
            ],
        ]);
    }

    /**
     * Bulk actions for system users.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->where('role', '!=', 'member')
            ->where('id', '!=', auth()->id()) // Exclude current user
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'No valid users selected.');
        }

        switch ($validated['action']) {
            case 'activate':
                $users->each->update(['is_active' => true]);
                $message = 'Users activated successfully.';
                break;
            case 'deactivate':
                $users->each->update(['is_active' => false]);
                $message = 'Users deactivated successfully.';
                break;
            case 'delete':
                // Only delete users without critical associations
                $deletableUsers = $users->filter(function($user) {
                    return !$user->loans()->exists() && !$user->transactions()->exists();
                });
                $deletableUsers->each->delete();
                $message = "{$deletableUsers->count()} user(s) deleted successfully.";
                break;
        }

        return back()->with('success', $message);
    }
}