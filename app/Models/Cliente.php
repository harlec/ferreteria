<?php
namespace App\Models;

class Cliente extends Model
{
    protected static $table = 'clientes';
    protected static $primaryKey = 'id_cliente';

    protected static $fillable = [
        'cliente',
        'doc_identidad',
        'telefono',
        'email',
        'estado',
    ];

    protected static $rules = [
        'cliente'       => 'required|min:3|max:200',
        'doc_identidad' => 'required|min:8|max:11',
    ];

    /**
     * Obtener clientes activos
     */
    public static function activos(): array
    {
        return static::where('estado', '1');
    }

    /**
     * Buscar cliente por documento
     */
    public static function findByDocumento(string $documento): ?self
    {
        return static::whereFirst('doc_identidad', $documento);
    }

    /**
     * Buscar clientes por término
     */
    public static function buscar(string $termino): array
    {
        return static::query()
            ->like('cliente', $termino)
            ->or_like('doc_identidad', $termino)
            ->and_where('estado', '1')
            ->get();
    }

    /**
     * Obtener ventas del cliente
     */
    public function getVentas(): array
    {
        return \Sdba::table('ventas')
            ->where('cliente', $this->getId())
            ->order_by('id_venta', 'desc')
            ->get();
    }

    /**
     * Obtener total de compras del cliente
     */
    public function getTotalCompras(): float
    {
        $ventas = $this->getVentas();
        return array_sum(array_column($ventas, 'total'));
    }

    /**
     * Verificar si el documento ya existe
     */
    public static function documentoExists(string $documento, ?int $exceptId = null): bool
    {
        $query = static::query()->where('doc_identidad', $documento);

        if ($exceptId) {
            $query->and_where('id_cliente !=', $exceptId);
        }

        return $query->get_one() !== null;
    }
}
