create database valenbicibd;
drop database valenbicibd;
use valenbicibd;

CREATE TABLE historico (
 id INT AUTO_INCREMENT PRIMARY KEY,
 estacion_id INT NOT NULL,
 direccion VARCHAR(255),
 bicis_disponibles INT NOT NULL,
 anclajes_libres INT NOT NULL,
 estado_operativo BOOLEAN NOT NULL,
 fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 ubicación POINT
);

select * from historico;