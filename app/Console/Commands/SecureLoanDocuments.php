<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureLoanDocuments extends Command
{
    protected $signature = 'sepu:secure-loan-documents';
    protected $description = 'Move existing loan documents from the public disk to the private disk';

    public function handle(): int
    {
        $moved = 0;

        Loan::whereNotNull('documents')->chunkById(100, function ($loans) use (&$moved) {
            foreach ($loans as $loan) {
                $docs = $loan->documents ?? [];
                $changed = false;

                foreach ($docs as &$doc) {
                    $path = $doc['path'] ?? null;
                    if (!$path || !str_starts_with($path, 'loan-documents/')) {
                        continue;
                    }

                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('local')->writeStream($path, Storage::disk('public')->readStream($path));
                        Storage::disk('public')->delete($path);
                        $moved++;
                    }

                    if (isset($doc['url'])) {
                        unset($doc['url']);
                        $doc['disk'] = 'local';
                        $changed = true;
                    }
                }
                unset($doc);

                if ($changed) {
                    $loan->documents = $docs;
                    $loan->saveQuietly();
                }
            }
        });

        $this->info("Moved {$moved} file(s) to private storage.");
        return self::SUCCESS;
    }
}