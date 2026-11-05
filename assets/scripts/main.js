var idChatSeleccionado;
var textoChatSeleccionado;

// Abre el menú al hacer clic derecho sobre un mensaje
function mostrarMenu(evento, idChat, esPropio) {
    evento.preventDefault();
    evento.stopPropagation();

    var menu = document.getElementById('menu-mensaje');

    idChatSeleccionado = idChat;
    // Guarda el texto del mensaje para mostrarlo al responder (o avisa si solo es un adjunto)
    var textoMensaje = evento.currentTarget.querySelector('p');
    textoChatSeleccionado = textoMensaje ? textoMensaje.textContent : '📎 Archivo adjunto';
    document.getElementById('id-chat-menu').value = idChat;
    document.getElementById('opcion-editar').hidden = !esPropio;
    document.getElementById('opcion-eliminar').hidden = !esPropio;

    menu.hidden = false;
    menu.style.left = evento.clientX + 'px';
    menu.style.top = evento.clientY + 'px';
}

function ocultarMenu() {
    // Oculta el menú al pulsar fuera de él
    var menu = document.getElementById('menu-mensaje');

    if (menu) {
        menu.hidden = true;
    }
}

// Envía el formulario al archivo PHP indicado
function enviarMensaje(archivo) {
    var formulario = document.getElementById('menu-mensaje');

    formulario.action = archivo;
    formulario.submit();
}

function editarMensaje() {
    // Pide el texto nuevo antes de enviarlo al proceso de edición
    var mensajeNuevo = prompt('Escribe el nuevo mensaje:');

    if (mensajeNuevo !== null) {
        document.getElementById('mensaje-editado').value = mensajeNuevo;
        enviarMensaje('../proc/editar_mensaje.php');
    }
}

function eliminarMensaje() {
    // Pide confirmación antes de eliminar el mensaje
    if (confirm('¿Quieres eliminar este mensaje?')) {
        enviarMensaje('../proc/eliminar_mensaje.php');
    }
}

function responderMensaje() {
    // Guarda el mensaje al que se está respondiendo
    document.getElementById('id-respuesta').value = idChatSeleccionado;
    document.getElementById('texto-respuesta').textContent = 'Respondiendo a: ' + textoChatSeleccionado;
    document.getElementById('respuesta-activa').hidden = false;
    ocultarMenu();
}

function cancelarRespuesta() {
    document.getElementById('id-respuesta').value = '';
    document.getElementById('respuesta-activa').hidden = true;
}

function reaccionarMensaje(reaccion) {
    // Envía la reacción y el id del mensaje seleccionado
    document.getElementById('reaccion-menu').value = reaccion;
    enviarMensaje('../proc/reaccionar_mensaje.php');
}

function mostrarArchivo(campo) {
    // Muestra el nombre del archivo elegido antes de enviarlo
    if (campo.files.length > 0) {
        document.getElementById('texto-archivo').textContent = '📎 ' + campo.files[0].name;
        document.getElementById('archivo-activo').hidden = false;
    }
}

function cancelarArchivo() {
    document.getElementById('archivo').value = '';
    document.getElementById('archivo-activo').hidden = true;
}

function bajarAlUltimoMensaje() {
    // Coloca el scroll del chat al final para ver el mensaje más reciente
    var mensajes = document.querySelector('.chat-mensajes');

    if (mensajes) {
        mensajes.scrollTop = mensajes.scrollHeight;
    }
}

// Baja al cargar la página y otra vez cuando terminan de cargar las imágenes,
// porque al aparecer hacen el chat más alto
document.addEventListener('DOMContentLoaded', bajarAlUltimoMensaje);
window.addEventListener('load', bajarAlUltimoMensaje);
