@extends('layouts.base')

@section('title', 'Respaldos cifrados')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Respaldos cifrados</h1>
        <p class="text-muted mb-0">Proteja todos los usuarios y registros para recuperarlos incluso después de formatear el PC.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><strong>No se pudo completar la operación.</strong> {{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="copropietario-detail-header-icon"><i class="fas fa-download"></i></span>
                        <div>
                            <h2 class="h5 mb-1">Exportar respaldo</h2>
                            <p class="text-muted small mb-0">Genere un archivo portátil completamente cifrado.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.backups.export') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="export_current_password">Contraseña actual del administrador</label>
                            <input class="form-control" id="export_current_password" name="current_password" type="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="backup_password">Contraseña del respaldo</label>
                            <input class="form-control" id="backup_password" name="backup_password" type="password" minlength="12" required>
                            <div class="form-text">Mínimo 12 caracteres. Será necesaria para recuperar los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="backup_password_confirmation">Confirmar contraseña del respaldo</label>
                            <input class="form-control" id="backup_password_confirmation" name="backup_password_confirmation" type="password" required>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-download me-2"></i>Descargar respaldo</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100 shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="copropietario-detail-header-icon"><i class="fas fa-upload"></i></span>
                        <div>
                            <h2 class="h5 mb-1">Restaurar respaldo</h2>
                            <p class="text-muted small mb-0">Reemplaza los datos actuales por los del archivo.</p>
                        </div>
                    </div>
                    <div class="alert alert-warning small">
                        La restauración eliminará los registros actuales. Guarde primero un respaldo si necesita conservarlos.
                    </div>
                    <form method="POST" action="{{ route('admin.backups.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="backup_file">Archivo .rpebackup</label>
                            <input class="form-control" id="backup_file" name="backup_file" type="file" accept=".rpebackup" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="import_backup_password">Contraseña del respaldo</label>
                            <input class="form-control" id="import_backup_password" name="backup_password" type="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="import_current_password">Contraseña actual del administrador</label>
                            <input class="form-control" id="import_current_password" name="current_password" type="password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" id="confirm_replace" name="confirm_replace" type="checkbox" value="1" required>
                            <label class="form-check-label" for="confirm_replace">Entiendo que se reemplazarán todos los datos actuales.</label>
                        </div>
                        <button class="btn btn-danger" type="submit" onclick="return confirm('¿Restaurar este respaldo y reemplazar todos los datos actuales?')">
                            <i class="fas fa-rotate-left me-2"></i>Restaurar datos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4 mb-0">
        <i class="fas fa-lock me-2"></i>
        El archivo usa Argon2id y XChaCha20-Poly1305. Sin su contraseña no puede leerse ni recuperarse.
    </div>
@endsection
