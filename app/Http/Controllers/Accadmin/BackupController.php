<?php

namespace App\Http\Controllers\Accadmin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function store(DatabaseBackupService $backups): RedirectResponse
    {
        try {
            $backups->create();
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('accadmin.dashboard')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('accadmin.dashboard')
            ->with('success', 'Database backup created.');
    }

    public function download(string $filename, DatabaseBackupService $backups): BinaryFileResponse
    {
        $path = $backups->pathForDownload($filename);

        return response()->download($path, $filename);
    }
}
