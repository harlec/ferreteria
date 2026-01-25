-- Tabla para control de despachos parciales
CREATE TABLE IF NOT EXISTS despachos (
  id_despacho INT AUTO_INCREMENT PRIMARY KEY,
  venta INT NOT NULL,
  detalle INT NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  fecha DATETIME NOT NULL,
  usuario INT NOT NULL,
  INDEX idx_venta (venta),
  INDEX idx_detalle (detalle)
);
