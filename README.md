# Espressivo Editorial

**Tema y framework de WordPress para diarios digitales y portales de noticias.** Construido desde cero bajo una arquitectura de marca blanca (white-label) y SaaS, para duplicarse y licenciarse a distintas editoriales con el menor número posible de plugins de terceros.

![Versión](https://img.shields.io/badge/versi%C3%B3n-2.1.0-cc3332)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4?logo=php&logoColor=white)
![Idioma](https://img.shields.io/badge/idioma-espa%C3%B1ol-ffc407)

Instalación de referencia: **Diario El Oriental** (Maturín, Monagas, Venezuela).

📄 [Acerca del proyecto](ABOUT.md) · 🕓 [Historial de cambios](HISTORIAL_DE_CAMBIOS.md)

---

## Contenido

1. [Características](#-características)
2. [Requisitos](#-requisitos)
3. [Instalación](#-instalación)
4. [Configuración](#-configuración)
5. [Actualizar una instalación existente](#-actualizar-una-instalación-existente)
6. [Roles y permisos](#-roles-y-permisos)
7. [Seguridad](#-seguridad)
8. [Rendimiento](#-rendimiento)
9. [Desarrollo](#-desarrollo)
10. [Solución de problemas](#-solución-de-problemas)
11. [Changelog](#-changelog)
12. [Créditos y licencia](#-créditos-y-licencia)

---

## ✨ Características

### Portada y secciones
- **Portada editorial:** noticia principal, reja 60/40 con la portada impresa del día y bloques por sección (Premium, Noticias Locales y Más Secciones).
- **Páginas de sección con scroll infinito** y archivos con botón **"Cargar más"**. La primera carga y las siguientes tandas usan la misma consulta, así que no se salta ni se repite ninguna noticia.
- **Navegación sin recarga (Swup):** el contenido cambia sin recargar la página y la radio sigue sonando. Scroll infinito, formularios, visores y sliders funcionan igual al llegar desde el menú.
- **Imagen en todos los bloques:** si una noticia no tiene imagen destacada (o está rota), se usa la primera imagen de su contenido.
- **Ticker "Último minuto"**, **modo oscuro**, **buscador predictivo AJAX** y **barra de progreso de lectura**.
- **Radio en vivo:** reproductor flotante persistente y página dedicada (Vital 101.5 FM).

### SSIVO-SEO y Google Site Kit
- **Metadatos propios:** `<title>`, meta descripción, canónica, Open Graph y Twitter Cards, sin plugins de SEO.
- **Tabla indexable** `ssivo_seo_indexable`: los datos SEO se guardan en una tabla propia y no en `wp_postmeta`.
- **Automatización:** meta descripción generada al guardar e imagen destacada asignada automáticamente desde el contenido (también en las entradas programadas).
- **Panel lateral en Gutenberg** con análisis de legibilidad en el navegador y vista previa del resultado en Google.
- **Panel SEO para todo el equipo:** visitas, usuarios, impresiones, contenidos más vistos, palabras clave, países y dispositivos de **Google Site Kit**, con caché de 4 horas y actualización automática cada hora.
- **Site Kit completo solo para administradores:** el resto de usuarios ve los datos en el panel SEO, que usa la cuenta de Google del administrador que conectó cada servicio.

### Redacción y control editorial
- **Firma obligatoria** para publicar, también desde el editor de bloques. Nunca despublica entradas que ya están publicadas o programadas.
- **Roles editoriales** propios (Dirección, Gerencia, Publicista) con capacidades mínimas.
- **Escritorio con marca propia:** widgets de portada del día, clientes, contactos recibidos, últimas publicaciones y actividad diaria de los autores.
- **Reportes editoriales en PDF** (dompdf) y **suspensión temporal de usuarios**.
- **Reparación de acentos y Ñ:** corrige el texto con codificación dañada en categorías, menús, títulos, contenido y firmas, y lo previene al guardar.

### Publicidad
- **Gestor de anuncios** para banners de cabecera, in-feed (entre noticias) y patrocinio por categoría o página, con rotación automática de slides.

### Clasificados
- Sección `/clasificados/` con **tipos jerárquicos** (Empleos, Inmuebles, Vehículos…), buscador, filtros y URL propias (`/clasificados-vehiculos/`).
- **Imágenes** en cada aviso (imagen destacada, bloques de imagen y galería), visibles en el listado y en el aviso.
- Enlace **"Clasificados"** en los menús principal y móvil.

### Carteles y edictos
- Tipo de contenido para documentos legales en PDF (`/carteles/`) con cuadrícula de miniaturas y **visor PDF integrado**.

### Portada del día
- Visor a pantalla completa con zoom (rueda y controles), arrastre y descarga.
- Programación del **reemplazo automático a las 05:00**.

### Contacto
- Formulario **100 % nativo** por AJAX, con validación, consentimiento de privacidad y filtros anti-spam.
- Los mensajes se guardan en un tipo de contenido privado y se exportan a **Excel/CSV**.

---

## 📋 Requisitos

| Componente | Versión |
|---|---|
| WordPress | 5.8 o superior (verificado en 7.1) |
| PHP | 8.1 o superior, con las extensiones `mbstring` y `dom` |
| Base de datos | MySQL o MariaDB con `utf8mb4` |
| Opcional | Google Site Kit (Analytics 4 y Search Console) para el panel SEO |
| Solo para desarrollo | Node.js y npm (panel SEO del editor), Composer (dependencias PDF) |

---

## 🛠 Instalación

1. **Copia el tema** en `wp-content/themes/espressivo-editorial/`. Las dependencias de PHP (`vendor/`) ya vienen incluidas.
2. **Actívalo** en *Apariencia → Temas*.
3. **Entra al escritorio como administrador.** La primera vez, el tema crea automáticamente:
   - las páginas legales, de contacto, de carteles y de cada sección (con la plantilla *Página de Categoría Automática*);
   - las categorías y subcategorías editoriales;
   - los menús principal y móvil, incluido el enlace a Clasificados;
   - la tabla `ssivo_seo_indexable` y los permisos del panel SEO.
4. **Guarda los enlaces permanentes** en *Ajustes → Enlaces permanentes → Guardar cambios* (activa las URL de clasificados y carteles).

---

## 🔧 Configuración

| Qué | Dónde |
|---|---|
| **Inicio de sesión** | `https://tu-dominio/turpial` (`wp-login.php` está bloqueado) |
| **Menús** | *Apariencia → Menús*: ubicaciones *Menú Principal (PC)*, *Menú Móvil (Teléfonos)*, *Menú del Pie de Página* y *Menú Superior* |
| **Google Site Kit** | Instala Site Kit y conéctalo **con una cuenta de administrador**. El panel **SEO** muestra sus datos a todo el equipo sin más configuración. |
| **Ajustes SEO** | *SEO → Ajustes globales* (solo administradores): sufijo del título, imagen por defecto para compartir y estado de la conexión con Site Kit |
| **Portada del día** | Menú *Portada del Día* |
| **Publicidad** | Menú *Publicidad* (banners de inicio y de categorías) |
| **Placeholders de anuncios** | *Apariencia → Personalizar → Publicidad*: "Mostrar placeholders vacíos" (desactívalo en producción) |
| **Mensajes de contacto** | Menú *Mensajes* → "Descargar Excel/CSV" |

---

## 🔄 Actualizar una instalación existente

1. Sustituye los archivos del tema por los de esta versión.
2. **Entra al escritorio como administrador.** Al hacerlo se ejecutan, una sola vez, las tareas de la 2.1.0:
   - reparación de acentos y Ñ en el contenido ya guardado (por lotes, sin tocar slugs ni URL);
   - alta de "Clasificados" en los menús principal y móvil;
   - actualización de los permisos del panel SEO.
3. **Vacía la caché** de página, CDN u objeto si la usas (LiteSpeed, Hostinger, etc.).

---

## 👥 Roles y permisos

| Rol | Alcance |
|---|---|
| **Administrador** | Control total, incluido Google Site Kit completo y los ajustes del panel SEO. |
| **Dirección** | Gestión editorial completa (publicar, editar y borrar de otros, categorías) y sus propios reportes. Sin ajustes del sitio. |
| **Gerencia** | Gestión editorial, publicidad, carteles, mensajes de contacto y reportes. |
| **Publicista** | Publicidad y subida de archivos. |
| **Autor / Editor** | Redacción, con firma obligatoria. |

Todos los roles excepto *suscriptor* tienen acceso al **panel SEO**. Las pantallas de administración del sitio (plugins, temas, ajustes, usuarios) están bloqueadas para los roles editoriales.

---

## 🔐 Seguridad

- **Inicio de sesión en `/turpial`**: las visitas a `wp-login.php` y a `wp-admin` sin sesión se redirigen a la portada.
- **Saneamiento y escape** de toda entrada y salida (`sanitize_*`, `esc_*`, `wp_json_encode` en JSON-LD).
- **Nonces** en formularios y en las acciones con sesión iniciada. Los endpoints públicos de solo lectura (cargar más, buscador, aviso de noticias nuevas) no dependen del nonce, para seguir funcionando con páginas en caché.
- **Capacidades mínimas** por rol, protección real de las pantallas de administración y bloqueo de acciones sobre administradores.
- **Sin comentarios**: están desactivados en todo el sitio.
- **Sitemap de usuarios desactivado**.

---

## ⚡ Rendimiento

- **Transients:** `pro_latest_post_date` (60 s) para el aviso de noticias nuevas y `pro_ticker_posts` (5 min) para el ticker. Se invalidan al publicar, actualizar o cambiar categorías.
- **Cache busting** con `filemtime()` en los recursos del tema.
- **Scripts diferidos** (`defer`) y carga diferida nativa de imágenes.
- **Tamaños de imagen propios:** `hero-thumbnail` (1200×675) y `card-thumbnail` (600×400).
- **Consultas acotadas:** solo los campos necesarios y orden determinista (fecha + ID).

---

## 🧑‍💻 Desarrollo

### Estructura

```text
inc/seo/            SSIVO-SEO y puente con Google Site Kit (class-site-kit-bridge.php)
inc/reportes/       Reportes editoriales en PDF
inc/*.php           Clasificados, publicidad, portada del día, white-label y seguridad
template-parts/     Bloques de la portada, tarjeta de noticia y publicidad
assets/             CSS, JS e imágenes
src/seo/            Código fuente React del panel SEO del editor
vendor/             Dependencias de Composer (dompdf)
```

La descripción completa de cada módulo está en [ABOUT.md](ABOUT.md).

### Compilar el panel SEO del editor

```bash
npm install
npm run build     # genera build/ (no se versiona)
npm run start     # modo desarrollo con recarga
```

### Dependencias de PHP

```bash
php composer.phar install   # solo si actualizas dompdf
```

### Convenciones

- Código, comentarios y textos de la interfaz en **español**, con dominio de traducción `pro`.
- Las correcciones importantes se documentan en el propio código con un comentario *"Auditoría fix"* que explica la causa.
- Cada cambio se registra en [HISTORIAL_DE_CAMBIOS.md](HISTORIAL_DE_CAMBIOS.md) y en el changelog de abajo.

---

## 🩺 Solución de problemas

| Síntoma | Qué revisar |
|---|---|
| El panel SEO muestra "No disponible" | Que Google Site Kit esté activo y que un administrador haya conectado Analytics y Search Console. El estado aparece en *SEO → Ajustes globales*. |
| "Google Site Kit no está activo" | El plugin está desactivado o no está instalado. |
| Una entrada no se publica | Falta la **firma**: escribe el nombre en la caja *Firma* y publica de nuevo. |
| Clasificados o carteles devuelven 404 | Guarda de nuevo los enlaces permanentes. |
| Siguen viéndose caracteres extraños (`Ã³`, `Ã±`) | Entra al escritorio como administrador para completar la reparación, que avanza por lotes, y vacía la caché. |
| Los cambios de diseño no aparecen | Vacía la caché de página o CDN. |

---

## 📜 Changelog

> Historial detallado commit a commit en [HISTORIAL_DE_CAMBIOS.md](HISTORIAL_DE_CAMBIOS.md).

### v2.1.0 — Auditoría (25 de septiembre de 2026)
- ✅ **Entradas que desaparecían:** la validación de firma ya no pasa a Borrador entradas publicadas o programadas al editarlas (edición rápida o masiva, REST, plugins). En el editor de bloques la firma viaja en la misma petición de publicación.
- ✅ **Listados completos:** la primera página y el scroll infinito o "Cargar más" comparten consulta (tamaño, orden fecha + ID, subcategorías) y avanzan por desplazamiento; ya no se saltan noticias.
- ✅ **Navegación Swup:** scroll infinito, formulario de contacto, visores, sliders y radio funcionan también al llegar a una página desde el menú; solo se re-ejecutan los scripts del contenido nuevo.
- ✅ **Imágenes en widgets:** si falta la imagen destacada (o está rota) se usa la primera imagen del contenido; la asignación automática reconoce las imágenes redimensionadas, también en las programadas.
- ✅ **SEO + Site Kit:** el panel SEO muestra los datos de Site Kit a todos los usuarios con las credenciales del administrador que conectó cada servicio; el Site Kit completo queda solo para administradores.
- ✅ **Acentos y Ñ:** reparación segura del texto con codificación dañada (categorías, menús, títulos, contenido, firmas y datos SEO) y protección al guardar.
- ✅ **Clasificados:** entrada en los menús principal y móvil, imagen destacada y bloques de imagen y galería visibles en el listado y en el aviso.
- ✅ **Documentación:** ABOUT.md, historial de cambios y README renovado. Cabeceras de requisitos en `style.css`.

### v2.0.0 — Refactorización Espressivo e integración SSIVO-SEO
- ✅ **Marca blanca:** nomenclatura genérica ("Espressivo", "MerchanDev").
- ✅ **SSIVO-SEO integrado:** motor de posicionamiento propio (React + PHP) sin plugins de terceros.
- ✅ **Tabla indexable autónoma** `ssivo_seo_indexable`.
- ✅ **Panel lateral en Gutenberg** para calcular la legibilidad en tiempo real.

### v1.3.0 — Mejoras estructurales y legales
- ✅ Plantillas autogenerables de "Términos y condiciones" y "Política de cookies".
- ✅ Reorganización del pie y retirada de metaetiquetas de la portada.

### v1.2.0 — Auditoría y funcionalidades
- ✅ Lightbox interactivo con zoom y desplazamiento para portadas.
- ✅ Buscador predictivo AJAX con validaciones de seguridad.
- ✅ Ticker "Último minuto" con caché en transients.

### v1.1.0 — Auditoría de seguridad
- ✅ Saneamiento completo de entradas, nonces y guards `ABSPATH`.
- ✅ URL de inicio de sesión personalizada `/turpial` con coincidencia exacta.

---

## 🤝 Créditos y licencia

Diseñado y desarrollado por **Arturo Merchán — [Merchan.Dev](https://merchan.dev)** y **Espressivo Venezuela, C.A.** · Soporte: soporte@merchan.dev

Framework editorial SaaS para medios digitales e impresos. Consulta las condiciones de uso y la nota de licencia en [ABOUT.md](ABOUT.md#autoría-y-licencia).
