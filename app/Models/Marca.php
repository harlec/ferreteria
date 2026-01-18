<?php
namespace App\Models;

class Marca extends Model
{
    protected static $table = 'marca';
    protected static $primaryKey = 'id_marca';

    protected static $fillable = [
        'marca',
        'estado',
    ];

    protected static $rules = [
        'marca' => 'required|min:2|max:100',
    ];

    /**
     * Obtener marcas activas
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
            ->get_list('id_marca', 'marca');
    }
}
