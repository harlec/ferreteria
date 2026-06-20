-- Corrige ventas con total negativo recalculando desde detalle_ventas
-- Ejecutar UNA sola vez. Verificar primero con el SELECT comentado abajo.

-- Verificar cuáles se van a corregir:
-- SELECT v.id_venta, v.total as total_actual,
--        COALESCE(SUM(dv.total), 0) as total_correcto
-- FROM ventas v
-- LEFT JOIN detalle_ventas dv ON dv.venta = v.id_venta
-- WHERE v.total < 0
-- GROUP BY v.id_venta;

UPDATE ventas v
SET v.total = (
    SELECT COALESCE(SUM(dv.total), 0)
    FROM detalle_ventas dv
    WHERE dv.venta = v.id_venta
)
WHERE v.total < 0;
