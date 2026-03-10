<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

/**
 * Trait EncryptsAttributes
 * 
 * Encripta automáticamente atributos sensibles del modelo antes de guardarlos
 * en la base de datos y los desencripta al recuperarlos.
 * 
 * SEGURIDAD: Los datos se encriptan usando AES-256-CBC con la clave APP_KEY.
 * Solo usuarios autenticados con acceso al sistema pueden ver los datos en texto plano.
 * 
 * USO:
 * 1. Agregar el trait al modelo: use EncryptsAttributes;
 * 2. Definir propiedad: protected $encryptable = ['campo1', 'campo2'];
 * 
 * IMPORTANTE: Los campos encriptados NO pueden ser buscados directamente en la BD.
 */
trait EncryptsAttributes
{
    /**
     * Encriptar atributos antes de guardar en la base de datos
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->isEncryptable($key) && !is_null($value)) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Desencriptar atributos al recuperar de la base de datos
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->isEncryptable($key) && !is_null($value)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                // Si falla la desencriptación, retornar el valor original
                // Esto puede ocurrir si el campo no estaba encriptado previamente
                \Log::warning("Error desencriptando campo {$key}: " . $e->getMessage());
            }
        }

        return $value;
    }

    /**
     * Desencriptar atributos al convertir a array
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getEncryptable() as $key) {
            if (isset($attributes[$key]) && !is_null($attributes[$key])) {
                try {
                    $attributes[$key] = Crypt::decryptString($attributes[$key]);
                } catch (\Exception $e) {
                    \Log::warning("Error desencriptando campo {$key} en array: " . $e->getMessage());
                }
            }
        }

        return $attributes;
    }

    /**
     * Verificar si un atributo debe ser encriptado
     *
     * @param string $key
     * @return bool
     */
    protected function isEncryptable($key): bool
    {
        return in_array($key, $this->getEncryptable());
    }

    /**
     * Obtener lista de atributos encriptables
     *
     * @return array
     */
    protected function getEncryptable(): array
    {
        return property_exists($this, 'encryptable') ? $this->encryptable : [];
    }
}
