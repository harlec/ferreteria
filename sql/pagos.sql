CREATE TABLE IF NOT EXISTS `pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `venta`   int(11) NOT NULL,
  `forma`   int(11) NOT NULL,
  `monto`   decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `venta` (`venta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
