<?php

// Este archivo solo se carga desde conversaciones.php, que ya comprueba la sesión.
// Si se abre directamente por URL, redirige a conversaciones.php.
if (basename($_SERVER['SCRIPT_FILENAME']) === 'chat.php') {
    header('Location: conversaciones.php');
    exit();
}

include_once('../database/conexion.php');

$idUsuario = $_SESSION['id_usuario'];
$idAmigo = $_SESSION['id_amigo'];
$idConversacion = $_SESSION['id_conversacion'];
$amigo = null;
$mensajes = [];
$fondos = [];
$idFondoSeleccionado = 1;

try {
    // Obtiene los datos del amigo para la cabecera del chat
    $sqlAmigo = "SELECT id, nombre, apellidos, foto
                 FROM usuarios
                 WHERE id = :id_amigo";

    $stmtAmigo = $conn->prepare($sqlAmigo);
    $stmtAmigo->execute(['id_amigo' => $idAmigo]);
    $amigo = $stmtAmigo->fetch(PDO::FETCH_ASSOC);

    // Carga el catálogo y el fondo que este usuario eligió para esta conversación
    $stmtFondos = $conn->query("SELECT id, nombre FROM fondos ORDER BY id");
    $fondos = $stmtFondos->fetchAll(PDO::FETCH_ASSOC);

    $sqlFondo = "SELECT f.id
                 FROM fondos_conversaciones fc
                 INNER JOIN fondos f ON fc.id_fondo = f.id
                 WHERE fc.id_conversacion = :id_conversacion
                   AND fc.id_usuario = :id_usuario";
    $stmtFondo = $conn->prepare($sqlFondo);
    $stmtFondo->execute([
        'id_conversacion' => $idConversacion,
        'id_usuario' => $idUsuario
    ]);
    $fondoSeleccionado = $stmtFondo->fetch(PDO::FETCH_ASSOC);

    if ($fondoSeleccionado) {
        $idFondoSeleccionado = $fondoSeleccionado['id'];
    }

    // Carga los mensajes junto con sus respuestas y reacciones
    $sqlMensajes = "SELECT ch.id, ch.mensaje, ch.fecha_hora, ch.id_emisor,
                           ch.archivo, ch.nombre_archivo, ch.tipo_archivo,
                           ch.id_respuesta, respuesta.mensaje AS mensaje_respuesta,
                           respuesta.nombre_archivo AS archivo_respuesta,
                           (SELECT GROUP_CONCAT(r.reaccion SEPARATOR ' ')
                            FROM reacciones r
                            WHERE r.id_chat = ch.id) AS reacciones
                    FROM chats ch
                    LEFT JOIN chats respuesta ON respuesta.id = ch.id_respuesta
                    WHERE ch.id_conversacion = :id_conversacion
                    ORDER BY ch.fecha_hora ASC, ch.id ASC";

    $stmtMensajes = $conn->prepare($sqlMensajes);
    $stmtMensajes->execute(['id_conversacion' => $idConversacion]);
    $mensajes = $stmtMensajes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'No se ha podido cargar el chat.';
}

$rutaArchivosChat = '../assets/uploads/chats/';
$errorArchivo = isset($_GET['error']) && $_GET['error'] === 'archivo';

$fotoAmigo = $fotoPredeterminada;
// Usa la foto del usuario si tiene una guardada
if ($amigo && $amigo['foto']) {
    $fotoAmigo = $rutaFotosUsuarios . $amigo['foto'];
}

?>

<main class="chat-pagina">
        <header class="chat-cabecera">
            <form action="conversaciones.php" method="POST">
                <button class="chat-volver" type="submit" name="cerrar_chat">&larr;</button>
            </form>
            <?php if ($amigo): ?>
                <img src="<?= htmlspecialchars($fotoAmigo) ?>" alt="">
                <h1><?= htmlspecialchars($amigo['nombre'] . ' ' . $amigo['apellidos']) ?></h1>
                <form class="selector-fondo" action="../proc/guardar_fondo.php" method="POST">
                    <input type="hidden" name="id_conversacion" value="<?= $idConversacion ?>">
                    <input type="hidden" name="id_amigo" value="<?= $idAmigo ?>">
                    <label for="id-fondo">Fondo:</label>
                    <select id="id-fondo" name="id_fondo" onchange="this.form.submit()">
                        <?php foreach ($fondos as $fondo): ?>
                            <option value="<?= $fondo['id'] ?>" <?= $fondo['id'] == $idFondoSeleccionado ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fondo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php else: ?>
                <h1>Chat</h1>
            <?php endif; ?>
        </header>

        <section class="chat-mensajes fondo-chat-<?= $idFondoSeleccionado ?>">
            <?php if (isset($error)): ?>
                <p class="chat-aviso"><?= $error ?></p>
            <?php elseif (empty($mensajes)): ?>
                <p class="chat-aviso">Todavía no hay mensajes en esta conversación.</p>
            <?php else: ?>
                <?php foreach ($mensajes as $mensaje): ?>
                    <?php
                    $claseMensaje = 'mensaje-amigo';
                    $esPropio = 'false';

                    // Coloca a la derecha los mensajes enviados por el usuario
                    if ($mensaje['id_emisor'] == $idUsuario) {
                        $claseMensaje = 'mensaje-propio';
                        $esPropio = 'true';
                    }
                    ?>
                    <article
                        class="mensaje <?= $claseMensaje ?>"
                        oncontextmenu="mostrarMenu(event, <?= $mensaje['id'] ?>, <?= $esPropio ?>)">
                        <!-- Muestra el emisor de cada mensaje -->
                        <span class="mensaje-autor"><?= $esPropio === 'true' ? 'Tú' : ($amigo ? htmlspecialchars($amigo['nombre']) : '') ?></span>
                        <?php if ($mensaje['id_respuesta']): ?>
                            <div class="respuesta-original">
                                <?= $mensaje['mensaje_respuesta'] !== '' ? htmlspecialchars($mensaje['mensaje_respuesta']) : '📎 ' . htmlspecialchars($mensaje['archivo_respuesta']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($mensaje['archivo']): ?>
                            <?php $rutaArchivo = $rutaArchivosChat . $mensaje['archivo']; ?>
                            <?php if ($mensaje['tipo_archivo'] === 'imagen'): ?>
                                <a href="<?= $rutaArchivo ?>" target="_blank">
                                    <img class="mensaje-imagen" src="<?= $rutaArchivo ?>" alt="<?= htmlspecialchars($mensaje['nombre_archivo']) ?>">
                                </a>
                            <?php elseif ($mensaje['tipo_archivo'] === 'audio'): ?>
                                <audio class="mensaje-audio" controls src="<?= $rutaArchivo ?>"></audio>
                            <?php else: ?>
                                <a class="mensaje-archivo" href="<?= $rutaArchivo ?>" download="<?= htmlspecialchars($mensaje['nombre_archivo']) ?>">
                                    📄 <?= htmlspecialchars($mensaje['nombre_archivo']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($mensaje['mensaje'] !== ''): ?>
                            <p><?= htmlspecialchars($mensaje['mensaje']) ?></p>
                        <?php endif; ?>
                        <time datetime="<?= $mensaje['fecha_hora'] ?>">
                            <?= date('d/m/Y H:i', strtotime($mensaje['fecha_hora'])) ?>
                        </time>
                        <?php if ($mensaje['reacciones']): ?>
                            <div class="reacciones-mensaje"><?= htmlspecialchars($mensaje['reacciones']) ?></div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- Menú que aparece al hacer clic derecho sobre un mensaje -->
        <form id="menu-mensaje" class="menu-mensaje" method="POST" hidden onclick="event.stopPropagation()">
            <input id="id-chat-menu" type="hidden" name="id_chat">
            <input id="mensaje-editado" type="hidden" name="mensaje">
            <input id="reaccion-menu" type="hidden" name="reaccion">
            <button type="button" onclick="responderMensaje()">Responder</button>
            <button id="opcion-editar" type="button" onclick="editarMensaje()">Editar</button>
            <button id="opcion-eliminar" type="button" onclick="eliminarMensaje()">Eliminar</button>
            <div class="menu-reacciones">
                <button type="button" onclick="reaccionarMensaje('👍')">👍</button>
                <button type="button" onclick="reaccionarMensaje('❤️')">❤️</button>
                <button type="button" onclick="reaccionarMensaje('😂')">😂</button>
                <button type="button" onclick="reaccionarMensaje('😮')">😮</button>
            </div>
        </form>

        <!-- Formulario para enviar un mensaje nuevo -->
        <form class="chat-formulario" action="../proc/enviar_mensaje.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_conversacion" value="<?= $idConversacion ?>">
            <input type="hidden" name="id_amigo" value="<?= $idAmigo ?>">
            <input id="id-respuesta" type="hidden" name="id_respuesta">
            <div id="respuesta-activa" class="respuesta-activa" hidden>
                <span id="texto-respuesta"></span>
                <button type="button" onclick="cancelarRespuesta()">×</button>
            </div>
            <?php if ($errorArchivo): ?>
                <p class="chat-error-archivo">No se pudo enviar el archivo. Solo se permiten imágenes, audios, PDF, TXT, ZIP, DOCX o XLSX de hasta 20 MB.</p>
            <?php endif; ?>
            <div id="archivo-activo" class="respuesta-activa" hidden>
                <span id="texto-archivo"></span>
                <button type="button" onclick="cancelarArchivo()">×</button>
            </div>
            <!-- Botón para adjuntar una imagen, un audio o un archivo -->
            <label class="chat-adjuntar" for="archivo" title="Adjuntar imagen, audio o archivo">📎</label>
            <input id="archivo" class="chat-archivo" type="file" name="archivo"
                   accept="image/jpeg,image/png,image/gif,image/webp,audio/*,.pdf,.txt,.zip,.docx,.xlsx"
                   onchange="mostrarArchivo(this)">
            <input type="text" name="mensaje" placeholder="Escribe un mensaje" autocomplete="off" maxlength="250">
            <button type="submit">Enviar</button>
        </form>
</main>
