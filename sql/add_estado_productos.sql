-- Agrega columna estado a productos para soft delete
-- 1 = activo (default), 0 = eliminado
ALTER TABLE `productos`
  ADD COLUMN `estado` TINYINT(1) NOT NULL DEFAULT 1 AFTER `proveedor`;

-- Asegura que todos los productos existentes queden como activos
UPDATE `productos` SET `estado` = 1;
