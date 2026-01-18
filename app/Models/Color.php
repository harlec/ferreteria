<?php
namespace App\Models;

class Color extends Model
{
    protected static string $table = 'color';
    protected static string $primaryKey = 'id_color';

    protected static array $fillable = [
        'color',
        'estado',
    ];

    protected static array $rules = [
        'color' => 'required|min:2|max:50',
    ];

    /**
     * Obtener colores activos
     */
    public static function activos(): array
    {
        return static::where('estado', '1');
    }

    /**
     * Obtener lista para select
     */
    public static function lista(): array
    {
        return static::query()
            ->where('estado', '1')
            ->get_list('id_color', 'color');
    }
}
