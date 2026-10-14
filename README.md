# CMS-Editorial

Tema de WordPress para publicaciones digitales. La identidad, los colores y los servicios se administran desde WordPress; el código no incluye logos ni enlaces de una publicación de referencia.

## Configuración desde WordPress

Abre **Apariencia → CMS-Editorial** con una cuenta Administrador:

- **Identidad:** título y descripción nativos del sitio, logo principal, variante para modo oscuro, logos de acceso/panel, pie y reportes PDF, favicon, imagen para compartir, sufijo SEO y texto del pie.
- **Contacto y redes:** correo público, teléfono, dirección y enlaces sociales. Los enlaces vacíos no se muestran.
- **Radio:** nombre, descripción, URL de transmisión y activación. El reproductor está desactivado hasta configurarlo.
- **Portada:** slugs de las categorías locales. Los nombres se toman de las categorías de WordPress.
- **Colores:** fondos, texto, enlaces, acentos, navegación, pie, banners, última hora, botones, administrador y modo oscuro.

Todos estos ajustes también están disponibles en **Apariencia → Personalizar → CMS-Editorial** con vista previa. En **Identidad del sitio** están el logo principal, el favicon y el título/descripción nativos. Ambos caminos comparten los mismos valores. Se recomienda un icono cuadrado de al menos 512 × 512 píxeles.

Los colores de publicidad afectan al marco, los espacios vacíos y la cabecera del patrocinador. El contenido de una imagen publicitaria se cambia subiendo otra imagen al gestor **Publicidad**.

El idioma y la zona horaria se configuran en **Ajustes → Generales**. Las páginas de privacidad, cookies y términos muestran su contenido guardado en WordPress; edítalo en **Páginas**. El tema no impone una empresa, ubicación ni texto jurídico.

## Funciones editoriales

- Portada por secciones, búsqueda AJAX, navegación móvil, modo oscuro y carga de noticias.
- Publicidad por ubicación, patrocinadores por categoría y carruseles de banners.
- Carteles, clasificados y portada impresa programable.
- Formulario de contacto y gestión de mensajes.
- Metadatos SEO, Open Graph, imagen para compartir y controles por artículo.
- Roles editoriales, acceso administrativo y reportes PDF.
- Los administradores pueden publicar artículos individualmente o por lotes sin firma manual; se usa **Departamento de redacción** si está vacía. Los demás perfiles deben firmar. Las firmas existentes se conservan.

## Instalación y actualización

1. Copia la carpeta del tema en `wp-content/themes/cms-editorial/` y actívalo.
2. Configura identidad y colores en Apariencia → CMS-Editorial.
3. Asigna los menús, widgets y categorías de la publicación.
4. Sube logos y anuncios propios a la biblioteca de medios.

En una actualización, conserva la carpeta activa del tema para que WordPress mantenga sus `theme_mods`, menús y logo. Los datos editoriales no se borran. Las páginas que usaban la plantilla antigua de radio pasan a `page-radio.php` al entrar como administrador.

## Desarrollo

- PHP 8.1 o superior; WordPress con el personalizador clásico.
- `composer install` para las dependencias de reportes PDF (incluidas en `vendor/` para distribuir el tema).
- `npm ci && npm run build` para volver a compilar la barra lateral SEO del editor. Sus archivos compilados se incluyen en `build/`.
- Las pruebas de integración están en `tests/integration/white-label.php`. Se ejecutan con WP-CLI **únicamente en una instalación local desechable**, con `WP_ENVIRONMENT_TYPE=local`.

```sh
wp eval-file wp-content/themes/cms-editorial/tests/integration/white-label.php --user=administrador-local
```

## Compatibilidad

Las claves de opciones, metadatos, capacidades, namespaces y tablas de versiones anteriores se conservan internamente para no perder información ni romper integraciones. Los nombres públicos y recursos de identidad pertenecen a **CMS-Editorial** o a la publicación configurada. Las dependencias de terceros mantienen sus avisos y licencias originales.

## Estructura

- `inc/theme-settings.php`: opciones, validación, personalizador, CSS dinámico, logos y radio.
- `assets/js/theme-settings.js`: biblioteca de medios y selectores de color.
- `inc/seo/`: metadatos y herramientas SEO.
- `inc/reportes/`: reportes y PDF.
- `inc/ad-manager.php`: publicidad y patrocinadores.
- `inc/clasificados.php`: sistema de clasificados.
- `template-parts/`: módulos de portada y publicidad.

Versión **3.0.0**.
