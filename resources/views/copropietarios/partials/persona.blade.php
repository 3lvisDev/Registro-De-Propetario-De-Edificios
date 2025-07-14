<div class="border rounded p-3 mb-3">
    <input type="hidden" name="copropietarios[{{ $index }}][tipo]" value="{{ $tipo }}">

    <h6 class="text-muted mb-3">{{ ucfirst($tipo) }} {{ (int) $index + 1 }}</h6>

    <div class="form-group mb-2">
        <label>Nombre Completo</label>
        <input type="text" name="copropietarios[{{ $index }}][nombre_completo]" class="form-control" required>
    </div>

    <div class="form-group mb-2">
        <label>Teléfono</label>
        <input type="text" name="copropietarios[{{ $index }}][telefono]" class="form-control">
    </div>

    <div class="form-group mb-2">
        <label>Correo</label>
        <input type="email" name="copropietarios[{{ $index }}][correo]" class="form-control">
    </div>

    <div class="form-group mb-2">
        <label>Patente</label>
        <input type="text" name="copropietarios[{{ $index }}][patente]" class="form-control">
    </div>
</div>

