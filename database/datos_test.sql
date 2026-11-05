USE db_whatsapp;

-- Datos de prueba para desarrollo.
-- Todos los usuarios tienen la contraseña: password
-- ! ELIMINA TODOS LOS DATOS Y PONE UNOS DE DEMO

DELETE FROM fondos_conversaciones;
DELETE FROM reacciones;
UPDATE chats SET id_respuesta = NULL;
DELETE FROM chats;
DELETE FROM conversaciones;
DELETE FROM fondos;
DELETE FROM peticiones;
DELETE FROM estados;
DELETE FROM usuarios;

INSERT INTO usuarios (id, nombre, apellidos, username, password, foto, rol) VALUES
    (1, 'Ana', 'García López', 'ana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bingo.png', 'usuario'),
    (2, 'Carlos', 'Martínez Ruiz', 'carlos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bluey.png', 'usuario'),
    (3, 'Lucía', 'Sánchez Torres', 'lucia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'caballo.jpg', 'usuario'),
    (4, 'David', 'Fernández Romero', 'david', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gato.jpg', 'usuario'),
    (5, 'Marta', 'Díaz Navarro', 'marta', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'perro.jpg', 'usuario'),
    (6, 'Admin', 'Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin.jpg', 'administrador');

INSERT INTO estados (id, estado) VALUES
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

INSERT INTO peticiones (id, id_estado, id_solicitante, id_solicitado) VALUES
    (1, 2, 1, 2),
    (2, 2, 1, 3),
    (3, 1, 4, 1),
    (4, 3, 5, 1),
    (5, 1, 2, 5);

-- El usuario con el ID menor se guarda siempre en id_usuario_1.
INSERT INTO conversaciones (id, id_usuario_1, id_usuario_2, fecha_creacion) VALUES
    (1, 1, 2, '2026-10-14 09:00:00'),
    (2, 1, 3, '2026-10-14 12:30:00'),
    (3, 2, 5, '2026-10-15 10:00:00'),
    (4, 3, 4, '2026-10-15 11:00:00');

INSERT INTO chats (id, fecha_hora, mensaje, id_emisor, id_conversacion, id_respuesta) VALUES
    (1, '2026-10-14 09:00:00', '¡Hola, Carlos! ¿Cómo va todo?', 1, 1, NULL),
    (2, '2026-10-14 09:02:00', 'Muy bien, terminando el proyecto. ¿Y tú?', 2, 1, 1),
    (3, '2026-10-14 09:04:00', 'Probando la vista del chat.', 1, 1, NULL),
    (4, '2026-10-14 09:05:00', 'Perfecto, luego le echo un vistazo.', 2, 1, 3),
    (5, '2026-10-14 12:30:00', 'Lucía, ¿te apetece tomar un café?', 1, 2, NULL),
    (6, '2026-10-14 12:45:00', 'Sí, nos vemos a las cinco.', 3, 2, 5),
    (7, '2026-10-15 08:15:00', 'Te he enviado los apuntes.', 3, 2, NULL),
    (8, '2026-10-15 08:20:00', '¡Recibidos, muchas gracias!', 1, 2, 7),
    (9, '2026-10-15 10:00:00', 'Hola Marta, ¿tienes un minuto?', 2, 3, NULL),
    (10, '2026-10-15 10:03:00', 'Claro, dime.', 5, 3, 9);

INSERT INTO reacciones (id, reaccion, id_usuario, id_chat) VALUES
    (1, '👍', 2, 1),
    (2, '❤️', 1, 2),
    (3, '👍', 3, 5);
