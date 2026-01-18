<?php
namespace App\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Compra;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard principal
     */
    public function index(): void
    {
        // Obtener estadísticas básicas
        $stats = $this->getStats();

        $this->render('dashboard/index', [
            'titulo'     => 'Dashboard',
            'menuActivo' => '1',
            'stats'      => $stats,
        ]);
    }

    /**
     * Obtener estadísticas para el dashboard
     */
    private function getStats(): array
    {
        $stats = [
            'productos'     => 0,
            'clientes'      => 0,
            'ventas_hoy'    => 0,
            'total_hoy'     => 0,
            'stock_bajo'    => 0,
        ];

        try {
            // Total de productos
            $stats['productos'] = \Sdba::table('productos')
                ->where('estado', '1')
                ->total();

            // Total de clientes
            $stats['clientes'] = \Sdba::table('clientes')
                ->where('estado', '1')
                ->total();

            // Ventas de hoy
            $hoy = date('Y-m-d');
            $ventasHoy = \Sdba::table('ventas')
                ->where('fecha', $hoy)
                ->and_where('estado', '1')
                ->get();

            $stats['ventas_hoy'] = count($ventasHoy);
            $stats['total_hoy'] = array_sum(array_column($ventasHoy, 'total'));

            // Productos con stock bajo (menos de 5 unidades)
            $productosStock = \Sdba::table('stock')
                ->where('stockt <=', '5')
                ->group_by('producto')
                ->get();

            $stats['stock_bajo'] = count($productosStock);

        } catch (\Exception $e) {
            // Si hay error, mantener valores en 0
        }

        return $stats;
    }
}
