-- Crea la base de datos si no existe
CREATE DATABASE IF NOT EXISTS db_whatsapp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_whatsapp;

-- Usuarios registrados en la aplicación
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto VARCHAR(500) NULL,
    rol ENUM('usuario', 'administrador') NOT NULL DEFAULT 'usuario'
);

-- Estados posibles de las peticiones de amistad
CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estado VARCHAR(30) NOT NULL
);

-- Fondos disponibles para personalizar las conversaciones
CREATE TABLE fondos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

-- Relaciona a los dos usuarios que forman una conversación
CREATE TABLE conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_1 INT NOT NULL,
    id_usuario_2 INT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conversaciones_usuario_1
        FOREIGN KEY (id_usuario_1) REFERENCES usuarios (id),
    CONSTRAINT fk_conversaciones_usuario_2
        FOREIGN KEY (id_usuario_2) REFERENCES usuarios (id)
);

-- Fondo elegido por cada usuario para cada conversación
CREATE TABLE fondos_conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_conversacion INT NOT NULL,
    id_usuario INT NOT NULL,
    id_fondo INT NOT NULL,
    CONSTRAINT uq_fondo_conversacion_usuario UNIQUE (id_conversacion, id_usuario),
    CONSTRAINT fk_fondo_conversacion
        FOREIGN KEY (id_conversacion) REFERENCES conversaciones (id),
    CONSTRAINT fk_fondo_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id),
    CONSTRAINT fk_fondo_seleccionado
        FOREIGN KEY (id_fondo) REFERENCES fondos (id)
);

-- Mensajes enviados dentro de cada conversación.
-- Un mensaje puede llevar texto, un archivo adjunto o las dos cosas.
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mensaje VARCHAR(250) NOT NULL DEFAULT '',
    archivo VARCHAR(255) NULL,
    nombre_archivo VARCHAR(255) NULL,
    tipo_archivo ENUM('imagen', 'audio', 'archivo') NULL,
    id_emisor INT NOT NULL,
    id_conversacion INT NOT NULL,
    id_respuesta INT NULL,
    CONSTRAINT fk_chats_emisor
        FOREIGN KEY (id_emisor) REFERENCES usuarios (id),
    CONSTRAINT fk_chats_conversacion
        FOREIGN KEY (id_conversacion) REFERENCES conversaciones (id),
    CONSTRAINT fk_chats_respuesta
        FOREIGN KEY (id_respuesta) REFERENCES chats (id)
);

-- Reacciones de los usuarios a los mensajes
CREATE TABLE reacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reaccion VARCHAR(30) NOT NULL,
    id_usuario INT NOT NULL,
    id_chat INT NOT NULL,
    CONSTRAINT fk_reacciones_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id),
    CONSTRAINT fk_reacciones_chat
        FOREIGN KEY (id_chat) REFERENCES chats (id)
);

-- Peticiones de amistad entre usuarios
CREATE TABLE peticiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estado INT NOT NULL,
    id_solicitante INT NOT NULL,
    id_solicitado INT NOT NULL,
    CONSTRAINT fk_peticiones_estado
        FOREIGN KEY (id_estado) REFERENCES estados (id),
    CONSTRAINT fk_peticiones_solicitante
        FOREIGN KEY (id_solicitante) REFERENCES usuarios (id),
    CONSTRAINT fk_peticiones_solicitado
        FOREIGN KEY (id_solicitado) REFERENCES usuarios (id)
);

INSERT IGNORE INTO estados (id, estado) VALUES
    (1, 'pendiente'),
    (2, 'aceptada'),
    (3, 'rechazada');

INSERT INTO fondos (id, nombre) VALUES
    (1, 'Predeterminado'),
    (2, 'Estrellas'),
    (3, 'Burbujas'),
    (4, 'Ondas'),
    (5, 'Orgánico'),
    (6, 'Puntos'),
    (7, 'Diagonales'),
    (8, 'Montañas'),
    (9, 'Degradado'),
    (10, 'Topográfico'),
    (11, 'Geométrico');
