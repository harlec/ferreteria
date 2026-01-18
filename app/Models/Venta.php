<?php
namespace App\Models;

class Venta extends Model
{
    protected static string $table = 'ventas';
    protected static string $primaryKey = 'id_venta';

    protected static array $fillable = [
        'fecha',
        'fecha_ope',
        'total',
        'cliente',
        'usuario',
        'tipo',
        'forma',
        'estado',
    ];

    // Tipos de venta
    const TIPO_CONTADO = '1';
    const TIPO_CREDITO = '2';

    // Formas de pago
    const FORMA_EFECTIVO = '1';
    const FORMA_TARJETA_DEBITO = '2';
    const FORMA_TARJETA_CREDITO = '3';

    // Estados
    const ESTADO_PENDIENTE = '0';
    const ESTADO_CONFIRMADA = '1';
    const ESTADO_ANULADA = '2';

    /**
     * Obtener cliente de la venta
     */
    public function cliente(): ?Cliente
    {
        if (!$this->cliente) return null;
        return Cliente::find((int)$this->cliente);
    }

    /**
     * Obtener usuario que realizó la venta
     */
    public function usuario(): ?Usuario
    {
        if (!$this->usuario) return null;
        return Usuario::find((int)$this->usuario);
    }

    /**
     * Obtener detalles de la venta
     */
    public function getDetalles(): array
    {
        return \Sdba::table('detalle_ventas')
            ->where('venta', $this->getId())
            ->and_where('estado', '1')
            ->get();
    }

    /**
     * Obtener comprobante de la venta
     */
    public function getComprobante(): ?array
    {
        return \Sdba::table('comprobantes')
            ->where('venta', $this->getId())
            ->get_one();
    }

    /**
     * Obtener ventas del día
     */
    public static function delDia(?string $fecha = null): array
    {
        $fecha = $fecha ?? date('Y-m-d');

        $records = static::query()
            ->where('fecha', $fecha)
            ->and_where('estado', '1')
            ->order_by('id_venta', 'desc')
            ->get();

        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public static function porFechas(string $desde, string $hasta): array
    {
        $records = static::query()
            ->where('fecha >=', $desde)
            ->and_where('fecha <=', $hasta)
            ->and_where('estado', '1')
            ->order_by('id_venta', 'desc')
            ->get();

        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Obtener total de ventas del día
     */
    public static function totalDelDia(?string $fecha = null): float
    {
        $ventas = static::delDia($fecha);
        return array_sum(array_map(fn($v) => (float)$v->total, $ventas));
    }

    /**
     * Verificar si la venta puede ser anulada
     */
    public function puedeAnularse(): bool
    {
        return $this->estado == self::ESTADO_CONFIRMADA;
    }

    /**
     * Obtener nombre del tipo de venta
     */
    public function getTipoNombre(): string
    {
        return match($this->tipo) {
            self::TIPO_CONTADO => 'Contado',
            self::TIPO_CREDITO => 'Crédito',
            default => 'Desconocido'
        };
    }

    /**
     * Obtener nombre de la forma de pago
     */
    public function getFormaNombre(): string
    {
        return match($this->forma) {
            self::FORMA_EFECTIVO => 'Efectivo',
            self::FORMA_TARJETA_DEBITO => 'Tarjeta Débito',
            self::FORMA_TARJETA_CREDITO => 'Tarjeta Crédito',
            default => 'Desconocido'
        };
    }
}
