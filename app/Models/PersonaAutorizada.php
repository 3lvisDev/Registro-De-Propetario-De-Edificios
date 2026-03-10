<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PersonaAutorizada
 * 
 * Representa a una persona autorizada para acceder al edificio, asociada a un departamento.
 * 
 * CONVENCIONES DE NOMBRES:
 * - Nombres de campos en base de datos: snake_case (ej: nombre_completo, rut_pasaporte)
 * - Propiedades del modelo: snake_case (Laravel convierte automáticamente)
 * - Métodos del modelo: camelCase (ej: getCopropietarioAttribute)
 * - Nombres de tablas: plural en snake_case (ej: persona_autorizadas)
 * 
 * CAMPOS PRINCIPALES:
 * - nombre_completo: Nombre completo de la persona autorizada (mínimo 3 caracteres)
 * - rut_pasaporte: RUT o número de pasaporte (requerido) - CAMPO ESTANDARIZADO
 * - departamento: Número del departamento asociado (requerido)
 * - patente: Patente del vehículo (opcional)
 * 
 * NOTA IMPORTANTE SOBRE CAMPO rut_pasaporte:
 * Este campo fue estandarizado en toda la aplicación para mantener consistencia.
 * Anteriormente podría haber tenido nombres diferentes (rut, pasaporte, etc.).
 * Ahora se usa 'rut_pasaporte' en:
 * - Migración de base de datos
 * - Modelo (fillable)
 * - Reglas de validación (FormRequest)
 * - Vistas Blade (formularios y displays)
 * 
 * RELACIONES:
 * - copropietario_id: Clave foránea al copropietario asociado
 * 
 * @property int $id
 * @property string $nombre_completo
 * @property string $rut_pasaporte
 * @property string $departamento
 * @property string|null $patente
 * @property int|null $copropietario_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PersonaAutorizada extends Model
{
    use HasFactory, \App\Traits\EncryptsAttributes;

    /**
     * Campos asignables masivamente.
     * 
     * IMPORTANTE: Solo estos campos pueden ser asignados mediante create() o fill().
     * Los campos id, created_at, updated_at están protegidos automáticamente.
     * 
     * NOTA: El campo 'rut_pasaporte' es el nombre estandarizado para RUT o pasaporte.
     * 
     * Campos permitidos según Requisitos 20.2 y 20.5:
     * - nombre_completo: Nombre completo de la persona autorizada
     * - rut_pasaporte: RUT o pasaporte (estandarizado)
     * - departamento: Número del departamento asociado
     * - patente: Patente del vehículo (opcional)
     * - copropietario_id: ID del copropietario asociado
     * 
     * Campos explícitamente excluidos: id, created_at, updated_at
     * 
     * @var array<string>
     */
    protected $fillable = [
        'nombre_completo',
        'rut_pasaporte',
        'departamento',
        'patente',
        'copropietario_id',
    ];

    /**
     * Campos que deben ser encriptados en la base de datos.
     * 
     * SEGURIDAD: Estos campos se encriptan automáticamente antes de guardar
     * y se desencriptan al recuperar. Solo usuarios autenticados pueden verlos.
     * 
     * Campos encriptados:
     * - nombre_completo: Información personal sensible
     * - rut_pasaporte: Documento de identidad (dato altamente sensible)
     * 
     * NOTA: Los campos encriptados NO pueden ser buscados directamente en la BD.
     * 
     * @var array<string>
     */
    protected $encryptable = [
        'nombre_completo',
        'rut_pasaporte',
    ];

    /**
     * Relación belongsTo con Copropietario.
     * 
     * Una persona autorizada pertenece a un copropietario.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function copropietario()
    {
        return $this->belongsTo(Copropietario::class);
    }
}

