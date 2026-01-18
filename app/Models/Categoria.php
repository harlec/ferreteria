<?php
namespace App\Models;

class Categoria extends Model
{
    protected static $table = 'categorias';
    protected static $primaryKey = 'id_categoria';

    protected static $fillable = [
        'nom_cat',
        'estado',
    ];

    protected static $rules = [
        'nom_cat' => 'required|min:2|max:100',
    ];

    /**
     * Obtener categorías activas
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
            ->get_list('id_categoria', 'nom_cat');
    }

    /**
     * Contar productos en esta categoría
     */
    public function countProductos(): int
    {
        return \Sdba::table('productos')
            ->where('categoria', $this->getId())
            ->and_where('estado', '1')
            ->total();
    }
}
