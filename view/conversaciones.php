<?php
session_start();
$nombreUsuario = $_SESSION['username'] ?? '';
include_once('../database/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$rolUsuario = $_SESSION['rol'] ?? 'usuario';
$amigos = [];
$usuariosEncontrados = [];
$rutaFotosUsuarios = '../assets/uploads/usuarios/';
$fotoPredeterminada = '../assets/icons/usuario.png';
$fotoUsuario = $fotoPredeterminada;
$numeroPeticiones = 0;
$buscar = $_GET['buscar'] ?? '';
if (!is_string($buscar)) {
    $buscar = '';
}
$buscar = trim($buscar);

if (isset($_POST['cerrar_chat'])) {
    // Cierra el chat pero mantiene al usuario en la sesión
    unset($_SESSION['id_conversacion']);
    unset($_SESSION['id_amigo']);
}

if (isset($_POST['id_conversacion'])) {
    $idConversacion = $_POST['id_conversacion'];
    $idAmigo = $_POST['id_amigo'] ?? '';

    echo "admin";

    if (is_string($idConversacion) && ctype_digit($idConversacion) &&
        is_string($idAmigo) && ctype_digit($idAmigo)) {
        $stmt = $conn->prepare('SELECT c.id FROM conversaciones c INNER JOIN peticiones p ON p.id_estado = 2 AND ((p.id_solicitante = :usuario_solicitante AND p.id_solicitado = :amigo_solicitado) OR (p.id_solicitante = :amigo_solicitante AND p.id_solicitado = :usuario_solicitado_inverso)) WHERE c.id = :conversacion AND ((c.id_usuario_1 = :usuario_1 AND c.id_usuario_2 = :amigo_1) OR (c.id_usuario_2 = :usuario_2 AND c.id_usuario_1 = :amigo_2))');
        $stmt->execute([
            'usuario_solicitante' => $idUsuario,
            'amigo_solicitado' => $idAmigo,
            'amigo_solicitante' => $idAmigo,
            'usuario_solicitado_inverso' => $idUsuario,
            'conversacion' => $idConversacion,
            'usuario_1' => $idUsuario,
            'amigo_1' => $idAmigo,
            'usuario_2' => $idUsuario,
            'amigo_2' => $idAmigo
        ]);

        if ($stmt->fetch()) {
            $_SESSION['id_conversacion'] = $idConversacion;
            $_SESSION['id_amigo'] = $idAmigo;
        }
    }
}

try {
    $stmtUsuario = $conn->prepare('SELECT nombre, apellidos, username, foto FROM usuarios WHERE id = :id_usuario');
    $stmtUsuario->execute(['id_usuario' => $idUsuario]);
    $usuarioActual = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if ($usuarioActual) {
        // Muestra siempre el username (no el nombre real) del usuario conectado
        $nombreUsuario = $usuarioActual['username'];

        if (!empty($usuarioActual['foto'])) {
            $fotoUsuario = $rutaFotosUsuarios . $usuarioActual['foto'];
        }
    }

    $stmtPeticiones = $conn->prepare('SELECT COUNT(*) FROM peticiones WHERE id_solicitado = :id_usuario AND id_estado = 1');
    $stmtPeticiones->execute(['id_usuario' => $idUsuario]);
    $numeroPeticiones = $stmtPeticiones->fetchColumn();

    // Busca las amistades aceptadas que tienen una conversación
    $sql = "SELECT DISTINCT u.id, u.nombre, u.apellidos, u.foto, c.id AS id_conversacion
            FROM usuarios u
            INNER JOIN peticiones p
                ON u.id IN (p.id_solicitante, p.id_solicitado)
            INNER JOIN conversaciones c
                ON u.id IN (c.id_usuario_1, c.id_usuario_2)
                AND :id_usuario_conversacion IN (c.id_usuario_1, c.id_usuario_2)
            WHERE p.id_estado = 2 AND :id_usuario_1 IN (p.id_solicitante, p.id_solicitado) AND u.id <> :id_usuario_2
            ORDER BY u.nombre, u.apellidos";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id_usuario_conversacion' => $idUsuario,
        'id_usuario_1' => $idUsuario,
        'id_usuario_2' => $idUsuario
    ]);
    $amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($buscar !== '') {
        $sqlUsuarios = "SELECT u.id, u.username, u.nombre, u.apellidos, u.foto,
                               p.id AS id_peticion, p.id_estado, p.id_solicitante
                        FROM usuarios u
                        LEFT JOIN peticiones p ON p.id = (
                            SELECT MAX(p2.id)
                            FROM peticiones p2
                            WHERE (p2.id_solicitante = :id_solicitante AND p2.id_solicitado = u.id)
                               OR (p2.id_solicitante = u.id AND p2.id_solicitado = :id_solicitado)
                        )
                        WHERE u.id <> :id_usuario";
        $parametrosUsuarios = [
            'id_solicitante' => $idUsuario,
            'id_solicitado' => $idUsuario,
            'id_usuario' => $idUsuario
        ];

        if ($buscar !== '') {
            $sqlUsuarios .= " AND (u.username LIKE :buscar_username
                                  OR u.nombre LIKE :buscar_nombre
                                  OR u.apellidos LIKE :buscar_apellidos
                                  OR CONCAT(u.nombre, ' ', u.apellidos) LIKE :buscar_nombre_completo)";
            $textoBuscar = '%' . $buscar . '%';
            $parametrosUsuarios['buscar_username'] = $textoBuscar;
            $parametrosUsuarios['buscar_nombre'] = $textoBuscar;
            $parametrosUsuarios['buscar_apellidos'] = $textoBuscar;
            $parametrosUsuarios['buscar_nombre_completo'] = $textoBuscar;
        }

        $sqlUsuarios .= ' ORDER BY u.nombre, u.apellidos';
        $stmt = $conn->prepare($sqlUsuarios);
        $stmt->execute($parametrosUsuarios);
        $usuariosEncontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = 'No se pudieron cargar las conversaciones.';
}

$mensajesSolicitud = [
    'enviada' => 'Solicitud enviada.',
    'aceptada' => 'Solicitud aceptada.',
    'rechazada' => 'Solicitud rechazada.'
];
$mensajeSolicitud = $mensajesSolicitud[$_GET['solicitud'] ?? ''] ?? '';
if (isset($_GET['error']) && $_GET['error'] === 'solicitud') {
    $mensajeSolicitud = 'No se pudo procesar la solicitud.';
}

?>

<?php
$clasePagina = 'pagina-conversaciones';
if (isset($_SESSION['id_conversacion'])) {
    $clasePagina = 'pagina-conversaciones chat-abierto';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoloNet
    </title>
    <link rel="icon" type="image/png" href="../assets/icons/holonet-favicon.png?v=3.0">
    <link rel="stylesheet" href="../assets/styles/style.css?v=3.1">
</head>
<body class="<?= $clasePagina ?>" onclick="ocultarMenu()">
    <header class="cabecera-principal">
        <a class="marca-principal" href="conversaciones.php" title="HoloNet">
            <img src="../assets/icons/holonet-logo.png" alt="HoloNet">
        </a>
        <div class="acciones-usuario">
            <?php if ($rolUsuario === 'administrador'): ?>
                <a class="admin" href="admin.php" title="Panel de administración">
                    <img src="../assets/icons/admin.png" alt="Panel de administración">
                </a>
            <?php endif; ?>
            <a class="notificaciones" href="peticiones.php" title="Peticiones recibidas">
                <img src="<?= $numeroPeticiones > 0 ? '../assets/icons/notificacion.png' : '../assets/icons/campana.png' ?>" alt="Peticiones recibidas">
            </a>
            <a class="usuario" href="ver_usuario.php">
            <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Perfil">
            <span>@<?= htmlspecialchars($nombreUsuario) ?></span>
            </a>
        </div>
    </header>

    <div class="app-whatsapp">
        <!-- Panel lateral con las amistades -->
        <aside id="lateral">
            <div id="menu-header">
                <h2>Chats</h2>
            </div>

            <form id="buscador" action="conversaciones.php" method="GET">
                <img src="../assets/icons/buscar.png" alt="">
                <input type="search" name="buscar" placeholder="Buscar amistad" value="<?= htmlspecialchars($buscar) ?>">
            </form>

            <?php if ($mensajeSolicitud !== ''): ?>
                <p role="status"><?= $mensajeSolicitud ?></p>
            <?php endif; ?>

            <div id="panel-listas">
            <?php if ($buscar !== ''): ?>
                <div id="resultados-usuarios">
                    <h2>Usuarios encontrados</h2>
                    <?php if (empty($usuariosEncontrados)): ?>
                        <p>No se encontraron usuarios.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($usuariosEncontrados as $usuarioEncontrado): ?>
                                <li>
                                    <div class="usuario-encontrado">
                                        <img src="<?= !empty($usuarioEncontrado['foto']) ? htmlspecialchars($rutaFotosUsuarios . $usuarioEncontrado['foto']) : $fotoPredeterminada ?>" alt="">
                                        <span><?= htmlspecialchars($usuarioEncontrado['nombre'] . ' ' . $usuarioEncontrado['apellidos']) ?></span>
                                        <?php if ($usuarioEncontrado['id_estado'] == 2): ?>
                                            <span>Amigos</span>
                                        <?php elseif ($usuarioEncontrado['id_estado'] == 1 && $usuarioEncontrado['id_solicitante'] == $usuarioEncontrado['id']): ?>
                                            <form action="../proc/gestionar_solicitud.php" method="POST">
                                                <input type="hidden" name="buscar" value="<?= htmlspecialchars($buscar) ?>">
                                                <input type="hidden" name="id_peticion" value="<?= $usuarioEncontrado['id_peticion'] ?>">
                                                <button type="submit" name="accion" value="aceptar">Aceptar</button>
                                                <button type="submit" name="accion" value="rechazar">Rechazar</button>
                                            </form>
                                        <?php elseif ($usuarioEncontrado['id_estado'] == 1): ?>
                                            <span>Solicitud enviada</span>
                                        <?php else: ?>
                                            <form action="../proc/gestionar_solicitud.php" method="POST">
                                                <input type="hidden" name="buscar" value="<?= htmlspecialchars($buscar) ?>">
                                                <input type="hidden" name="id_usuario" value="<?= $usuarioEncontrado['id'] ?>">
                                                <button type="submit" name="accion" value="enviar">Enviar solicitud</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div id="chats">
                <h2>Amistades</h2>

                <?php if (isset($error)): ?>
                    <p>Error al cargar las amistades: <?= $error ?></p>
                <?php elseif (empty($amigos)): ?>
                    <p>Todavía no tienes amistades aceptadas.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($amigos as $amigoLista): ?>
                            <?php
                            $claseChat = '';
                            if (isset($_SESSION['id_conversacion']) && $_SESSION['id_conversacion'] == $amigoLista['id_conversacion']) {
                                $claseChat = 'chat-activo';
                            }
                            ?>
                            <li>
                                <form action="conversaciones.php" method="POST">
                                    <input type="hidden" name="id_conversacion" value="<?= $amigoLista['id_conversacion'] ?>">
                                    <input type="hidden" name="id_amigo" value="<?= $amigoLista['id'] ?>">
                                    <button
                                        type="submit"
                                        class="<?= $claseChat ?>">
                                        <img src="<?= !empty($amigoLista['foto']) ? htmlspecialchars($rutaFotosUsuarios . $amigoLista['foto']) : $fotoPredeterminada ?>" alt="">
                                        <span><?= htmlspecialchars($amigoLista['nombre'] . ' ' . $amigoLista['apellidos']) ?></span>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            </div>

            <form id="cerrar-sesion" action="../proc/logout.php" method="POST">
                <button type="submit">Cerrar sesión</button>
            </form>
        </aside>

        <!-- Carga el chat seleccionado o muestra el mensaje inicial -->
        <?php if (isset($_SESSION['id_conversacion'])): ?>
            <?php
                include('chat.php');
            ?>
        <?php else: ?>
            <main class="chat-sin-seleccionar">
                <div class="mensaje-inicial">
                    <img class="mensaje-inicial-logo" src="../assets/icons/holonet-logo.png" alt="HoloNet">
                    <h2>Selecciona una conversación</h2>
                    <p>Elige un contacto del panel lateral para empezar a chatear.</p>
                </div>
            </main>
        <?php endif; ?>
    </div>
    <script src="../assets/scripts/main.js?v=1.8"></script>
</body>
</html>
