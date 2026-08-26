<?php

namespace App\Http\Controllers;

use App\Enums\LoanMigrationBatchStatus;
use App\Enums\LoanMigrationRecordValidationStatus;
use App\Models\LoanMigrationBatch;
use App\Models\LoanMigrationRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Member;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\LoanMigrationImportService;


class LoanMigrationController extends Controller
{

    protected $loanMigrationImportService;

    public function __construct(LoanMigrationImportService $loanMigrationImportService)
    {
        $this->loanMigrationImportService = $loanMigrationImportService;
    }
    /**
     * Display the Loan Migration dashboard.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        /*
         * Only authorized staff should access
         * the migration module.
         */
        abort_unless(
            $user && $this->canAccessMigration($user),
            403
        );

        $query = LoanMigrationBatch::query()
            ->with([
                'creator:id,name',
                'submitter:id,name',
                'verifier:id,name',
                'approver:id,name',
            ])
            ->latest();

        /*
         * Optional status filter.
         */
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        /*
         * Optional search.
         */
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $batches = $query
            ->paginate(15)
            ->withQueryString();

        /*
         * Dashboard statistics.
         */
        $statistics = [
            'total_batches' => LoanMigrationBatch::count(),

            'draft' => LoanMigrationBatch::where(
                'status',
                LoanMigrationBatchStatus::DRAFT->value
            )->count(),

            'validation_failed' => LoanMigrationBatch::where(
                'status',
                LoanMigrationBatchStatus::VALIDATION_FAILED->value
            )->count(),

            'submitted' => LoanMigrationBatch::where(
                'status',
                LoanMigrationBatchStatus::SUBMITTED->value
            )->count(),

            'accounts_verified' => LoanMigrationBatch::where(
                'status',
                LoanMigrationBatchStatus::ACCOUNTS_VERIFIED->value
            )->count(),

            'approved' => LoanMigrationBatch::where(
                'status',
                LoanMigrationBatchStatus::APPROVED->value
            )->count(),

            'processed' => LoanMigrationBatch::where(
                'status',
                LoanMigrationBatchStatus::PROCESSED->value
            )->count(),

            'total_records' => (int) LoanMigrationBatch::sum(
                'total_records'
            ),

            'total_outstanding_balance' => (float) LoanMigrationBatch::sum(
                'total_outstanding_balance'
            ),
        ];

        return Inertia::render('LoanMigration/Index', [
            'batches' => $batches,

            'statistics' => $statistics,

            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ],

            'statuses' => collect(
                LoanMigrationBatchStatus::cases()
            )->map(fn(LoanMigrationBatchStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])->values(),
        ]);
    }

    /**
     * Show the Create Migration Batch page.
     */
    public function create(): Response
    {
        $user = Auth::user();

        abort_unless(
            $user && $this->canCreateBatch($user),
            403
        );

        return Inertia::render('LoanMigration/Create', [
            'nextBatchNumber' => $this->generateNextBatchNumber(),
        ]);
    }

    /**
     * Store a new migration batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $user && $this->canCreateBatch($user),
            403
        );

        $validated = $request->validate([
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        /*
         * Generate the batch number inside a transaction
         * to reduce the possibility of duplicate numbers.
         */
        $batch = DB::transaction(function () use ($validated, $user) {

            $batchNumber = $this->generateNextBatchNumber();

            return LoanMigrationBatch::create([
                'batch_number' => $batchNumber,

                'description' => $validated['description'] ?? null,

                'status' => LoanMigrationBatchStatus::DRAFT,

                'created_by' => $user->id,

                'remarks' => $validated['remarks'] ?? null,

                'total_records' => 0,
                'valid_records' => 0,
                'invalid_records' => 0,
                'processed_records' => 0,

                'total_original_amount' => 0,
                'total_amount_paid' => 0,
                'total_outstanding_balance' => 0,
            ]);
        });

        return redirect()
            ->route('loan-migration.show', $batch)
            ->with(
                'success',
                "Migration batch {$batch->batch_number} created successfully."
            );
    }

    /**
     * Display a single migration batch.
     */
    public function show(LoanMigrationBatch $batch): Response
    {
        $user = Auth::user();

        abort_unless(
            $user && $this->canAccessMigration($user),
            403
        );

        $batch->load([
            'creator:id,name',
            'submitter:id,name',
            'verifier:id,name',
            'approver:id,name',
        ]);

        return Inertia::render('LoanMigration/Show', [
            'batch' => $batch,
        ]);
    }

    /**
     * Generate the next migration batch number.
     *
     * Example:
     *
     * MIG-2026-001
     * MIG-2026-002
     * MIG-2026-003
     */
    private function generateNextBatchNumber(): string
    {
        $year = now()->year;

        $prefix = "MIG-{$year}-";

        $lastBatch = LoanMigrationBatch::query()
            ->where('batch_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if (!$lastBatch) {
            return "{$prefix}001";
        }

        $lastNumber = (int) str_replace(
            $prefix,
            '',
            $lastBatch->batch_number
        );

        $nextNumber = $lastNumber + 1;

        return $prefix . str_pad(
            (string) $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Determine whether the user can access
     * the migration module.
     *
     * Members must never access migration.
     */
    private function canAccessMigration(User $user): bool
    {
        return $this->hasRole($user, [
            'admin',
            'Administrator',
            'Loans Officer',
            'Accounts Officer',
        ]);
    }
    /**
     * Determine whether the user can create
     * and capture migration records.
     */
    private function canCreateBatch($user): bool
    {
        return $this->hasRole($user, [
            'admin',
            'Administrator',
            'Loans Officer',
        ]);
    }

    /**
     * Check the application's user role.
     *
     * This supports the common role implementations
     * used by Laravel applications.
     */
    private function hasRole($user, array $roles): bool
    {
        /*
         * Spatie Laravel Permission.
         */
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        /*
         * Single role attribute.
         */
        if (isset($user->role)) {
            return in_array(
                $user->role,
                $roles,
                true
            );
        }

        /*
         * Role relationship.
         */
        if (
            method_exists($user, 'roles') &&
            $user->relationLoaded('roles')
        ) {
            return $user->roles
                ->pluck('name')
                ->intersect($roles)
                ->isNotEmpty();
        }

        return false;
    }

    public function importForm(LoanMigrationBatch $batch): Response
    {
        $user =  Auth::user();

        abort_unless($user && $this->canCreateBatch($user), 403);
        abort_unless($batch->isEditable(), 403);

        return Inertia::render('LoanMigration/Import', ['batch' => $batch]);
    }



    public function createRecord(LoanMigrationBatch $batch)
    {
        $user = Auth::user();

        abort_unless(
            $user && $this->canCreateBatch($user),
            403
        );

        $members = Member::query()
            ->select([
                'id',
                'membership_id',
                'first_name',
                'middle_name',
                'last_name',
                'emergency_contact_phone',
            ])
            ->orderBy('membership_id')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,

                    'member_number' => $member->membership_id,

                    'name' => trim(
                        $member->first_name . ' ' .
                            ($member->middle_name ?? '') . ' ' .
                            $member->last_name
                    ),

                    'phone' => $member->emergency_contact_phone,
                ];
            });

        $loanProducts = LoanProduct::query()
            ->select([
                'id',
                'name',
                'code',
                'interest_rate',
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('LoanMigration/Records/Create', [
            'batch' => $batch,
            'members' => $members,
            'loanProducts' => $loanProducts,
        ]);
    }


    /// Import the migration records from excel file
    public function import(Request $request, LoanMigrationBatch $batch): RedirectResponse
    {
        $user = Auth::user();
        $canCreateBatch = $this->canCreateBatch($user);

        return $this->loanMigrationImportService->import($request, $batch, $canCreateBatch);
       
    }
}
