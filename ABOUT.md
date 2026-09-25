# Acerca de Espressivo Editorial

> Tema y framework de WordPress para diarios digitales y portales de noticias, construido a medida bajo un modelo de marca blanca (white-label) y SaaS.

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4?logo=php&logoColor=white)
![Versión](https://img.shields.io/badge/versi%C3%B3n-2.1.1-cc3332)
![Idioma](https://img.shields.io/badge/idioma-espa%C3%B1ol-ffc407)

---

## Ficha para el panel "About" de GitHub

GitHub guarda estos datos en la configuración del repositorio (icono ⚙️ junto a **About** en la portada del repo), no en un archivo. Estos son los valores listos para copiar:

| Campo | Valor |
|---|---|
| **Description** | Tema y framework editorial para WordPress: portales de noticias white-label/SaaS con SEO nativo integrado a Google Site Kit, gestor de publicidad, clasificados, carteles y edictos en PDF, portada del día y reportes editoriales. |
| **Website** | https://merchan.dev |
| **Topics** | `wordpress` `wordpress-theme` `news` `newspaper` `editorial` `white-label` `saas` `seo` `google-site-kit` `classifieds` `swup` `php` `venezuela` |

---

## Qué es

**Espressivo Editorial** es un tema de WordPress que funciona como plataforma completa para un medio de comunicación: portada editorial, secciones por categoría, publicidad, clasificados, avisos legales y herramientas para la redacción. Está pensado para duplicarse y licenciarse a distintas editoriales (marca blanca), con el menor número posible de plugins de terceros.

La instalación de referencia es **Diario El Oriental** (Maturín, estado Monagas, Venezuela).

## Módulos principales

| Módulo | Qué hace | Dónde vive |
|---|---|---|
| **Portada editorial** | Noticia principal, reja 60/40 con la portada impresa del día, secciones Premium, Local y "Más secciones". | `front-page.php`, `template-parts/home/` |
| **Secciones y listados** | Páginas de categoría con scroll infinito y archivos con "Cargar más", con una consulta compartida. | `page-categoria.php`, `category.php`, `template-parts/content/card.php` |
| **SSIVO-SEO** | Metadatos (title, description, Open Graph, Twitter), tabla indexable propia, panel lateral de legibilidad en Gutenberg y panel de métricas conectado a Google Site Kit. | `inc/seo/`, `src/seo/` |
| **Gestor de publicidad** | Banners de cabecera, in-feed y patrocinio por categoría, con rotación automática. | `inc/ad-manager.php`, `template-parts/ads/` |
| **Clasificados** | Tipo de contenido con tipos jerárquicos, buscador, imágenes y URL propias (`/clasificados/`). | `inc/clasificados.php`, `archive-clasificado.php`, `single-clasificado.php` |
| **Carteles y edictos** | Documentos legales en PDF con visor integrado. | `page-carteles.php`, `archive-cartel.php`, `single-cartel.php` |
| **Portada del día** | Programación de la portada impresa (reemplazo automático a las 05:00). | `inc/portada-dia.php` |
| **Reportes editoriales** | Informes de publicación por autor exportables a PDF (dompdf). | `inc/reportes/` |
| **Contacto** | Formulario AJAX propio, mensajes guardados como contenido privado y exportación a CSV. | `page-contacto.php`, `functions.php` |
| **Radio en vivo** | Reproductor flotante persistente y página dedicada (Vital 101.5 FM). | `footer.php`, `page-vital-101-5fm.php` |
| **Panel white-label** | Escritorio con marca propia, widgets editoriales, esquemas de color por rol, suspensión de usuarios. | `inc/admin-whitelabel.php`, `functions.php` |
| **Seguridad** | Acceso de inicio de sesión en `/turpial`, bloqueo de `wp-login.php`, nonces y saneamiento. | `inc/security.php` |

## Roles y permisos

| Rol | Alcance |
|---|---|
| **Administrador** | Control total, incluido el Site Kit completo y los ajustes del panel SEO. |
| **Dirección** | Gestión editorial completa (publicar, editar y borrar de otros, categorías) y reportes propios. |
| **Gerencia** | Gestión editorial, publicidad, carteles, mensajes de contacto y reportes. |
| **Publicista** | Publicidad y subida de archivos. |
| **Autor / Editor** | Redacción. La firma es obligatoria para publicar. |

Todos los roles, salvo *suscriptor*, ven el **panel SEO** con los datos de Google Site Kit. El Site Kit completo es exclusivo de los administradores.

## Tecnología

- **WordPress** 5.8 o superior (verificado en 7.1.2) y **PHP** 8.1 o superior.
- **JavaScript** sin frameworks en el frontend y **Swup 4** para navegar sin recargar (el reproductor de radio sigue sonando entre páginas).
- **React / `@wordpress/scripts`** para el panel SEO del editor (`src/seo/`, se compila con `npm run build`).
- **Composer**: `dompdf/dompdf` 3.x para los reportes PDF (incluido en `vendor/`).
- **Integración opcional**: Google Site Kit (Analytics 4 y Search Console).

## Estructura del repositorio

```text
espressivo-editorial/
├── style.css · functions.php · header.php · footer.php
├── front-page.php · index.php · single.php · page.php · 404.php
├── category.php · page-categoria.php
├── page-carteles.php · archive-cartel.php · single-cartel.php
├── archive-clasificado.php · single-clasificado.php · taxonomy-tipo_clasificado.php
├── page-contacto.php · page-vital-101-5fm.php · page-politica-*.php · page-terminos-*.php
├── inc/
│   ├── seo/            SSIVO-SEO y puente con Google Site Kit
│   ├── reportes/       Reportes editoriales en PDF
│   ├── clasificados.php · ad-manager.php · portada-dia.php
│   └── admin-whitelabel.php · security.php
├── template-parts/
│   ├── home/           Bloques de la portada
│   ├── content/        Tarjeta de noticia compartida
│   └── ads/            Publicidad in-feed y patrocinio
├── assets/             CSS, JS e imágenes
├── src/seo/            Código fuente React del panel SEO
└── vendor/             Dependencias de Composer (dompdf)
```

## Documentación

- [README.md](README.md): instalación, configuración y características.
- [HISTORIAL_DE_CAMBIOS.md](HISTORIAL_DE_CAMBIOS.md): registro cronológico de todos los commits.

## Autoría y licencia

Diseñado y desarrollado por **Arturo Merchán — [Merchan.Dev](https://merchan.dev)** y **Espressivo Venezuela, C.A.** Soporte: soporte@merchan.dev.

> **Nota sobre la licencia:** el repositorio declara licencias distintas. La cabecera de `style.css` indica *GPL v2 o posterior*, `composer.json` indica *proprietary* y los archivos del tema incluyen un aviso legal que prohíbe su reproducción, edición o venta. Conviene unificar la licencia en un único criterio.
