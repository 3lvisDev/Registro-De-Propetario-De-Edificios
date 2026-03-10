<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Copropietario
 * 
 * Representa a un propietario o arrendatario de un departamento en el edificio.
 * 
 * CONVENCIONES DE NOMBRES:
 * - Nombres de campos en base de datos: snake_case (ej: nombre_completo, numero_departamento)
 * - Propiedades del modelo: snake_case (Laravel convierte automáticamente)
 * - Métodos del modelo: camelCase (ej: getNombreCompletoAttribute)
 * - Nombres de tablas: plural en snake_case (ej: copropietarios)
 * 
 * CAMPOS PRINCIPALES:
 * - nombre_completo: Nombre completo del copropietario (mínimo 5 caracteres)
 * - numero_departamento: Número del departamento asociado (requerido)
 * - tipo: Tipo de copropietario - "Propietario" o "Arrendatario" (requerido)
 * - telefono: Número de teléfono (opcional)
 * - correo: Correo electrónico (opcional, validado)
 * - patente: Patente del vehículo (opcional)
 * - estacionamiento: Número de estacionamiento (opcional)
 * - bodega: Número de bodega (opcional)
 * 
 * RELACIONES:
 * - propietario_id: Clave foránea al propietario principal (para arrendatarios)
 * 
 * @property int $id
 * @property string $nombre_completo
 * @property string $numero_departamento
 * @property string $tipo
 * @property string|null $telefono
 * @property string|null $correo
 * @property string|null $patente
 * @property string|null $estacionamiento
 * @property string|null $bodega
 * @property int|null $propietario_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Copropietario extends Model
{
    use HasFactory, \App\Traits\EncryptsAttributes;

    /**
     * Campos asignables masivamente.
     * 
     * IMPORTANTE: Solo estos campos pueden ser asignados mediante create() o fill().
     * Los campos id, created_at, updated_at están protegidos automáticamente.
     * 
     * Campos permitidos según Requisitos 20.1 y 20.5:
     * - nombre_completo: Nombre completo del copropietario
     * - numero_departamento: Número del departamento
     * - tipo: Tipo (Propietario o Arrendatario)
     * - telefono: Teléfono (opcional)
     * - correo: Correo electrónico (opcional)
     * - patente: Patente del vehículo (opcional)
     * - estacionamiento: Número de estacionamiento (opcional)
     * - bodega: Número de bodega (opcional)
     * - propietario_id: ID del propietario principal (para arrendatarios)
     * 
     * Campos explícitamente excluidos: id, created_at, updated_at
     * 
     * @var array<string>
     */
    protected $fillable = [
        'nombre_completo',
        'numero_departamento',
        'tipo',
        'telefono',
        'correo',
        'patente',
        'estacionamiento',
        'bodega',
        'propietario_id',
    ];

    /**
     * Campos que deben ser encriptados en la base de datos.
     * 
     * SEGURIDAD: Estos campos se encriptan automáticamente antes de guardar
     * y se desencriptan al recuperar. Solo usuarios autenticados pueden verlos.
     * 
     * Campos encriptados:
     * - nombre_completo: Información personal sensible
     * - telefono: Dato de contacto privado
     * - correo: Dato de contacto privado
     * 
     * NOTA: Los campos encriptados NO pueden ser buscados directamente en la BD.
     * 
     * @var array<string>
     */
    protected $encryptable = [
        'nombre_completo',
        'telefono',
        'correo',
    ];

    /**
     * Relación hasMany: Arrendatarios asociados a este propietario.
     * 
     * Un propietario puede tener múltiples arrendatarios.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function arrendatarios()
    {
        return $this->hasMany(Copropietario::class, 'propietario_id');
    }

    /**
     * Relación belongsTo: Propietario principal de este arrendatario.
     * 
     * Un arrendatario pertenece a un propietario principal.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function propietarioPrincipal()
    {
        return $this->belongsTo(Copropietario::class, 'propietario_id');
    }

    /**
     * Relación hasMany: Personas autorizadas asociadas a este copropietario.
     * 
     * Un copropietario puede tener múltiples personas autorizadas.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personasAutorizadas()
    {
        return $this->hasMany(PersonaAutorizada::class);
    }
}

