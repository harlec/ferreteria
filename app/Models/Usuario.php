<?php
namespace App\Models;

class Usuario extends Model
{
    protected static $table = 'usuarios';
    protected static $primaryKey = 'id_usuario';

    protected static $fillable = [
        'nombres',
        'usuario',
        'clave',
        'tienda',
        'rol',
        'estado',
    ];

    protected static $rules = [
        'nombres' => 'required|min:3|max:100',
        'usuario' => 'required|min:3|max:50',
        'clave'   => 'required|min:6',
        'tienda'  => 'required',
        'rol'     => 'required|in:admin,operador',
    ];

    /**
     * Pepper para hash de contraseñas (mantener compatibilidad con sistema legacy)
     */
    private static string $pepper = 'c1isvFdxMDdmjOlvxpecFw';

    /**
     * Buscar usuario por nombre de usuario
     */
    public static function findByUsername(string $username): ?self
    {
        return static::whereFirst('usuario', $username);
    }

    /**
     * Verificar credenciales de login
     */
    public static function authenticate(string $username, string $password): ?self
    {
        $user = static::findByUsername($username);

        if (!$user) {
            return null;
        }

        // Verificar contraseña con el método legacy (HMAC-SHA256)
        $hashedPassword = static::hashPassword($password);

        if ($user->clave !== $hashedPassword) {
            return null;
        }

        // Verificar que el usuario esté activo
        if ($user->estado != '1') {
            return null;
        }

        return $user;
    }

    /**
     * Hash de contraseña (método legacy para compatibilidad)
     */
    public static function hashPassword(string $password): string
    {
        return hash_hmac('sha256', $password, static::$pepper);
    }

    /**
     * Crear usuario con contraseña hasheada
     */
    public static function createWithPassword(array $data): ?self
    {
        if (isset($data['clave'])) {
            $data['clave'] = static::hashPassword($data['clave']);
        }

        return static::create($data);
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(string $newPassword): bool
    {
        return $this->update([
            'clave' => static::hashPassword($newPassword)
        ]);
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Verificar si es operador
     */
    public function isOperador(): bool
    {
        return $this->rol === 'operador';
    }

    /**
     * Obtener usuarios activos
     */
    public static function activos(): array
    {
        return static::where('estado', '1');
    }

    /**
     * Obtener usuarios por rol
     */
    public static function byRol(string $rol): array
    {
        $records = static::query()
            ->where('rol', $rol)
            ->and_where('estado', '1')
            ->get();

        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Verificar si el nombre de usuario ya existe
     */
    public static function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $query = static::query()->where('usuario', $username);

        if ($exceptId) {
            $query->and_where('id_usuario !=', $exceptId);
        }

        return $query->get_one() !== null;
    }
}
