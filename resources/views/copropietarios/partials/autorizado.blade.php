<div class="border rounded p-3 mb-3">
    <h6 class="text-muted mb-3">Autorizado {{ (int) $index + 1 }}</h6>

    <div class="form-group mb-2">
        <label>Nombre Completo</label>
        <input type="text" name="autorizados[{{ $index }}][nombre_completo]" class="form-control" required>
    </div>

    <div class="form-group mb-2">
        <label>RUT o Pasaporte</label>
        <input type="text" name="autorizados[{{ $index }}][rut_pasaporte]" class="form-control" required>
    </div>

    <div class="form-group mb-2">
        <label>Patente Vehículo</label>
        <input type="text" name="autorizados[{{ $index }}][patente]" class="form-control">
    </div>
</div>

