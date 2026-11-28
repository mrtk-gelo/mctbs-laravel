<?php

namespace App\Http\Controllers\Accadmin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(DatabaseBackupService $backups): View
    {
        return view('accadmin.backups', [
            'backups' => $backups->recent(),
        ]);
    }

    public function store(DatabaseBackupService $backups): RedirectResponse
    {
        try {
            $backups->create();
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('accadmin.backups.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('accadmin.backups.index')
            ->with('success', 'Database backup created.');
    }

    public function download(string $filename, DatabaseBackupService $backups): BinaryFileResponse
    {
        $path = $backups->pathForDownload($filename);

        return response()->download($path, $filename);
    }
}
