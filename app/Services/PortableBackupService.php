<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PortableBackupService
{
    private const FORMAT = 'RPEBACKUP';

    private const VERSION = 1;

    public function export(string $password): string
    {
        $payload = json_encode([
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'created_at' => now()->toIso8601String(),
            'application' => config('app.name'),
            'tables' => [
                'users' => DB::table('users')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
                'copropietarios' => Copropietario::query()->orderBy('id')->get()->toArray(),
                'persona_autorizadas' => PersonaAutorizada::query()->orderBy('id')->get()->toArray(),
                'audit_logs' => AuditLog::query()->orderBy('id')->get()->toArray(),
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $key = $this->deriveKey($password, $salt);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $payload,
            self::FORMAT.':'.self::VERSION,
            $nonce,
            $key,
        );
        sodium_memzero($key);

        return json_encode([
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'kdf' => 'argon2id',
            'cipher' => 'xchacha20-poly1305',
            'salt' => base64_encode($salt),
            'nonce' => base64_encode($nonce),
            'data' => base64_encode($ciphertext),
        ], JSON_THROW_ON_ERROR);
    }

    public function import(string $encryptedBackup, string $password): void
    {
        try {
            $envelope = json_decode($encryptedBackup, true, 16, JSON_THROW_ON_ERROR);
            $this->validateEnvelope($envelope);

            $salt = base64_decode($envelope['salt'], true);
            $nonce = base64_decode($envelope['nonce'], true);
            $ciphertext = base64_decode($envelope['data'], true);
            if ($salt === false || $nonce === false || $ciphertext === false) {
                throw new RuntimeException('El archivo de respaldo no es válido.');
            }

            $key = $this->deriveKey($password, $salt);
            $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
                $ciphertext,
                self::FORMAT.':'.self::VERSION,
                $nonce,
                $key,
            );
            sodium_memzero($key);
            if ($plaintext === false) {
                throw new RuntimeException('Contraseña incorrecta o respaldo alterado.');
            }

            $payload = json_decode($plaintext, true, 64, JSON_THROW_ON_ERROR);
            $this->validatePayload($payload);
            $this->restoreTables($payload['tables']);
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new RuntimeException('No se pudo leer el respaldo. Compruebe el archivo y la contraseña.');
        }
    }

    private function deriveKey(string $password, string $salt): string
    {
        return sodium_crypto_pwhash(
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES,
            $password,
            $salt,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13,
        );
    }

    private function validateEnvelope(mixed $envelope): void
    {
        if (! is_array($envelope)
            || ($envelope['format'] ?? null) !== self::FORMAT
            || ($envelope['version'] ?? null) !== self::VERSION
            || ($envelope['kdf'] ?? null) !== 'argon2id'
            || ($envelope['cipher'] ?? null) !== 'xchacha20-poly1305'
            || ! isset($envelope['salt'], $envelope['nonce'], $envelope['data'])) {
            throw new RuntimeException('El archivo no es un respaldo compatible.');
        }
    }

    private function validatePayload(mixed $payload): void
    {
        $tables = $payload['tables'] ?? null;
        if (($payload['format'] ?? null) !== self::FORMAT
            || ($payload['version'] ?? null) !== self::VERSION
            || ! is_array($tables)) {
            throw new RuntimeException('El contenido del respaldo no es compatible.');
        }

        foreach (['users', 'copropietarios', 'persona_autorizadas', 'audit_logs'] as $table) {
            if (! isset($tables[$table]) || ! is_array($tables[$table])) {
                throw new RuntimeException('El respaldo está incompleto.');
            }
        }

        if (! collect($tables['users'])->contains(fn ($user) => (bool) ($user['is_admin'] ?? false))) {
            throw new RuntimeException('El respaldo no contiene una cuenta administradora.');
        }
    }

    private function restoreTables(array $tables): void
    {
        DB::transaction(function () use ($tables): void {
            DB::table('audit_logs')->delete();
            DB::table('persona_autorizadas')->delete();
            DB::table('copropietarios')->delete();
            DB::table('personal_access_tokens')->delete();
            DB::table('password_reset_tokens')->delete();
            DB::table('users')->delete();

            foreach ($tables['users'] as $attributes) {
                DB::table('users')->insert([
                    'id' => $attributes['id'],
                    'name' => $attributes['name'],
                    'email' => $attributes['email'],
                    'email_verified_at' => $attributes['email_verified_at'] ?? null,
                    'password' => $attributes['password'],
                    'remember_token' => null,
                    'is_admin' => (bool) ($attributes['is_admin'] ?? false),
                    'created_at' => $attributes['created_at'] ?? now(),
                    'updated_at' => $attributes['updated_at'] ?? now(),
                ]);
            }

            foreach ($tables['copropietarios'] as $attributes) {
                $model = new Copropietario;
                $model->id = $attributes['id'];
                $model->forceFill(collect($attributes)->except(['id', 'created_at', 'updated_at'])->all());
                $model->created_at = $attributes['created_at'] ?? now();
                $model->updated_at = $attributes['updated_at'] ?? now();
                $model->save();
            }

            foreach ($tables['persona_autorizadas'] as $attributes) {
                $model = new PersonaAutorizada;
                $model->id = $attributes['id'];
                $model->forceFill(collect($attributes)->except(['id', 'created_at', 'updated_at'])->all());
                $model->created_at = $attributes['created_at'] ?? now();
                $model->updated_at = $attributes['updated_at'] ?? now();
                $model->save();
            }

            foreach ($tables['audit_logs'] as $attributes) {
                $model = new AuditLog;
                $model->id = $attributes['id'];
                $model->forceFill(collect($attributes)->except('id')->all());
                $model->save();
            }
        }, 3);
    }
}
