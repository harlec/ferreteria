-- Agrega columna proforma_origen a ventas para trazabilidad
-- NULL = venta directa, N = ID de la proforma de origen
ALTER TABLE `ventas`
  ADD COLUMN `proforma_origen` INT(11) NULL DEFAULT NULL AFTER `fecha_pago`;
