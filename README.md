# HoloNet

Aplicación web de mensajería inspirada en WhatsApp, desarrollada como proyecto de Desarrollo Web en Entorno Servidor (PHP + MySQL).

## Funcionalidades implementadas

### Usuarios
- [x] **Registro** de nuevos usuarios con validación de datos y contraseña cifrada (`password_hash`).
- [x] **Inicio y cierre de sesión** con control de acceso a las páginas privadas.
- [x] **Username visible** en la cabecera de todas las páginas internas.
- [x] **Edición del perfil:** cambiar usuario, nombre, apellidos, contraseña y foto de perfil (JPG/PNG, máx. 2 MB).
- [x] **Fotos de perfil** visibles en la cabecera, la lista de amistades, las peticiones y el chat.

### Amistades
- [x] **Buscador de usuarios** por nombre de usuario, nombre o apellidos.
- [x] **Peticiones de amistad:** enviar, cancelar, aceptar y rechazar solicitudes.
- [x] **Notificación** con el número de peticiones pendientes recibidas.
- [x] **Lista de amistades** desde la que abrir cada conversación.

### Chat
- [x] **Envío de mensajes** dentro de cada conversación, con fecha y hora.
- [x] **Emisor de cada mensaje:** el nombre del amigo aparece encima de sus mensajes y «Tú» encima de los propios.
- [x] **Adjuntos:** enviar imágenes (JPG, PNG, GIF, WebP), audios (MP3, WAV, OGG, M4A) y archivos (PDF, TXT, ZIP, DOCX, XLSX) de hasta 20 MB, con o sin texto. Las imágenes se ven en el chat, los audios se reproducen y los archivos se descargan.
- [x] **Menú contextual** (clic derecho) sobre cada mensaje.
- [x] **Responder** a un mensaje concreto.
- [x] **Editar y eliminar** los mensajes propios.
- [x] **Reacciones con emojis** (👍 ❤️ 😂 😮).
- [x] **Fondos de pantalla:** 11 fondos predeterminados, guardados por usuario y conversación, con versión web y móvil.

### Identidad visual
- [x] **Nombre y logotipo propios:** HoloNet, con logo y favicon.

## Ideas fuera del MVP

Funcionalidades que no forman parte de esta versión, pero que podrían añadirse en el futuro:

- **Organización temporal:** agrupar los mensajes por día y mostrar sus horas de forma más clara.
- **Notas de voz:** grabar audio directamente desde el navegador (ahora se pueden enviar archivos de audio ya grabados).
- **Grupos de chat:** crear conversaciones grupales editables y completamente funcionales, con gestión de participantes y datos del grupo.
- **Actualización automática:** implementar *polling* con JavaScript para cargar mensajes nuevos sin tener que actualizar la página completa.
- **Gestión de contactos:** añadir las opciones de bloquear, desbloquear y eliminar usuarios o contactos.

## Instalación

1. Ejecutar [`database/esquema.sql`](database/esquema.sql), que crea la base de datos `db_whatsapp` y sus tablas.
2. Opcional: ejecutar [`database/datos_test.sql`](database/datos_test.sql) para cargar usuarios y conversaciones de prueba (contraseña de todos: `password`).

## Recursos generados por inteligencia artificial

Los fondos de conversación incluidos en [`assets/backgrounds`](assets/backgrounds) han sido generados mediante inteligencia artificial. Hay 11 opciones (predeterminado + 10 diseños), cada una en formato web (1920x1080) y móvil (1080x1920), en WebP.
