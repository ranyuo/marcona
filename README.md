# Portal IES “Luis Felipe de las Casas Grieve” — Marcona

Archivos para publicar en Hostinger:

- `portal-private/`: código PHP, vistas y datos. Va **fuera** de `public_html`, al mismo nivel.
- `public_html/`: raíz pública del dominio, con la página, la intranet, el CSS, el JavaScript y las imágenes.

El archivo `portal-private/config.php` (datos de la base de datos y clave de instalación) **no se guarda en GitHub**: el repositorio es público. Se crea a partir de `portal-private/config.example.php`.

## Requisitos del hosting

- **PHP 8.1 o superior** con las extensiones `pdo_mysql`, `fileinfo`, `openssl`, `zlib`, `json` y `session` (Hostinger las trae activas). `mbstring` es recomendable.
- **MariaDB 10.4+ o MySQL 8** (en hPanel, «MySQL» es MariaDB). **Una base de datos por sitio:** las tablas usan el prefijo fijo `pisco_`.
- El sitio debe estar en la **raíz de un dominio o subdominio** (no en una subcarpeta).
- Carpetas con permiso de escritura: `portal-private/` (bloqueo de instalación, `backups/` y la configuración SMTP guardada desde la intranet) y `public_html/assets/cache/`.

## Instalación nueva

1. **Base de datos:** en hPanel › *Bases de datos › Bases de datos MySQL*, crea una base y un usuario con su contraseña. Anota los tres datos.
2. **Descarga el código:** en GitHub, *Code › Download ZIP*.
3. **Sube y extrae:** con el *Administrador de archivos*, sube el ZIP a la carpeta del dominio (la que contiene `public_html`) y extráelo. GitHub mete todo en una carpeta `marcona-main/`; mueve `portal-private/` y `public_html/` desde ahí a la carpeta del dominio y reemplaza el `public_html` vacío.
4. **Configura:** copia `portal-private/config.example.php` como `portal-private/config.php` y completa:
   - `db_name`, `db_user` y `db_password`;
   - `install_key`: una clave privada aleatoria de 32 caracteres o más. Guárdala.
5. **Instala:** abre `https://tu-dominio/install.php`, escribe la `install_key` y crea la cuenta del administrador principal.
6. **Borra** `public_html/install.php`. Después de instalar ya no sirve: si lo vuelves a abrir responde «El portal ya está instalado» (código 410). Mientras el portal no esté instalado, la portada muestra un aviso que indica abrir `/install.php`.
7. **Permisos:** `public_html/assets/cache/` debe tener permisos **755**.
8. **Comprueba:**
   - Abre el sitio y entra a la intranet con la cuenta creada.
   - Revisa *Configuración*: datos institucionales y logo.
   - En *Mesa de partes › Áreas receptoras › Editar áreas*, carga las áreas reales con sus correos.
   - En *Usuarios y roles*, crea las cuentas del personal.

**Correo de notificaciones (recomendado):** en la intranet, *Configuración › Configurar correo notificador (SMTP)*, indica el servidor, la cuenta y su contraseña de aplicación y activa el envío. Después, en hPanel › *Avanzado › Trabajos cron*, crea una tarea **cada minuto** con `php /home/<usuario>/domains/<dominio>/portal-private/mail-worker.php` para los reintentos. Detalles en [MEJORAS-MODULOS.md](MEJORAS-MODULOS.md).

**Opcional, respaldos automáticos:** en hPanel › *Avanzado › Trabajos cron*, crea una tarea diaria con `php /home/<usuario>/domains/<dominio>/portal-private/cron.php`. Luego actívala en la intranet, en *Copias de seguridad*.

**Retención de la auditoría:** el historial de auditoría se conserva 24 meses y lo más antiguo se elimina una vez al día, desde `mail-worker.php` o `cron.php` (basta con que una de las dos tareas esté programada). Para cambiar el plazo, define `'audit_retention_months'` en `config.php` (mínimo 6; `0` lo conserva sin límite). Cada depuración queda registrada en la propia auditoría como «Depurar auditoría».

## Actualizar una instalación existente

Sube `portal-private/` y `public_html/` encima de las carpetas actuales y acepta **sobrescribir**. **No borres las carpetas antes:** así se conservan `config.php`, `backups/` y la configuración SMTP guardada (`.smtp-settings.json`). Después, borra `public_html/install.php`.

La base de datos se actualiza sola la primera vez que se abre el sitio con la versión nueva (por ejemplo, crea la cola de correo `pisco_mail`); no se pierden datos ni cuentas.

## Dónde se edita

- **CSS y JavaScript del portal:** `public_html/assets/app/` (`portal.css` y `portal-1.js` a `portal-4.js`).
- **Armazón de la página:** `portal-private/views/portal.php`.
- **Contenido inicial** que carga la instalación: `portal-private/data/portal.json`.
- **Imágenes y documentos:** `public_html/assets/marcona/`. Las imágenes grandes tienen una copia `.webp` que el servidor entrega a los navegadores compatibles.

### Diseños, carrusel de programas y galería de noticias

- **Apariencia del portal:** en la intranet, *Apariencia del portal* (botón «Apariencia» de la barra superior), en tres pasos:
  1. **Estructura de la página:** **Clásico institucional**, **Moderno**, **Universitario**, **Editorial**, **Impacto**, **Horizonte** o **Campus**. Solo cambia la forma: cabecera, portada y bloques. *Universitario* sigue la estructura de un portal universitario moderno; *Impacto*, la de un portal de alto contraste (referencia de diseño: upn.edu.pe), con logotipo en bloque del color destacado y títulos condensados; *Horizonte*, la de un portal de universidad moderna (referencia de diseño: cientifica.edu.pe). Los bloques siguen el orden y la visibilidad configurados en *Portada*.
  2. **Colores y letras:** la combinación en uso (color institucional, color de botones y enlaces y color destacado, letras y esquinas) y las demás combinaciones. **Todas las estructuras usan estos colores.** Con una combinación oscura (*Noche*), Impacto, Campus, Horizonte y Editorial muestran el sitio público con los tonos claros de esa combinación, porque no tienen versión oscura; el panel sí queda oscuro.
  3. **Opciones avanzadas** (plegado): colores propios para una sola estructura. Normalmente no hace falta; si tiene valores propios aparece «Personalizado» y «Restablecer» vuelve a los colores del paso 2.
  
- **Texto junto al logotipo:** si *Configuración › Texto del logotipo generado* está vacío, las estructuras Impacto, Horizonte y Campus muestran el nombre corto oficial completo, sin abreviar.
- **Programas de estudio en carrusel:** se muestran en carrusel por omisión, en la portada y en la página de programas. Para cambiar a cuadrícula, usa *Vista en la web* en el módulo *Programas de estudio* o *Presentación de los programas de estudio* en *Portada › Bloques, accesos y cifras*.
- **Noticias con varias fotos:** cada noticia admite la imagen principal y hasta 4 fotos adicionales (5 en total). En la web se muestran como galería con visor ampliado.

El sistema es una copia del portal de Nasca (`ranyuo/claudenasca`, commit `bf8264c`) adaptada con la información pública de https://www.iestmarcona.edu.pe/, consultada el 30 de octubre de 2026. Las imágenes y los PDF se sirven desde `public_html/assets/marcona/`, no desde la web original. El código se actualizó el 9 de noviembre de 2026 al sistema v2 de `ranyuo/v2_flv`, conservando el contenido, el tema y las imágenes de Marcona.

## Usar este sistema para otro instituto

El código es el mismo para todos los institutos; lo propio de cada uno está en la base de datos (editable desde la intranet) y en el contenido inicial.

1. **Contenido inicial:** `portal-private/data/portal.json` es el de Marcona (personal, noticias, TUPA, enlaces, colores). Reemplázalo por el del nuevo instituto antes de instalar, o instala y edita todo desde la intranet. Las imágenes y los PDF de `public_html/assets/marcona/` son de FLV: no los publiques en otro sitio (algunos PDF contienen datos de postulantes); deja solo `sin-foto.jpg` y los archivos del nuevo instituto.
2. **Base de datos propia** y `config.php` propio, con una `install_key` nueva (no copies `portal-private/` de otro sitio: la configuración SMTP guardada está cifrada con esa clave e incluye la cuenta del otro instituto). Opcionalmente, la ruta del archivo de configuración se puede indicar con la variable de entorno `PORTAL_CONFIG_FILE`.
3. Instala como se indica arriba y completa en la intranet:
   - *Configuración*: nombre, nombre corto, sigla del logo, logo, dirección, **distrito y región** (se usan en el pie de la intranet, en los títulos emitidos y en los datos para buscadores), teléfono, correo, redes, SEO y WhatsApp.
   - *Apariencia del portal*: estructura, colores y letras.
   - *Enlaces y servicios*: DRE o GRE de la región (el pie muestra el nombre del enlace `dre`), plataforma virtual, correo y biblioteca.
   - *Mesa de partes*: prefijo de numeración, correo, teléfono, áreas y feriados regionales. Los feriados nacionales del Perú (incluida Semana Santa) se aplican solos cada año.
   - *Libro de reclamaciones*, *Bolsa laboral* y el correo notificador (SMTP).
4. Programa los trabajos cron de `mail-worker.php` (cada minuto) y `cron.php` (diario).

## Datos que debe confirmar la institución

- Horario de atención (la web solo indica «turno diurno»).
- Responsables y correos de las áreas receptoras de Mesa de partes (se cargaron los del directorio institucional).
- Tasas: se cargó la «Relación de derechos, tasas y pagos» publicada (montos de 2024).
- Admisión y matrícula: el último proceso publicado es el de 2025.
