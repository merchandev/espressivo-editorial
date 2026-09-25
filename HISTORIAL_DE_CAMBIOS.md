# Historial de cambios — Espressivo Editorial

Registro cronológico de **todos los commits** del repositorio [`merchandev/espressivo-editorial`](https://github.com/merchandev/espressivo-editorial), de todas las ramas, ordenados del más antiguo al más reciente.

- **Horas** en hora de Venezuela (VET, UTC−4). Es la zona horaria que fija el propio tema.
- **Cifras** de líneas añadidas (+) y eliminadas (−) según `git`.
- Para el resumen por versiones consulta también el *Changelog* del [README](README.md). La descripción general del proyecto está en [ABOUT.md](ABOUT.md).

---

## Índice

1. [Resumen en cifras](#resumen-en-cifras)
2. [Línea de tiempo](#línea-de-tiempo)
3. [Mapa de ramas](#mapa-de-ramas)
4. [Antecedentes (antes del repositorio)](#antecedentes-antes-del-repositorio)
5. [Detalle por commit](#detalle-por-commit)
   - [13 de agosto de 2026](#13-de-agosto-de-2026)
   - [25 de septiembre de 2026](#25-de-septiembre-de-2026)
6. [Estado de ramas y pull requests](#estado-de-ramas-y-pull-requests)
7. [Observaciones pendientes](#observaciones-pendientes)
8. [Cómo añadir nuevas entradas](#cómo-añadir-nuevas-entradas)

**Leyenda de tipos:** 🟢 Inicial · 🔧 Corrección · ✨ Mejora · 🔀 Fusión · 📝 Documentación

---

## Resumen en cifras

| Indicador | Valor |
|---|---|
| Commits registrados | 15 |
| Ramas | `main`, `claude/laughing-planck-4m793a`, `fix/auditoria-editorial-2026-09-25` |
| Pull requests | 2 (#2 fusionado, #1 abierto) |
| Periodo | 13/08/2026 → 25/09/2026 |
| Versión actual del tema | **2.1.4** |
| Autores | Merchan.dev (7 commits) · Claude / Claude Code (8 commits) |

---

## Línea de tiempo

| # | Fecha y hora (VET) | Commit | Rama | Autor | Tipo | Título |
|---|---|---|---|---|---|---|
| 1 | 13/08/2026 09:49 | [`eaf6786`](https://github.com/merchandev/espressivo-editorial/commit/eaf6786) | `main` | merchandev | 🟢 | Update SEO dashboard and credits |
| 2 | 25/09/2026 07:32 | [`d7a9210`](https://github.com/merchandev/espressivo-editorial/commit/d7a9210) | `claude/laughing-planck-4m793a` | Claude | 🔧 ✨ | Auditoría: entradas ocultas, widgets, SEO/Site Kit, acentos y clasificados |
| 3 | 25/09/2026 07:34 | [`74fe87e`](https://github.com/merchandev/espressivo-editorial/commit/74fe87e) | `fix/auditoria-editorial-2026-09-25` | Merchan.dev | 🔧 | Fix editorial listings, SEO visibility, widgets and classifieds |
| 4 | 25/09/2026 07:34 | [`195c0ea`](https://github.com/merchandev/espressivo-editorial/commit/195c0ea) | `fix/auditoria-editorial-2026-09-25` | Merchan.dev | 🔧 | Load editorial audit fixes |
| 5 | 25/09/2026 07:34 | [`986b9cd`](https://github.com/merchandev/espressivo-editorial/commit/986b9cd) | `fix/auditoria-editorial-2026-09-25` | Merchan.dev | 🔧 | Unify category pagination with AJAX |
| 6 | 25/09/2026 07:35 | [`31e26db`](https://github.com/merchandev/espressivo-editorial/commit/31e26db) | `fix/auditoria-editorial-2026-09-25` | Merchan.dev | ✨ | Render classified featured images |
| 7 | 25/09/2026 07:35 | [`58a6204`](https://github.com/merchandev/espressivo-editorial/commit/58a6204) | `fix/auditoria-editorial-2026-09-25` | Merchan.dev | ✨ | Show classified featured image on single view |
| 8 | 25/09/2026 07:54 | [`7754bef`](https://github.com/merchandev/espressivo-editorial/commit/7754bef) | `main` | Merchan.dev | 🔀 | Merge pull request #2 |
| 9 | 25/09/2026 08:01 | [`a97109f`](https://github.com/merchandev/espressivo-editorial/commit/a97109f) | `claude/laughing-planck-4m793a` | Claude | 📝 | Documentación: ABOUT.md e historial de cambios |
| 10 | 25/09/2026 08:07 | [`afa897d`](https://github.com/merchandev/espressivo-editorial/commit/afa897d) | `claude/laughing-planck-4m793a` | Claude | ✨ 📝 | Versión 2.1.0, README renovado y menú de Clasificados en instalaciones nuevas |
| 11 | 25/09/2026 08:13 | [`8678283`](https://github.com/merchandev/espressivo-editorial/commit/8678283) | `main` | Claude | 📝 | README: acceso rápido a la documentación; todo integrado en `main` |
| 12 | 25/09/2026 08:29 | [`ecb6673`](https://github.com/merchandev/espressivo-editorial/commit/ecb6673) | `claude/laughing-planck-4m793a` | Claude | 🔧 | v2.1.1: cifras del panel SEO alineadas con Site Kit |
| 13 | 25/09/2026 08:43 | [`f3df61f`](https://github.com/merchandev/espressivo-editorial/commit/f3df61f) | `claude/laughing-planck-4m793a` → `main` | Claude | 🔧 📝 | v2.1.2: revisión completa, listados y migración de acentos |
| 14 | 25/09/2026 09:26 | [`914109c`](https://github.com/merchandev/espressivo-editorial/commit/914109c) | `claude/laughing-planck-4m793a` → `main` | Claude | 🔧 ✨ | v2.1.3: panel SEO con los mismos datos que Site Kit |
| 15 | 25/09/2026 | *(este commit)* | `claude/laughing-planck-4m793a` → `main` | Claude | ✨ | v2.1.4: banner de cabecera un 15 % más grande |

Otros eventos del 25/09/2026: el **PR #1** se abrió a las 07:39 y el **PR #2** a las 07:52; el PR #2 se fusionó en `main` a las 07:54.

---

## Mapa de ramas

```text
main ──●──────────────────────────────────●── 7754bef ──● a97109f ──● afa897d ──● 8678283 ──● ecb6673 ──● f3df61f ──● 914109c ──● commit 15 ── main
      eaf6786 \                          /   (PR #2)     (documentación, v2.1.0 y README)   (v2.1.1)    (v2.1.2)    (v2.1.3)    (v2.1.4)
               ├── claude/laughing-planck-4m793a
               │    ● d7a9210 (auditoría)
               │
               └── fix/auditoria-editorial-2026-09-25
                    ● 74fe87e → ● 195c0ea → ● 986b9cd → ● 31e26db → ● 58a6204   (PR #1, abierto)
```

Los commits 9 a 15 se hicieron en `claude/laughing-planck-4m793a` sobre `7754bef` y se integraron en `main` por avance rápido (*fast-forward*), sin commit de fusión: ambas ramas apuntan al mismo commit.

---

## Antecedentes (antes del repositorio)

Estas versiones son anteriores al control de versiones. No tienen commits propios: todo su trabajo quedó consolidado en el commit inicial `eaf6786`. Se documentan según el *Changelog* del README.

| Versión | Contenido |
|---|---|
| **v1.1.0** — Auditoría de seguridad | Saneamiento completo de entradas, nonces y *guards* `ABSPATH`. URL de inicio de sesión personalizada `/turpial` con coincidencia exacta. |
| **v1.2.0** — Auditoría y funcionalidades | Lightbox con zoom y desplazamiento para portadas. Buscador predictivo AJAX con validaciones. Ticker "Último minuto" con caché en *transients*. |
| **v1.3.0** — Mejoras estructurales y legales | Plantillas autogenerables de "Términos y condiciones" y "Política de cookies". Reorganización del pie y retirada de metaetiquetas de la portada. |
| **v2.0.0** — Refactorización Espressivo e integración SSIVO-SEO | Marca blanca con nomenclatura genérica. Motor SSIVO-SEO (React + PHP) sin plugins. Tabla indexable `ssivo_seo_indexable`. Panel lateral de legibilidad en Gutenberg. |

---

## Detalle por commit

### 13 de agosto de 2026

#### 1 · `eaf6786` — Update SEO dashboard and credits 🟢

| | |
|---|---|
| **Rama** | `main` (commit raíz, sin padres) |
| **Autor** | merchandev · soporte@merchan.dev |
| **Fecha** | 13/08/2026 09:49:47 VET |
| **Cambios** | 718 archivos · +255.405 líneas |

Primer commit del repositorio. Incorpora el tema completo en su versión **2.0.0**, con el panel SEO y los créditos actualizados.

**Contenido incorporado**

| Grupo | Archivos |
|---|---|
| Tema (sin dependencias) | 74 archivos: 47 PHP, 7 JS, 7 CSS, 6 imágenes, 3 JSON, `README.md`, `.gitignore`, `composer.lock`, `composer.phar` |
| Dependencias (`vendor/`) | 644 archivos de Composer: `dompdf/dompdf` 3.1.6, `php-font-lib`, `php-svg-lib`, `masterminds/html5` |

**Módulos presentes desde el inicio**

- Plantillas: portada editorial, categorías (`category.php` y `page-categoria.php`), entradas, páginas legales, contacto, radio y 404.
- `inc/seo/`: SSIVO-SEO (metadatos, tabla indexable, automatizaciones, metabox, panel de métricas con Google Site Kit).
- `src/seo/`: panel React de legibilidad y vista previa del resultado en Google.
- `inc/ad-manager.php`: gestor de publicidad (cabecera, in-feed y patrocinio por categoría).
- `inc/clasificados.php`: clasificados con tipos y URL propias.
- `inc/portada-dia.php`: programación de la portada impresa.
- `inc/reportes/`: reportes editoriales en PDF.
- `inc/admin-whitelabel.php` e `inc/security.php`: panel con marca propia, roles y acceso `/turpial`.
- `functions.php`: roles editoriales, firma obligatoria, widgets del escritorio, contacto, carteles, instalación automática de páginas y menús.

---

### 25 de septiembre de 2026

#### 2 · `d7a9210` — Auditoría: entradas ocultas, widgets, SEO/Site Kit, acentos y clasificados 🔧 ✨

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a` → fusionada en `main` por el PR #2 |
| **Autor** | Claude (Claude Code) · noreply@anthropic.com |
| **Fecha** | 25/09/2026 07:32:19 VET (subido a GitHub a las 07:52) |
| **Cambios** | 22 archivos · +1.889 / −1.016 líneas |
| **Versión** | 2.1.0 (Changelog del README) |

Auditoría del tema que corrige seis incidencias reportadas. Cada corrección se verificó en un WordPress 7.1.2 de pruebas: en el navegador, el tema anterior pasaba 4 de 13 comprobaciones y el corregido las 13.

##### a) Entradas publicadas que desaparecían

| Archivo | Cambio |
|---|---|
| `functions.php` | `pro_enforce_firma_on_publish()` solo actúa al **pasar** a publicado o programado. Antes cualquier edición de una entrada publicada sin firma la devolvía a borrador. |
| `functions.php` | Nuevas `pro_register_firma_meta()` y `pro_rest_firma()`, más el filtro `rest_pre_insert_post`: la firma viaja por REST en la misma petición de publicación de Gutenberg. |
| `functions.php` | El script del editor sincroniza la firma con `core/editor`, y `pro_save_firma_autor_meta()` añade `wp_unslash()`. |
| `functions.php` | Nuevas constantes `PRO_CATEGORY_PAGE_PER_PAGE` (12) y `PRO_CATEGORY_ARCHIVE_PER_PAGE` (20). |
| `functions.php` | Nuevas `pro_category_includes_children()`, `pro_get_listing_query_args()`, `pro_get_main_query_loadmore_state()`, `pro_listing_data_attributes()` y `pro_check_public_ajax_nonce()`. |
| `functions.php` | `pro_load_more_posts()` pagina por desplazamiento (*offset*) con orden fecha + ID y responde JSON `{ html, count, has_more }`. `pro_loadmore_params` queda solo con datos globales. |
| `template-parts/content/card.php` | **Nuevo**: tarjeta de noticia compartida entre la primera carga y AJAX. |
| `page-categoria.php`, `category.php`, `index.php` | Usan la consulta compartida, la tarjeta y los atributos `data-*` del listado. |

##### b) Widgets que a veces no cargaban (navegación Swup)

| Archivo | Cambio |
|---|---|
| `assets/js/main.js` | Reescrito: arranque único (`boot`), reinicio por página (`initPage`) y limpieza (`teardownPage`). Delegación de eventos para "Cargar más", carteles, contacto, búsqueda y portada. |
| `assets/js/main.js` | Integración Swup con `ignoreVisit` (admin, login, archivos), cierre del menú móvil, sincronización de clases del `<body>`, re-ejecución solo de los scripts de `#swup`, rescate de imágenes con carga diferida y radio con un único `<audio>`. |
| `assets/js/main.js` | Búsqueda con `/^[\p{L}\s.,\-¿?¡!]+$/u` (acepta Á, Í, Ü) y consentimiento del formulario de contacto validado. |
| `footer.php` | Se retira `@swup/scripts-plugin`, que re-ejecutaba todos los scripts en cada navegación. |
| `template-parts/ads/category-sponsor.php` | Sin script inline (lo rota `main.js`) y `mb_strtoupper()` en lugar de `strtoupper()`. |

##### c) Imagen destacada en widgets y entradas programadas

| Archivo | Cambio |
|---|---|
| `functions.php` | Nuevas `pro_find_content_image_id()`, `pro_get_post_image_id()` y `pro_the_post_image()`: usan la destacada válida o, si falta, la primera imagen del contenido. |
| `inc/seo/class-automations.php` | La imagen destacada automática reconoce imágenes redimensionadas y funciona también cuando el cron publica una programada. La meta descripción se extrae de forma segura para UTF-8. |
| `front-page.php`, `template-parts/home/hero.php`, `premium.php`, `secondary.php`, `index.php` | Usan los nuevos helpers de imagen. |

##### d) Panel SEO vinculado a Google Site Kit

| Archivo | Cambio |
|---|---|
| `inc/seo/class-site-kit-bridge.php` | **Nuevo** `SiteKitBridge`: Site Kit completo solo para administradores, lectura con el token del propietario de cada módulo (`with_shared_read()`) y redirección al panel SEO. |
| `inc/seo/class-admin-page.php` | El proxy ya no usa `wp_set_current_user()`, que no cambia el token de Site Kit. Se retira la opción `ssivo_seo_sk_owner_id` y se añade un estado de conexión, la fecha real de actualización y la migración de capacidades 1.1.0. |
| `inc/seo/init.php` | Carga y registro del puente. |
| `inc/admin-whitelabel.php` | Se retira la regla que dejaba Site Kit a los editores. |

##### e) Acentos y Ñ

| Archivo | Cambio |
|---|---|
| `functions.php` | Se elimina `pro_fix_corrupted_terms()`, que rompía la Ñ, las mayúsculas acentuadas y las comillas. |
| `functions.php` | Nueva `pro_repair_mojibake()`: reparación Windows-1252 → UTF-8 con lista blanca de caracteres del español. |
| `functions.php` | Protección al guardar (`wp_insert_post_data`, `pre_insert_term`, `wp_update_term_data`). |
| `functions.php` | Migración por lotes `pro_repair_stored_mojibake()` sobre términos, entradas, menús, firmas y datos SEO, sin tocar slugs. |

##### f) Clasificados

| Archivo | Cambio |
|---|---|
| `inc/clasificados.php` | Soporte de imagen destacada, bloques `core/image` y `core/gallery`, y alta automática de "Clasificados" en los menús principal (antes de "Más") y móvil. Estilos cargados en todo el sitio. |
| `archive-clasificado.php`, `single-clasificado.php` | Imagen del aviso en la tarjeta del listado y en la página del aviso. |
| `assets/css/clasificados.css` | Estilos de las imágenes. |

##### g) Documentación

| Archivo | Cambio |
|---|---|
| `README.md` | Entrada de changelog **v2.1.0 — Auditoría (septiembre 2026)**. |

---

#### 3 · `74fe87e` — Fix editorial listings, SEO visibility, widgets and classifieds 🔧

| | |
|---|---|
| **Rama** | `fix/auditoria-editorial-2026-09-25` (PR #1, **abierto**, sin fusionar) |
| **Autor** | Merchan.dev · merchan.dev@hotmail.com |
| **Fecha** | 25/09/2026 07:34:07 VET |
| **Cambios** | 1 archivo · +501 líneas |

Añade `inc/editorial-audit-fixes.php`, que agrupa sus correcciones en un archivo aparte para no seguir ampliando `functions.php`:

1. **Invalidación editorial**: limpia cachés y *transients* en `transition_post_status` cuando una entrada entra o sale de publicado, también si la publica el cron.
2. **Paginación AJAX**: sustituye `pro_load_more_posts` por un endpoint de 20 entradas por página, orden fecha + ID e inclusión de subcategorías. Responde HTML y pagina por número de página.
3. **SEO y Site Kit**: concede `view_ssivo_seo` a todos los roles, oculta los menús de Site Kit a quien no es administrador y lo redirige al panel SEO.
4. **Widget "Últimas Publicaciones"**: lo vuelve a dibujar con miniatura (también para las programadas) y orden determinista por modificación + ID.
5. **Clasificados**: soporte de imagen destacada, bloques de imagen, galería y medios y texto, y enlace "Clasificados" añadido a los menús `primary` y `mobile` mediante el filtro `wp_nav_menu_items`.
6. **Mojibake**: reparación al mostrar el texto con un mapa fijo de secuencias (`strtr`) sobre título, extracto, contenido, widgets, menús y términos.
7. **Estilos**: CSS en línea para las imágenes de clasificados.

#### 4 · `195c0ea` — Load editorial audit fixes 🔧

| | |
|---|---|
| **Rama** | `fix/auditoria-editorial-2026-09-25` |
| **Fecha** | 25/09/2026 07:34:23 VET |
| **Cambios** | `inc/seo/init.php` · +1 línea |

Carga `inc/editorial-audit-fixes.php` desde el módulo SEO.

#### 5 · `986b9cd` — Unify category pagination with AJAX 🔧

| | |
|---|---|
| **Rama** | `fix/auditoria-editorial-2026-09-25` |
| **Fecha** | 25/09/2026 07:34:51 VET |
| **Cambios** | `page-categoria.php` · +9 / −2 líneas |

Lee la página actual de `paged` o `page` y sube el tamaño de 12 a 20 entradas para igualarlo con su endpoint AJAX.

#### 6 · `31e26db` — Render classified featured images ✨

| | |
|---|---|
| **Rama** | `fix/auditoria-editorial-2026-09-25` |
| **Fecha** | 25/09/2026 07:35:20 VET |
| **Cambios** | `archive-clasificado.php` · +19 / −1 líneas |

Muestra la imagen destacada (`medium_large`) en las tarjetas del listado de clasificados.

#### 7 · `58a6204` — Show classified featured image on single view ✨

| | |
|---|---|
| **Rama** | `fix/auditoria-editorial-2026-09-25` |
| **Fecha** | 25/09/2026 07:35:32 VET |
| **Cambios** | `single-clasificado.php` · +15 / −1 líneas |

Muestra la imagen destacada (`large`) en la página de cada aviso.

---

#### 8 · `7754bef` — Merge pull request #2 🔀

| | |
|---|---|
| **Rama** | `main` |
| **Autor** | Merchan.dev |
| **Fecha** | 25/09/2026 07:54:03 VET |
| **Padres** | `eaf6786` (main) · `d7a9210` (claude/laughing-planck-4m793a) |

Fusiona en `main` el [PR #2](https://github.com/merchandev/espressivo-editorial/pull/2). Desde este punto `main` contiene la auditoría v2.1.0 (22 archivos, +1.889 / −1.016 líneas).

---

#### 9 · `a97109f` — Documentación: ABOUT.md e historial de cambios 📝

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a` (actualizada a `main` en `7754bef`) |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 08:01:35 VET |
| **Cambios** | 3 archivos · +440 líneas |

| Archivo | Cambio |
|---|---|
| `ABOUT.md` | **Nuevo**: descripción del proyecto, módulos, roles, tecnología, estructura y ficha para el panel "About" de GitHub. |
| `HISTORIAL_DE_CAMBIOS.md` | **Nuevo**: este documento. |
| `README.md` | Enlaces a ambos documentos. |

---

#### 10 · `afa897d` — Versión 2.1.0, README renovado y menú de Clasificados en instalaciones nuevas ✨ 📝

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a` → `main` |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 08:07:19 VET |
| **Cambios** | 4 archivos · +251 / −76 líneas |

| Archivo | Cambio |
|---|---|
| `style.css` | Versión del tema **2.0.0 → 2.1.0**. Nuevas cabeceras `Requires at least: 5.8`, `Tested up to: 7.1` y `Requires PHP: 8.1`: WordPress comprueba los requisitos antes de activar o actualizar el tema. |
| `inc/clasificados.php` | `espressivo_add_clasificados_to_menus()` pasa de `init` a `admin_init` con prioridad 100. En una instalación nueva, la instalación automática (`pro_nuclear_install_pages`) reasignaba el menú principal después de añadir el enlace y "Clasificados" quedaba solo en el menú móvil. Ahora queda en los dos, sin duplicarse. |
| `README.md` | Reescrito: características actualizadas a la 2.1.0, requisitos, instalación, configuración, actualización de instalaciones existentes, roles, seguridad, rendimiento, desarrollo, solución de problemas, changelog y créditos. Corrige dos datos: los accesos a `wp-login.php` se redirigen a la portada (no devuelven 404) y la publicidad es de cabecera, in-feed y patrocinio (no de barra lateral). |
| `HISTORIAL_DE_CAMBIOS.md` | Registro de los commits 9 y 10. La observación sobre la versión del tema queda resuelta. |

---

#### 11 · `8678283` — README: acceso rápido a la documentación; todo integrado en `main` 📝

| | |
|---|---|
| **Rama** | `main` (también en `claude/laughing-planck-4m793a`) |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 08:13:02 VET |
| **Cambios** | 2 archivos · +42 / −13 líneas |

| Archivo | Cambio |
|---|---|
| `README.md` | Nueva sección **📚 Documentación rápida** bajo el título: enlaces al historial de cambios, al ABOUT y al changelog, con accesos directos a la línea de tiempo, el mapa de ramas, los pendientes, los módulos, los roles, la estructura y la versión 2.1.0. |
| `HISTORIAL_DE_CAMBIOS.md` | Hash del commit 10, registro del commit 11, mapa de ramas y estado actualizados. |

Con este commit, `main` contiene todo el trabajo: auditoría v2.1.0, documentación, versión del tema y README.

---

#### 12 · `ecb6673` — v2.1.1: cifras del panel SEO alineadas con Site Kit 🔧

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a` (integrado en `main` con el commit 13) |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 08:29 VET |
| **Cambios** | 5 archivos · +206 / −50 líneas |

**Motivo:** en producción el panel SEO mostraba 16.517 usuarios mientras que Site Kit mostraba 17K para los "últimos 28 días", y "Impresiones" y "Palabras clave" aparecían vacías sin explicar por qué.

**Causas encontradas** (verificadas en el código fuente de Site Kit):
- Site Kit calcula "Todos los visitantes" con la métrica `totalUsers`; el panel pedía `activeUsers`, que siempre da menos.
- Site Kit usa N días que **terminan hoy** en la fecha local (28 días: 29/08–25/09); el panel pedía N+1 días en hora UTC.
- Cuando Search Console fallaba, el panel ocultaba el error de Google y guardaba el resultado parcial 4 horas en caché.

| Archivo | Cambio |
|---|---|
| `inc/seo/class-admin-page.php` | Nuevo `date_range()` con el periodo de Site Kit en la zona horaria del sitio y periodos de 7, 14, 28 y 90 días (se retira "24 horas"). Métrica `totalUsers`. Caché nueva `ssivo_seo_google_v2_*` para descartar las cifras anteriores. Últimos datos válidos por servicio (`ssivo_seo_google_last_good_*`), error real de Google en cada bloque y caché de 15 minutos cuando un servicio falla. La vista muestra el periodo exacto y renombra la tarjeta a "Usuarios totales". |
| `style.css` | Versión **2.1.1**. |
| `README.md`, `ABOUT.md` | Versión 2.1.1, entrada de changelog y nuevas filas en "Solución de problemas" (errores de Google y comparación con Site Kit). |
| `HISTORIAL_DE_CAMBIOS.md` | Hash del commit 11 y registro de este commit. |

---

#### 13 · `f3df61f` — v2.1.2: revisión completa, listados y migración de acentos 🔧 📝

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a`, llevado a `main` por avance rápido |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 08:43 VET |
| **Cambios** | 6 archivos · +97 / −27 líneas |

**Motivo:** revisión completa de todos los cambios desde `eaf6786` (PHP, JavaScript, CSS y plantillas), con las pruebas repetidas en una instalación local de WordPress 7.1.2.

**Fallos encontrados y corregidos** (reproducidos antes de corregirlos):
- **Búsqueda con "Cargar más":** la primera página de resultados abarca todos los tipos buscables (entradas, páginas, carteles, clasificados) y ordena por relevancia, pero las tandas AJAX pedían solo entradas ordenadas por fecha. Resultado: se perdían resultados y el orden cambiaba desde la segunda tanda.
- **Sesiones de redacción:** en portada de entradas, etiquetas, autores, fechas y búsqueda, la primera página incluía entradas privadas y "Cargar más" no; el desplazamiento no coincidía y se saltaban entre 1 y 4 noticias por listado. Además esos listados ordenaban solo por fecha, sin desempate por ID.
- **Migración de acentos:** en una base grande, la primera carga del escritorio podía recorrer toda la tabla de entradas con `HEX()` en una sola petición.

| Archivo | Cambio |
|---|---|
| `functions.php` | `pro_get_listing_query_args()`: con búsqueda usa `post_type => any` y el orden por relevancia de WordPress. Nuevo `pre_get_posts` para portada de entradas, etiquetas, autores, fechas y búsqueda: solo contenido publicado y orden fecha + ID (la búsqueda conserva la relevancia). `pro_repair_stored_mojibake()`: revisa tramos de 2.000 ID por carga con un cursor, hasta el ID máximo. |
| `page-categoria.php` | El extracto del destacado se escapa con `esc_html()`, como en las tarjetas. |
| `style.css` | Versión **2.1.2**. |
| `README.md`, `ABOUT.md` | Versión 2.1.2, entrada de changelog, nota de actualización y fila de "Solución de problemas" sobre entradas privadas. |
| `HISTORIAL_DE_CAMBIOS.md` | Hash del commit 12, registro de este commit, mapa y estado de ramas. |

**Verificación:** listados de etiqueta, búsqueda, portada de entradas y autor (visitante y administrador) recorridos hasta el final con "Cargar más": 0 noticias perdidas y 0 repetidas (con la versión anterior faltaban de 1 a 4). Migración por tramos con un ID alto: termina en 3 cargas y repara el texto. Siguen en verde las pruebas anteriores: firma (6 casos), paginación de categorías (40/40), imágenes, acentos, panel SEO y Site Kit por rol, cron y la suite de navegador (13/13).

---

#### 14 · `914109c` — v2.1.3: panel SEO con los mismos datos que Site Kit 🔧 ✨

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a`, llevado a `main` por avance rápido |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 09:26 VET |
| **Cambios** | 6 archivos · +419 / −131 líneas |

**Motivo:** en producción, el panel SEO mostraba Google Analytics bien, pero "Impresiones" y "Palabras clave" salían con *"User does not have sufficient permission for site 'https://diarioeloriental.com/'"*, mientras que el Site Kit del administrador sí mostraba Search Console (396K impresiones, 6,9K clics). Además, países y dispositivos no coincidían con los gráficos de Site Kit (Venezuela 72 % frente a 40,2 %). El objetivo del panel es mostrar al equipo exactamente los datos de Site Kit sin darle acceso a Site Kit.

**Causas encontradas** (en el código fuente de Site Kit):
- Para los datos compartidos, Site Kit usa el token del **propietario** del módulo (el administrador que lo conectó, `ownerID`). El Site Kit de cada administrador usa en cambio **su propia** cuenta de Google. La cuenta que conectó Search Console no tiene permiso en la propiedad; la del administrador que lo consulta, sí.
- Los gráficos "Ubicaciones" y "Dispositivos" de Site Kit usan `totalUsers` en porcentaje sobre el total (4 primeros + "Otros"); el panel usaba vistas de página.

| Archivo | Cambio |
|---|---|
| `inc/seo/class-site-kit-bridge.php` | Cuenta de lectura por servicio (`ssivo_seo_sitekit_readers`): por defecto la del propietario; `with_shared_read()` la aplica en memoria durante la lectura, sin modificar los ajustes de Site Kit. Nuevos `with_own_credentials()`, `can_read_with_own_account()`, `get_reader()`, `set_reader()` y `get_module_owner_id()`. Se ignora una cuenta de lectura que deja de ser administrador o cierra su sesión de Site Kit. |
| `inc/seo/class-admin-page.php` | Si un servicio falla y consulta un administrador conectado a Site Kit, se repite con su cuenta; si funciona, se guarda como cuenta de lectura para todo el equipo y el cron, y se vacía la caché de los demás periodos. Ese administrador no recibe la caché con errores. Países y dispositivos con `totalUsers` en porcentaje (4 + "Otros"); tarjeta "Clics totales"; tarjetas en rejilla 2 × 2. *Ajustes globales* muestra la cuenta que lee cada servicio. Caché `v3` y migración que borra la caché y los últimos datos válidos anteriores. |
| `style.css` | Versión **2.1.3**. |
| `README.md`, `ABOUT.md` | Versión 2.1.3, changelog, configuración de Site Kit y "Solución de problemas" del error de permisos. |
| `HISTORIAL_DE_CAMBIOS.md` | Hash del commit 13 y registro de este commit. |

**Verificación** (Site Kit simulado con su lógica real de propietario y token; Search Console conectado por un administrador sin permiso):
- Antes: Search Console fallaba para la editora, la directora, el administrador y el cron.
- Después: cuando el administrador abre el panel, Search Console funciona para todos. El `ownerID` guardado en Site Kit no cambia.
- Casos límite: caché parcial, cuenta de lectura sin sesión de Site Kit (se vuelve al propietario) y ninguna cuenta con permiso (no cambia nada).
- En el navegador, la directora ve los datos y no ve el menú de Site Kit. Si abre su dirección, se la redirige al panel SEO.

---

#### 15 · v2.1.4: banner de cabecera un 15 % más grande ✨

| | |
|---|---|
| **Rama** | `claude/laughing-planck-4m793a`, llevado a `main` por avance rápido |
| **Autor** | Claude (Claude Code) |
| **Fecha** | 25/09/2026 |

**Motivo:** que la imagen promocional de la cabecera (junto al logo) se vea más grande sin cambiar sus proporciones.

| Archivo | Cambio |
|---|---|
| `assets/css/main.css` | `.header-ad`: `max-width` de 728 a 837 px y `height` de 90 a 104 px (+15 %, misma proporción). La imagen usa `object-fit: contain`, así que crece sin deformarse ni recortarse. La regla de móvil (≤ 767 px, ancho completo) no cambia. |
| `style.css`, `README.md`, `ABOUT.md` | Versión **2.1.4** y entrada de changelog. |
| `HISTORIAL_DE_CAMBIOS.md` | Hash del commit 14 y registro de este commit. |

**Verificación** (navegador, con un banner de prueba de 850 × 110 px, la proporción del real): a 1440 y 1200 px de ancho la imagen pasa de 695 × 90 a 804 × 104 px; a 390 px (móvil) sigue en 359 × 46 px.

---

## Estado de ramas y pull requests

| Rama | Último commit | Estado |
|---|---|---|
| `main` | commit 15 | Rama principal. Contiene todo el trabajo: auditoría v2.1.0, panel SEO v2.1.1 y v2.1.3, revisión v2.1.2, banner v2.1.4 y documentación. |
| `claude/laughing-planck-4m793a` | commit 15 | Igual que `main`. |
| `fix/auditoria-editorial-2026-09-25` | `58a6204` | PR #1 **abierto**, sin fusionar. |

| PR | Título | Estado |
|---|---|---|
| [#1](https://github.com/merchandev/espressivo-editorial/pull/1) | Corrige listados editoriales, SEO/Site Kit, widgets y Clasificados | Abierto |
| [#2](https://github.com/merchandev/espressivo-editorial/pull/2) | Auditoría: entradas ocultas, widgets, SEO/Site Kit, acentos y clasificados | Fusionado el 25/09/2026 |

---

## Observaciones pendientes

1. **El PR #1 no se puede fusionar tal cual sobre `main`.** Hay conflictos en `archive-clasificado.php`, `inc/seo/init.php`, `page-categoria.php` y `single-clasificado.php`. Además, su endpoint de "Cargar más" (HTML, paginado por número de página) sustituiría al actual (JSON, por desplazamiento) y rompería el scroll infinito. Se recomienda cerrarlo o rehacerlo sobre `main`.
2. **Aporte del PR #1 que `main` no tiene:** miniaturas en el widget del escritorio "Últimas Publicaciones", incluidas las entradas programadas. Si se desea, puede incorporarse a `main` en un cambio aparte.
3. ~~**Versión del tema:** la cabecera de `style.css` seguía en `2.0.0`.~~ ✅ Resuelto en el commit 10 (versión `2.1.0`).
4. **Archivo sin uso:** `main_head.css` es una copia antigua de `main.css` en UTF-16 que ningún archivo carga.
5. **Licencia:** `style.css` (GPL v2 o posterior), `composer.json` (*proprietary*) y los avisos legales de los archivos no coinciden.
6. **Buscador sin números:** la validación del formulario de búsqueda rechaza cifras por diseño ("evita números/símbolos"), así que búsquedas como *"Ley 2026"* no se envían. Conviene decidir si se permiten.
7. **Swup desde unpkg sin versión fija:** `footer.php` carga `https://unpkg.com/swup@4`, que toma la última 4.x publicada. Fijar una versión exacta o servir el archivo desde el tema evita cambios inesperados.

---

## Cómo añadir nuevas entradas

Cada commit nuevo se añade **al final de la línea de tiempo** y con su propia sección de detalle. Plantilla:

```markdown
#### N · `abc1234` — Título del commit 🔧

| | |
|---|---|
| **Rama** | `nombre-de-la-rama` |
| **Autor** | Nombre · correo |
| **Fecha** | DD/MM/AAAA HH:MM:SS VET |
| **Cambios** | X archivos · +A / −B líneas |

| Archivo | Cambio |
|---|---|
| `ruta/archivo.php` | Qué se cambió y por qué. |
```

Comandos útiles:

```bash
git log --all --date=iso --format='%h %ad %an %s'   # todos los commits con fecha
git show --stat <commit>                            # archivos y líneas de un commit
```
