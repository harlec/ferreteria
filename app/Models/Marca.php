<?php
namespace App\Models;

class Marca extends Model
{
    protected static string $table = 'marca';
    protected static string $primaryKey = 'id_marca';

    protected static array $fillable = [
        'marca',
        'estado',
    ];

    protected static array $rules = [
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
