CREATE DATABASE productora_audiovisual;
USE productora_audiovisual;

-- ------------------------------------------------------------
-- CATEGORIAS
-- ------------------------------------------------------------
CREATE TABLE categorias (
  id    INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tipo  VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PROVEEDORES
-- ------------------------------------------------------------
CREATE TABLE proveedores (
  id        INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(150) NOT NULL,
  telefono  VARCHAR(20),
  direccion VARCHAR(255)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- COMPRAS (cabecera)
-- ------------------------------------------------------------
CREATE TABLE compras (
  id              INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
  fecha           DATE        NOT NULL,
  tipocomprobante VARCHAR(50),
  idproveedor     INT         NOT NULL,

  CONSTRAINT fk_compras_proveedor
    FOREIGN KEY (idproveedor) REFERENCES proveedores(id)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- PRODUCTOS
-- ------------------------------------------------------------
CREATE TABLE productos (
  id          INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(150)   NOT NULL,
  precio_base DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  idcategoria INT            NOT NULL,

  CONSTRAINT fk_producto_categoria
    FOREIGN KEY (idcategoria) REFERENCES categorias(id)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- DETALLE DE COMPRA
-- ------------------------------------------------------------
CREATE TABLE detalleCompra (
  id         INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
  cantidad   INT            NOT NULL DEFAULT 1,
  precio     DECIMAL(10, 2) NOT NULL,
  idcompra   INT            NOT NULL,
  idproducto INT            NOT NULL,

  CONSTRAINT fk_detallecompra_compra
    FOREIGN KEY (idcompra) REFERENCES compras(id),

  CONSTRAINT fk_detallecompra_producto
    FOREIGN KEY (idproducto) REFERENCES productos(id)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- CLIENTES
-- ------------------------------------------------------------
CREATE TABLE cliente (
  id        INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(100) NOT NULL,
  apellidos VARCHAR(150) NOT NULL,
  DNI       VARCHAR(20)  NOT NULL UNIQUE,
  direccion VARCHAR(255),
  correo    VARCHAR(150),
  telefono  VARCHAR(20)
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- TESTIGOS
-- ------------------------------------------------------------
CREATE TABLE testigo (
  id        INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(100) NOT NULL,
  apellidos VARCHAR(150) NOT NULL,
  DNI       VARCHAR(20)  NOT NULL
) ENGINE=InnoDB;


select * from cliente;
select * from contratos;
-- ------------------------------------------------------------
-- CONTRATOS
-- Un cliente puede tener más de un contrato
-- ------------------------------------------------------------
CREATE TABLE contratos (
  id             INT  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  fecha_contrato DATE NOT NULL,
  fecha_entrega  DATE,
  idcliente      INT  NOT NULL,
  idtestigo      INT,

  CONSTRAINT fk_contrato_cliente
    FOREIGN KEY (idcliente) REFERENCES cliente(id),

  CONSTRAINT fk_contrato_testigo
    FOREIGN KEY (idtestigo) REFERENCES testigo(id)
) ENGINE=InnoDB;

select*from contratos;

-- ------------------------------------------------------------
-- DETALLE DE CONTRATO
-- El producto se escribe directamente como texto
-- ------------------------------------------------------------
CREATE TABLE detallecontratos (
  id         INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
  producto   VARCHAR(150)   NOT NULL,
  cantidad   INT            NOT NULL DEFAULT 1,
  adelanto   DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  subtotal   DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  total      DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  idcontrato INT            NOT NULL,

  CONSTRAINT fk_detalle_contrato
    FOREIGN KEY (idcontrato) REFERENCES contratos(id)
) ENGINE=InnoDB;