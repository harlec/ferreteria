ALTER TABLE `comprobantes`
  ADD COLUMN `anulado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `state`,
  ADD COLUMN `nota_credito_id` INT NULL DEFAULT NULL AFTER `anulado`;
