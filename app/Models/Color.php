<?php
namespace App\Models;

class Color extends Model
{
    protected static $table = 'color';
    protected static $primaryKey = 'id_color';

    protected static $fillable = [
        'color',
        'estado',
    ];

    protected static $rules = [
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
