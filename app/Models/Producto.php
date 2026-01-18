<?php
namespace App\Models;

class Producto extends Model
{
    protected static string $table = 'productos';
    protected static string $primaryKey = 'id_producto';

    protected static array $fillable = [
        'cod_sunat',
        'serie',
        'nom_prod',
        'codigo_producto',
        'color',
        'unidad_prod',
        'categoria',
        'marca',
        'precio_compra',
        'precio_venta',
        'exonerada',
        'proveedor',
        'estado',
    ];

    protected static array $rules = [
        'nom_prod'        => 'required|min:2|max:255',
        'codigo_producto' => 'required',
        'precio_venta'    => 'required|numeric|minValue:0',
        'precio_compra'   => 'required|numeric|minValue:0',
        'categoria'       => 'required',
        'unidad_prod'     => 'required',
    ];

    /**
     * Obtener categoría relacionada
     */
    public function categoria(): ?Categoria
    {
        if (!$this->categoria) return null;
        return Categoria::find((int)$this->categoria);
    }

    /**
     * Obtener marca relacionada
     */
    public function marca(): ?Marca
    {
        if (!$this->marca) return null;
        return Marca::find((int)$this->attributes['marca']);
    }

    /**
     * Obtener color relacionado
     */
    public function color(): ?Color
    {
        if (!$this->color) return null;
        return Color::find((int)$this->attributes['color']);
    }

    /**
     * Obtener unidad relacionada
     */
    public function unidad(): ?Unidad
    {
        if (!$this->unidad_prod) return null;
        return Unidad::find((int)$this->unidad_prod);
    }

    /**
     * Obtener stock actual del producto
     */
    public function getStock(): int
    {
        $stock = \Sdba::table('stock')
            ->where('producto', $this->getId())
            ->order_by('id_stock', 'desc')
            ->get_one();

        return $stock ? (int)$stock['stockt'] : 0;
    }

    /**
     * Obtener variantes (lotes) del producto
     */
    public function getVariantes(): array
    {
        return \Sdba::table('variantes')
            ->where('producto', $this->getId())
            ->get();
    }

    /**
     * Buscar productos por término
     */
    public static function buscar(string $termino): array
    {
        return static::query()
            ->like('nom_prod', $termino)
            ->or_like('codigo_producto', $termino)
            ->and_where('estado', '1')
            ->get();
    }

    /**
     * Obtener productos con stock bajo
     */
    public static function conStockBajo(int $minimo = 5): array
    {
        $productos = static::where('estado', '1');

        return array_filter($productos, function($producto) use ($minimo) {
            return $producto->getStock() <= $minimo;
        });
    }

    /**
     * Obtener productos con sus relaciones para listado
     */
    public static function conRelaciones(): array
    {
        return static::query()
            ->left_join('categoria', 'categorias', 'id_categoria')
            ->where('productos.estado', '1')
            ->get();
    }

    /**
     * Verificar si el código de producto ya existe
     */
    public static function codigoExists(string $codigo, ?int $exceptId = null): bool
    {
        $query = static::query()->where('codigo_producto', $codigo);

        if ($exceptId) {
            $query->and_where('id_producto !=', $exceptId);
        }

        return $query->get_one() !== null;
    }
}
