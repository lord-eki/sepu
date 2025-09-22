<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait HasRolePermissions
{
    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array(auth()->user()?->role, $roles);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return auth()->user()?->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is management
     */
    public function isManagement(): bool
    {
        return $this->hasRole('management');
    }

    /**
     * Check if user is accountant
     */
    public function isAccountant(): bool
    {
        return $this->hasRole('accountant');
    }

    /**
     * Check if user is loan officer
     */
    public function isLoanOfficer(): bool
    {
        return $this->hasRole('loan_officer');
    }

    /**
     * Check if user is member
     */
    public function isMember(): bool
    {
        return $this->hasRole('member');
    }

    /**
     * Check if user is staff (non-member)
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'accountant', 'loan_officer']);
    }

    /**
     * Check if user can manage members
     */
    public function canManageMembers(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'loan_officer']);
    }

    /**
     * Check if user can manage loans
     */
    public function canManageLoans(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'loan_officer']);
    }

    /**
     * Check if user can manage finances
     */
    public function canManageFinances(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'accountant']);
    }

    /**
     * Check if user can approve loans
     */
    public function canApproveLoans(): bool
    {
        return $this->hasAnyRole(['admin', 'management']);
    }

    /**
     * Check if user can disburse loans
     */
    public function canDisburseLoans(): bool
    {
        return $this->hasAnyRole(['admin', 'accountant']);
    }

    /**
     * Check if user can manage system settings
     */
    public function canManageSettings(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user can view financial reports
     */
    public function canViewFinancialReports(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'accountant']);
    }

    /**
     * Check if user can manage budgets
     */
    public function canManageBudgets(): bool
    {
        return $this->hasAnyRole(['admin', 'management']);
    }

    /**
     * Check if user can manage vouchers
     */
    public function canManageVouchers(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'accountant']);
    }

    /**
     * Check if user can approve vouchers
     */
    public function canApproveVouchers(): bool
    {
        return $this->hasAnyRole(['admin', 'management']);
    }

    /**
     * Check if user owns the resource (for members)
     */
    public function ownsResource($resource): bool
    {
        if (!$this->isMember()) {
            return false;
        }

        $member = auth()->user()->member;
        
        if (!$member) {
            return false;
        }

        // Check different resource types
        if (method_exists($resource, 'member_id')) {
            return $resource->member_id === $member->id;
        }

        if (method_exists($resource, 'member') && $resource->member) {
            return $resource->member->id === $member->id;
        }

        return false;
    }

    /**
     * Check if user can access member data
     */
    public function canAccessMemberData($member): bool
    {
        // Staff can access any member data
        if ($this->isStaff()) {
            return true;
        }

        // Members can only access their own data
        if ($this->isMember()) {
            return auth()->user()->member?->id === $member->id;
        }

        return false;
    }

    /**
     * Get role hierarchy level (higher number = more permissions)
     */
    public function getRoleLevel(): int
    {
        return match(auth()->user()?->role) {
            'admin' => 5,
            'management' => 4,
            'accountant' => 3,
            'loan_officer' => 2,
            'member' => 1,
            default => 0
        };
    }

    /**
     * Check if user has higher or equal role level
     */
    public function hasRoleLevel(int $level): bool
    {
        return $this->getRoleLevel() >= $level;
    }

    /**
     * Filter query based on user role
     */
    public function filterByRole($query, string $memberColumn = 'member_id')
    {
        // If user is staff, return all records
        if ($this->isStaff()) {
            return $query;
        }

        // If user is member, filter to their own records
        if ($this->isMember() && auth()->user()->member) {
            return $query->where($memberColumn, auth()->user()->member->id);
        }

        // If no member relationship, return empty collection
        return $query->whereRaw('1 = 0');
    }

    /**
     * Get accessible member IDs for current user
     */
    public function getAccessibleMemberIds(): array
    {
        // Staff can access all members
        if ($this->isStaff()) {
            return \App\Models\Member::pluck('id')->toArray();
        }

        // Members can only access their own data
        if ($this->isMember() && auth()->user()->member) {
            return [auth()->user()->member->id];
        }

        return [];
    }

    /**
     * Check transaction permissions based on amount
     */
    public function canProcessTransaction(float $amount): bool
    {
        return match(auth()->user()?->role) {
            'admin' => true,
            'management' => $amount <= 1000000, // 1M limit for management
            'accountant' => $amount <= 500000, // 500K limit for accountant
            'loan_officer' => $amount <= 100000, // 100K limit for loan officer
            default => false
        };
    }

    /**
     * Check loan approval permissions based on amount
     */
    public function canApproveLoan(float $amount): bool
    {
        return match(auth()->user()?->role) {
            'admin' => true,
            'management' => $amount <= 2000000, // 2M limit for management
            default => false
        };
    }

    /**
     * Get role-specific dashboard stats filter
     */
    public function getDashboardStatsFilter(): array
    {
        if ($this->isMember() && auth()->user()->member) {
            return ['member_id' => auth()->user()->member->id];
        }

        return []; // Staff can see all stats
    }

    /**
     * Check if user can perform bulk operations
     */
    public function canPerformBulkOperations(): bool
    {
        return $this->hasAnyRole(['admin', 'management']);
    }

    /**
     * Check if user can delete records
     */
    public function canDeleteRecords(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user can export data
     */
    public function canExportData(): bool
    {
        return $this->hasAnyRole(['admin', 'management', 'accountant']);
    }

    /**
     * Check if user can import data
     */
    public function canImportData(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Get role-specific navigation items
     */
    public function getNavigationItems(): array
    {
        $role = auth()->user()?->role;

        $navigation = [
            'dashboard' => true,
            'notifications' => true,
        ];

        switch ($role) {
            case 'admin':
                $navigation = array_merge($navigation, [
                    'members' => true,
                    'accounts' => true,
                    'transactions' => true,
                    'loans' => true,
                    'dividends' => true,
                    'budgets' => true,
                    'vouchers' => true,
                    'reports' => true,
                    'settings' => true,
                ]);
                break;

            case 'management':
                $navigation = array_merge($navigation, [
                    'members' => true,
                    'accounts' => true,
                    'transactions' => true,
                    'loans' => true,
                    'dividends' => true,
                    'budgets' => true,
                    'vouchers' => true,
                    'reports' => true,
                ]);
                break;

            case 'accountant':
                $navigation = array_merge($navigation, [
                    'members' => true,
                    'accounts' => true,
                    'transactions' => true,
                    'vouchers' => true,
                    'reports' => ['financial', 'transactions'],
                ]);
                break;

            case 'loan_officer':
                $navigation = array_merge($navigation, [
                    'members' => true,
                    'loans' => true,
                    'reports' => ['loans', 'members'],
                ]);
                break;

            case 'member':
                $navigation = array_merge($navigation, [
                    'my_profile' => true,
                    'my_accounts' => true,
                    'my_transactions' => true,
                    'my_loans' => true,
                    'my_dividends' => true,
                    'loan_calculator' => true,
                ]);
                break;
        }

        return $navigation;
    }
}