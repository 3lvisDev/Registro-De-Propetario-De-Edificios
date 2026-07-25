<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PortableBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('admin.backups.index');
    }

    public function export(Request $request, PortableBackupService $backups): StreamedResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'backup_password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $contents = $backups->export($validated['backup_password']);
        $filename = 'registro-propietarios-'.now()->format('Y-m-d-His').'.rpebackup';

        return response()->streamDownload(
            fn () => print($contents),
            $filename,
            ['Content-Type' => 'application/octet-stream'],
        );
    }

    public function import(Request $request, PortableBackupService $backups): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'backup_password' => ['required', 'string'],
            'backup_file' => ['required', 'file', 'max:51200'],
            'confirm_replace' => ['accepted'],
        ]);

        try {
            $contents = $validated['backup_file']->get();
            $backups->import($contents, $validated['backup_password']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['backup_file' => $exception->getMessage()]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Respaldo restaurado correctamente. Inicie sesión con una cuenta del respaldo.');
    }
}
