<?php
namespace App\Models;

class Proveedor extends Model
{
    protected static $table = 'proveedores';
    protected static $primaryKey = 'id_proveedor';

    protected static $fillable = [
        'proveedor',
        'doc_identidad',
        'direccion',
        'telefono',
        'email',
        'estado',
    ];

    protected static $rules = [
        'proveedor'     => 'required|min:3|max:200',
        'doc_identidad' => 'required|ruc',
    ];

    /**
     * Obtener proveedores activos
     */
    public static function activos(): array
    {
        return static::where('estado', '1');
    }

    /**
     * Buscar proveedor por RUC
     */
    public static function findByRuc(string $ruc): ?self
    {
        return static::whereFirst('doc_identidad', $ruc);
    }

    /**
     * Obtener lista para select
     */
    public static function lista(): array
    {
        return static::query()
            ->where('estado', '1')
            ->get_list('id_proveedor', 'proveedor');
    }

    /**
     * Obtener compras del proveedor
     */
    public function getCompras(): array
    {
        return \Sdba::table('compras')
            ->where('proveedor', $this->getId())
            ->order_by('id_compra', 'desc')
            ->get();
    }
}
