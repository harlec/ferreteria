<?php
namespace App\Models;

class Unidad extends Model
{
    protected static $table = 'unidades';
    protected static $primaryKey = 'id_unidad';

    protected static $fillable = [
        'nombre',
        'estado',
    ];

    protected static $rules = [
        'nombre' => 'required|min:2|max:50',
    ];

    /**
     * Obtener unidades activas
     */
    public static function activas(): array
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
            ->get_list('id_unidad', 'nombre');
    }
}
