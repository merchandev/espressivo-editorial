# CMS-Editorial

Tema de WordPress para publicaciones digitales. La identidad, los colores y los servicios se administran desde WordPress; el código no incluye logos ni enlaces de una publicación de referencia.

## Secciones editoriales

La estructura inicial tiene seis categorías, sin subcategorías: **Nacional, Deportes, Internacional, Farándula, Ciencias y Genéricas**. Los menús principal y móvil muestran Inicio y esas seis secciones; la portada y el pie usan el mismo orden.

Al actualizar a 3.2.0, una migración única se inicia al entrar en WordPress como administrador. Conserva todas las entradas, sus estados, autores y firmas. Agrupa Mundo y sus equivalentes en Internacional; las secciones de entretenimiento en Farándula; Ciencia y Tecnología y sus equivalentes en Ciencias. Las entradas de categorías retiradas conservan sus otras secciones válidas y, cuando no tienen ninguna, pasan a Genéricas. Genéricas es la categoría predeterminada.

La migración procesa hasta 100 entradas por petición y continúa automáticamente desde el administrador. Conserva un registro previo de categorías, asignaciones, menús, páginas y publicidad en la opción `cms_editorial_sections_backup_v1`, sin carga automática en el frontal. Los antiguos archivos de categoría redirigen a sus secciones correspondientes; las páginas vacías de secciones retiradas pasan a borrador. Contacto, clasificados, carteles y páginas legales siguen disponibles como servicios.

La reorganización se aplica una vez. Después puedes editar categorías, menús y portada desde WordPress; las visitas posteriores no restablecen la estructura ni vuelven a crear las categorías anteriores.

## Configuración desde WordPress

Abre **Apariencia → CMS-Editorial** con una cuenta Administrador:

- **Identidad:** título y descripción nativos del sitio, logo principal, variante para modo oscuro, logos de acceso/panel, pie y reportes PDF, favicon, imagen para compartir, sufijo SEO y texto del pie.
- **Contacto y redes:** correo público, teléfono, dirección y enlaces sociales. Los enlaces vacíos no se muestran.
- **Radio:** nombre, descripción, URL de transmisión y activación. El reproductor está desactivado hasta configurarlo.
- **Portada:** encabezado, categoría de destacados opcional, orden de secciones, secciones adicionales y activación de edición impresa/servicios. Los nombres de sección se toman de WordPress. Si los destacados están vacíos, se usan las últimas noticias.
- **Colores:** fondos, texto, enlaces, acentos, navegación, pie, banners, última hora, botones, administrador y modo oscuro.

El panel se organiza por pestañas, con navegación por teclado y guardado común. Todos estos ajustes también están disponibles en **Apariencia → Personalizar → CMS-Editorial** con vista previa. En **Identidad del sitio** están el logo principal, el favicon y el título/descripción nativos. Ambos caminos comparten los mismos valores. Se recomienda un icono cuadrado de al menos 512 × 512 píxeles.

Los colores de publicidad afectan al marco, los espacios vacíos y la cabecera del patrocinador. El contenido de una imagen publicitaria se cambia subiendo otra imagen al gestor **Publicidad**.

El idioma y la zona horaria se configuran en **Ajustes → Generales**. Las páginas de privacidad, cookies y términos muestran su contenido guardado en WordPress; edítalo en **Páginas**. El tema no impone una empresa, ubicación ni texto jurídico.

## Diseño y experiencia de lectura

- Portada modular con noticia principal, últimas actualizaciones, secciones y servicios.
- Cabecera adaptable, menú móvil con cierre por Escape, búsqueda predictiva y atajo `/`.
- Artículos con firma, tiempo de lectura, controles de tamaño de texto, compartir y copiar enlace.
- Visor de edición impresa con navegación por teclado, retorno del foco y zoom.
- Titulares rotativos con pausa manual y respeto a la preferencia de movimiento reducido.
- Archivos y resultados de búsqueda con tarjetas compartidas y carga de más noticias.
- Colores y modos de apariencia vinculados a la configuración de marca blanca.

Los enlaces y la jerarquía se editan en **Apariencia → Menús**. Asigna **Menú Principal (PC)** y, si lo necesitas, **Menú Móvil (Teléfonos)**. Si no hay un menú móvil asignado, el panel usa el principal. El menú de escritorio pasa automáticamente a panel cuando sus enlaces no caben. En móvil y tablet las subsecciones se abren con su botón de flecha; pulsar el nombre de una sección lleva a su página. Los desplegables largos del escritorio se distribuyen en columnas.

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
- Las pruebas de integración están en `tests/integration/`. Se ejecutan con WP-CLI **únicamente en una instalación local desechable**, con `WP_ENVIRONMENT_TYPE=local`.

```sh
wp eval-file wp-content/themes/cms-editorial/tests/integration/white-label.php --user=administrador-local
wp eval-file wp-content/themes/cms-editorial/tests/integration/editorial-ui.php --user=administrador-local
wp eval-file wp-content/themes/cms-editorial/tests/integration/navigation.php --user=administrador-local
```

`tests/browser/navigation.py` verifica el menú con Python, Playwright y Microsoft Edge en una instalación local. Incluye ramas temporales en el DOM de prueba para comprobar acordeones y menús largos, además de foco, modo oscuro, búsqueda y navegación. `tests/browser/sections.py` comprueba la estructura real de seis secciones, sus páginas, redirecciones y presentación móvil. El parámetro opcional `--screenshots` guarda capturas fuera del repositorio:

```sh
python tests/browser/navigation.py --url http://127.0.0.1:8093 --screenshots /ruta/temporal/capturas
python tests/browser/sections.py --url http://127.0.0.1:8093 --screenshots /ruta/temporal/capturas
```

`tests/integration/sections.php` prueba la migración con más de 200 entradas, permisos, conservación de datos y repetición de instaladores. Requiere una **copia desechable de la base**, además de `CMS_EDITORIAL_STRUCTURE_TEST=true` definido antes de cargar WordPress; reorganiza sus categorías y crea datos de prueba.

## Compatibilidad

Las claves de opciones, metadatos, capacidades, namespaces y tablas de versiones anteriores se conservan internamente para no perder información ni romper integraciones. Los nombres públicos y recursos de identidad pertenecen a **CMS-Editorial** o a la publicación configurada. Las dependencias de terceros mantienen sus avisos y licencias originales.

## Estructura

- `inc/theme-settings.php`: opciones, validación, personalizador, CSS dinámico, logos y radio.
- `inc/sections.php`: estructura inicial, migración por lotes, menús y redirecciones de categorías anteriores.
- `assets/js/theme-settings.js`: pestañas, biblioteca de medios y selectores de color.
- `inc/editorial-ui.php`, `assets/css/editorial-ui.css` y `assets/js/editorial-ui.js`: presentación editorial y herramientas de lectura.
- `inc/navigation.php`, `assets/css/navigation.css` y `assets/js/navigation.js`: menús de WordPress, panel responsive y acordeones.
- `inc/seo/`: metadatos y herramientas SEO.
- `inc/reportes/`: reportes y PDF.
- `inc/ad-manager.php`: publicidad y patrocinadores.
- `inc/clasificados.php`: sistema de clasificados.
- `template-parts/`: módulos de portada y publicidad.

Versión **3.2.0**.
