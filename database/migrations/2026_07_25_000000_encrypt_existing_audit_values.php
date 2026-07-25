<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('audit_logs')
            ->orderBy('id')
            ->chunkById(100, function ($logs): void {
                foreach ($logs as $log) {
                    DB::table('audit_logs')->where('id', $log->id)->update([
                        'old_values' => $this->encryptIfPresent($log->old_values),
                        'new_values' => $this->encryptIfPresent($log->new_values),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('audit_logs')
            ->orderBy('id')
            ->chunkById(100, function ($logs): void {
                foreach ($logs as $log) {
                    DB::table('audit_logs')->where('id', $log->id)->update([
                        'old_values' => $this->decryptIfPresent($log->old_values),
                        'new_values' => $this->decryptIfPresent($log->new_values),
                    ]);
                }
            });
    }

    private function encryptIfPresent(?string $value): ?string
    {
        return $value === null ? null : Crypt::encryptString($value);
    }

    private function decryptIfPresent(?string $value): ?string
    {
        return $value === null ? null : Crypt::decryptString($value);
    }
};
