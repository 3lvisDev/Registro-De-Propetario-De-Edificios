<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaAutorizada extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_completo',
        'rut_o_pasaporte',
        'departamento',
        'patente',
    ];
}

