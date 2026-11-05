CHAT ONLINE — FONDOS RESPONSIVE

IMPORTANTE: TODOS LOS FONDOS DE ESTA CARPETA HAN SIDO GENERADOS MEDIANTE INTELIGENCIA ARTIFICIAL.

11 opciones (default + 10 diseños), cada una en WEB (1920x1080) y MOVIL (1080x1920).
Formato WebP optimizado. Todos los fondos son claros para mantener los globos de mensajes existentes.

El backend debe guardar solo el ID, por usuario y conversación. Ver fondos.json.

Ejemplo CSS:
.chat-messages { background-image: url("/assets/wallpapers/default-web.webp"); background-size: cover; background-position: center; }
@media (max-width: 768px) { .chat-messages { background-image: url("/assets/wallpapers/default-movil.webp"); } }

Para cada fondo seleccionado, aplicar la ruta correspondiente a WEB/MOVIL con variables CSS o clases generadas en PHP. Validar siempre el ID contra la lista permitida.
