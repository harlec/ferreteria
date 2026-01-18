<?php
namespace App\Models;

class Stock extends Model
{
    protected static string $table = 'stock';
    protected static string $primaryKey = 'id_stock';

    protected static array $fillable = [
        'producto',
        'ingreso',
        'egreso',
        'stock',
        'stockt',
        'motivo',
        'fv',
        'tienda',
        'fecha',
        'estado',
    ];

    /**
     * Obtener producto relacionado
     */
    public function producto(): ?Producto
    {
        if (!$this->producto) return null;
        return Producto::find((int)$this->producto);
    }

    /**
     * Obtener stock actual de un producto
     */
    public static function getStockProducto(int $productoId): int
    {
        $stock = static::query()
            ->where('producto', $productoId)
            ->order_by('id_stock', 'desc')
            ->get_one();

        return $stock ? (int)$stock['stockt'] : 0;
    }

    /**
     * Registrar ingreso de stock (compra)
     */
    public static function registrarIngreso(int $productoId, int $cantidad, string $motivo, ?string $fv = null): ?self
    {
        $stockActual = static::getStockProducto($productoId);
        $nuevoStock = $stockActual + $cantidad;

        return static::create([
            'producto' => $productoId,
            'ingreso'  => $cantidad,
            'egreso'   => 0,
            'stock'    => $cantidad,
            'stockt'   => $nuevoStock,
            'motivo'   => $motivo,
            'fv'       => $fv ?? '0000-00-00',
            'fecha'    => date('Y-m-d'),
            'estado'   => '1',
        ]);
    }

    /**
     * Registrar egreso de stock (venta)
     */
    public static function registrarEgreso(int $productoId, int $cantidad, string $motivo): ?self
    {
        $stockActual = static::getStockProducto($productoId);
        $nuevoStock = max(0, $stockActual - $cantidad);

        return static::create([
            'producto' => $productoId,
            'ingreso'  => 0,
            'egreso'   => $cantidad,
            'stock'    => -$cantidad,
            'stockt'   => $nuevoStock,
            'motivo'   => $motivo,
            'fecha'    => date('Y-m-d'),
            'estado'   => '1',
        ]);
    }

    /**
     * Obtener movimientos de un producto (kardex)
     */
    public static function kardex(int $productoId): array
    {
        return static::query()
            ->where('producto', $productoId)
            ->order_by('id_stock', 'asc')
            ->get();
    }

    /**
     * Obtener productos con stock bajo
     */
    public static function productosStockBajo(int $minimo = 5): array
    {
        // Obtener el último stock de cada producto
        $sql = "SELECT s.* FROM stock s
                INNER JOIN (
                    SELECT producto, MAX(id_stock) as max_id
                    FROM stock
                    GROUP BY producto
                ) latest ON s.id_stock = latest.max_id
                WHERE s.stockt <= {$minimo}";

        // Como SDBA no soporta raw queries directamente, usamos un enfoque diferente
        $productos = Producto::where('estado', '1');
        $stockBajo = [];

        foreach ($productos as $producto) {
            $stock = static::getStockProducto($producto->getId());
            if ($stock <= $minimo) {
                $stockBajo[] = [
                    'producto' => $producto,
                    'stock'    => $stock,
                ];
            }
        }

        return $stockBajo;
    }
}
