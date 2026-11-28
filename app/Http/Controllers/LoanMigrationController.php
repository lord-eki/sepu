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
use Closure;
use DomainException;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\LoanMigrationDecisionRequest;
use App\Http\Requests\RejectLoanMigrationBatchRequest;
use App\Services\LoanMigration\LoanMigrationProcessor;
use App\Services\LoanMigration\LoanMigrationWorkflow;
use App\Http\Requests\ImportLoanMigrationRequest;
use App\Http\Requests\LoanMigrationRecordFilterRequest;
use App\Http\Requests\StoreLoanMigrationRecordRequest;
use App\Http\Requests\UpdateLoanMigrationRecordRequest;
use App\Services\LoanMigration\LoanMigrationRecordService;
use App\Services\LoanMigration\LoanMigrationSpreadsheet;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;



class LoanMigrationController extends Controller
{

    protected LoanMigrationImportService $loanMigrationImportService;

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

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

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
\         */
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


    // Tells whether the user may open the migration module.
    private function canAccessMigration(User $user): bool
    {
        return Gate::forUser($user)->allows('access', LoanMigrationBatch::class);
    }

    // Tells whether the user may capture and import migration records.
    private function canCreateBatch(User $user): bool
    {
        return Gate::forUser($user)->allows('capture', LoanMigrationBatch::class);
    }


    // Re-checks every record in the batch and reports how many are valid.
    public function validateBatch(LoanMigrationBatch $batch, LoanMigrationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('capture', $batch);

        return $this->runStep(function () use ($batch, $workflow) {
            $result = $workflow->validate($batch, Auth::user());

            return "Validation finished: {$result->valid_records} valid, {$result->invalid_records} invalid.";
        });
    }

    // Lists the batch's invalid records with their error messages.
    public function validationErrors(LoanMigrationBatch $batch): Response
    {
        Gate::authorize('access', LoanMigrationBatch::class);

        $records = $batch->records()
            ->where('validation_status', LoanMigrationRecordValidationStatus::INVALID->value)
            ->orderBy('id')
            ->paginate(25)
            ->through(fn($record) => [
                'id' => $record->id,
                'loan_number' => $record->loan_number,
                'member_number' => $record->member_number,
                'errors' => $record->validation_errors,
            ]);

        return Inertia::render('LoanMigration/Records/Validation', [
            'batch' => $batch,
            'records' => $records,
        ]);
    }

    // Submits a validated batch for Accounts Officer verification.
    public function submit(LoanMigrationDecisionRequest $request, LoanMigrationBatch $batch, LoanMigrationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('capture', $batch);

        return $this->runStep(function () use ($request, $batch, $workflow) {
            $workflow->submit($batch, Auth::user(), $request->validated('remarks'));

            return 'The batch was submitted for Accounts Officer verification.';
        });
    }

    // Records the Accounts Officer's verification of the batch totals.
    public function verifyAccounts(LoanMigrationDecisionRequest $request, LoanMigrationBatch $batch, LoanMigrationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('verify', $batch);

        return $this->runStep(function () use ($request, $batch, $workflow) {
            $workflow->verifyAccounts($batch, Auth::user(), $request->validated('remarks'));

            return 'The batch accounts were verified.';
        });
    }

    // Approves a batch the Accounts Officer has verified.
    public function approve(LoanMigrationDecisionRequest $request, LoanMigrationBatch $batch, LoanMigrationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('approve', $batch);

        return $this->runStep(function () use ($request, $batch, $workflow) {
            $workflow->approve($batch, Auth::user(), $request->validated('remarks'));

            return 'The batch was approved.';
        });
    }

    // Turns a submitted or verified batch down with a reason.
    public function reject(RejectLoanMigrationBatchRequest $request, LoanMigrationBatch $batch, LoanMigrationWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('reject', $batch);

        return $this->runStep(function () use ($request, $batch, $workflow) {
            $workflow->reject($batch, Auth::user(), $request->validated('reason'));

            return 'The batch was rejected.';
        });
    }

    // Runs a workflow step and returns its success message, or the domain error it raised.
    private function runStep(Closure $step): RedirectResponse
    {
        try {
            $message = $step();
        } catch (DomainException $e) {
            return back()->withErrors(['batch' => $e->getMessage()]);
        }

        return back()->with('success', $message);
    }

    /**
     * Check the application's user role.
     */
    private function hasRole($user, array $roles): bool
    {

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        if (isset($user->role)) {
            return in_array(
                $user->role,
                $roles,
                true
            );
        }

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

    // Adds a manually entered record to an editable batch.
    public function storeRecord(StoreLoanMigrationRecordRequest $request, LoanMigrationBatch $batch, LoanMigrationRecordService $records): RedirectResponse
    {
        Gate::authorize('capture', $batch);

        return $this->runStep(function () use ($request, $batch, $records) {
            $record = $records->create($batch, $request->validated(), Auth::user());

            return "Record {$record->loan_number} was added.";
        });
    }

    // Lists a batch's records with their validation and processing results.
    public function records(LoanMigrationRecordFilterRequest $request, LoanMigrationBatch $batch): Response
    {
        Gate::authorize('access', LoanMigrationBatch::class);

        $filters = $request->validated();

        $records = $batch->records()
            ->with('loanProduct:id,name,code')
            ->when($filters['validation'] ?? null, fn($query, $status) => $query->where('validation_status', $status))
            ->when($filters['processing'] ?? null, fn($query, $status) => $query->where('processing_status', $status))
            ->when($filters['search'] ?? null, fn($query, $term) => $query->where(
                fn($match) => $match->where('loan_number', 'like', "%{$term}%")->orWhere('member_number', 'like', "%{$term}%")
            ))
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('LoanMigration/Records/Index', [
            'batch' => $batch,
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    // Shows the edit form for one record of an editable batch.
    public function editRecord(LoanMigrationBatch $batch, LoanMigrationRecord $record): Response|RedirectResponse
    {
        Gate::authorize('capture', $batch);

        abort_unless((int) $record->batch_id === (int) $batch->id, 404);

        if (! $batch->isEditable()) {
            return back()->withErrors(['batch' => 'Records can only be changed while the batch is a draft or has failed validation.']);
        }

        return Inertia::render('LoanMigration/Records/Edit', array_merge($this->recordFormOptions(), [
            'batch' => $batch,
            'record' => $record,
        ]));
    }

    // Saves changes to a record of an editable batch.
    public function updateRecord(UpdateLoanMigrationRecordRequest $request, LoanMigrationBatch $batch, LoanMigrationRecord $record, LoanMigrationRecordService $records): RedirectResponse
    {
        Gate::authorize('capture', $batch);

        return $this->runStep(function () use ($request, $batch, $record, $records) {
            $records->update($batch, $record, $request->validated(), Auth::user());

            return 'The record was updated.';
        });
    }

    // Removes a record from an editable batch.
    public function destroyRecord(LoanMigrationBatch $batch, LoanMigrationRecord $record, LoanMigrationRecordService $records): RedirectResponse
    {
        Gate::authorize('capture', $batch);

        return $this->runStep(function () use ($batch, $record, $records) {
            $records->delete($batch, $record, Auth::user());

            return 'The record was removed.';
        });
    }

    // Reports the problems in an uploaded spreadsheet without saving anything.
    public function previewImport(ImportLoanMigrationRequest $request, LoanMigrationBatch $batch, LoanMigrationImportService $imports): JsonResponse
    {
        Gate::authorize('capture', $batch);

        return response()->json($imports->preview($request->file('file'), $batch));
    }

    // Downloads the spreadsheet template for importing legacy loans.
    public function downloadTemplate(LoanMigrationSpreadsheet $spreadsheet): StreamedResponse
    {
        Gate::authorize('access', LoanMigrationBatch::class);

        return $spreadsheet->template();
    }

    // Downloads a batch's records with their validation and processing results.
    public function export(LoanMigrationBatch $batch, LoanMigrationSpreadsheet $spreadsheet): StreamedResponse
    {
        Gate::authorize('access', LoanMigrationBatch::class);

        return $spreadsheet->export($batch);
    }

    // Returns the members and loan products offered by the record form.
    private function recordFormOptions(): array
    {
        return [
            'members' => Member::query()
                ->orderBy('membership_id')
                ->get(['id', 'membership_id', 'first_name', 'middle_name', 'last_name'])
                ->map(fn($member) => [
                    'id' => $member->id,
                    'member_number' => $member->membership_id,
                    'name' => trim("{$member->first_name} {$member->middle_name} {$member->last_name}"),
                ]),
            'loanProducts' => LoanProduct::query()->orderBy('name')->get(['id', 'name', 'code', 'interest_rate']),
        ];
    }

    /// Import the migration records from excel file
    public function import(ImportLoanMigrationRequest $request, LoanMigrationBatch $batch): RedirectResponse
    {
        $user = Auth::user();
        $canCreateBatch = $this->canCreateBatch($user);

        return $this->loanMigrationImportService->import($request, $batch, $canCreateBatch);
    }

    // Creates the loans, schedules and opening ledger entries for an approved batch.
    public function process(LoanMigrationBatch $batch, LoanMigrationProcessor $processor): RedirectResponse
    {
        Gate::authorize('process', $batch);

        return $this->runStep(function () use ($batch, $processor) {
            $result = $processor->process($batch, Auth::user());
            $message = "{$result['created']} loan(s) created.";

            return $result['failed'] === []
                ? $message
                : $message . ' Failed: ' . implode(', ', $result['failed']) . '. Fix them and process again.';
        });
    }
}
