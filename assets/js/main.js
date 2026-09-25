/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 *
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual,
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
/**
 * Script principal de Pro
 * Maneja el modo oscuro, menú móvil, ticker, sliders, polling de noticias y scroll infinito.
 *
 * Navegación con Swup: solo se reemplaza el contenedor #swup, así que este archivo
 * se ejecuta una única vez. Todo lo que vive dentro del contenido se engancha por
 * delegación de eventos o se (re)inicializa en initPage() tras cada navegación.
 * Antes las inicializaciones dependían de DOMContentLoaded, que no vuelve a
 * dispararse al navegar: el scroll infinito, el formulario de contacto, los
 * visores y los sliders dejaban de funcionar "a veces" (según cómo se llegara
 * a la página).
 */

// =====================================================================
//  Diseñado y desarrollado por Merchan.Dev & Espressivo Venezuela, C.A
// =====================================================================
console.log(
    '%c Merchan.Dev %c & Espressivo Venezuela, C.A ',
    'background:#111827; color:#f59e0b; font-weight:bold; font-size:13px; padding:4px 8px; border-radius:4px 0 0 4px;',
    'background:#f59e0b; color:#111827; font-weight:bold; font-size:13px; padding:4px 8px; border-radius:0 4px 4px 0;'
);
console.log('%cDiseño y desarrollo web · https://merchan.dev', 'color:#6b7280; font-size:11px;');

(function () {
    'use strict';

    const params = window.pro_loadmore_params || {};

    // Tareas a deshacer al abandonar la página actual (intervalos, observers...)
    const pageCleanups = [];

    function onPageLeave(fn) {
        pageCleanups.push(fn);
    }

    function teardownPage() {
        while (pageCleanups.length) {
            try {
                pageCleanups.pop()();
            } catch (e) {
                console.error(e);
            }
        }
    }

    function swupContainer() {
        return document.getElementById('swup') || document.body;
    }

    // ==========================================
    // 1. MODO OSCURO (Dark Mode)
    // ==========================================
    function initDarkMode() {
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }

        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
            });
        }
    }

    // ==========================================
    // 2. MENÚ MÓVIL A PANTALLA COMPLETA
    // ==========================================
    const MENU_ICON = '<span class="material-symbols-outlined" aria-hidden="true" style="vertical-align: middle;">menu</span>';
    const CLOSE_ICON = '<span class="material-symbols-outlined" aria-hidden="true" style="vertical-align: middle;">close</span>';

    function initMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.main-navigation');
        if (!menuToggle || !navMenu) return;

        menuToggle.addEventListener('click', function (e) {
            e.preventDefault();
            const open = navMenu.classList.toggle('toggled');
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            // Bloquear scroll de fondo si está abierto
            menuToggle.innerHTML = open ? CLOSE_ICON : MENU_ICON;
            document.body.style.overflow = open ? 'hidden' : '';
        });
    }

    // Al navegar con Swup el menú queda fuera del contenido reemplazado: hay que cerrarlo
    function closeMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.main-navigation');
        if (!navMenu || !navMenu.classList.contains('toggled')) return;

        navMenu.classList.remove('toggled');
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.innerHTML = MENU_ICON;
        }
        document.body.style.overflow = '';
    }

    // ==========================================
    // 3. TICKER DE ÚLTIMO MINUTO (cabecera)
    // ==========================================
    function initTicker() {
        const tickerSlider = document.getElementById('newsTickerSlider');
        if (!tickerSlider) return;

        const slides = Array.from(tickerSlider.querySelectorAll('.ticker-slide'));
        if (slides.length === 0) return;

        // Asegurarse de que solo el primero tiene la clase active al arrancar
        slides.forEach((s, i) => {
            s.classList.remove('active', 'prev');
            if (i === 0) s.classList.add('active');
        });

        if (slides.length < 2) return;

        let current = 0;
        let isAnimating = false;

        setInterval(() => {
            if (isAnimating) return;
            isAnimating = true;

            const prevIndex = current;
            current = (current + 1) % slides.length;

            // Salida: el slide actual pasa a .prev
            slides[prevIndex].classList.remove('active');
            slides[prevIndex].classList.add('prev');

            // Entrada: el siguiente aparece con .active
            slides[current].classList.add('active');

            // Limpiar .prev cuando termina la transición CSS (450ms)
            setTimeout(() => {
                slides[prevIndex].classList.remove('prev');
                isAnimating = false;
            }, 500);
        }, 5000);
    }

    // ==========================================
    // 4. LISTADOS: "CARGAR MÁS" Y SCROLL INFINITO
    // El estado de cada listado (categoría, desplazamiento, tamaño de tanda)
    // viaja en atributos data-* impresos por PHP junto al propio listado.
    // ==========================================
    const LISTING_FILTERS = {
        catId: 'category_id',
        tagId: 'tag_id',
        authorId: 'author_id',
        search: 's',
        year: 'year',
        monthnum: 'monthnum',
        day: 'day'
    };

    function appendUniquePosts(grid, html) {
        const template = document.createElement('template');
        template.innerHTML = html;
        // Si entre dos cargas se publicó una noticia, la tanda puede repetir la última mostrada
        template.content.querySelectorAll('article[id]').forEach((article) => {
            if (document.getElementById(article.id)) article.remove();
        });
        grid.appendChild(template.content);
        rescueLazyImages(grid);
    }

    /**
     * Pide la siguiente tanda del listado descrito por los data-* de `el`.
     * Resuelve true si quedan más noticias por cargar.
     */
    function fetchMorePosts(el) {
        const data = el.dataset;
        const grid = data.target ? swupContainer().querySelector(data.target) : null;
        if (!grid || !params.ajax_url) {
            return Promise.reject(new Error('Listado no disponible'));
        }

        const formData = new FormData();
        formData.append('action', 'pro_load_more_posts');
        formData.append('nonce', params.nonce || '');
        formData.append('offset', data.offset || '0');
        formData.append('per_page', data.perPage || '12');
        Object.keys(LISTING_FILTERS).forEach((key) => {
            if (data[key]) formData.append(LISTING_FILTERS[key], data[key]);
        });

        return fetch(params.ajax_url, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then((response) => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then((json) => {
                if (!json || !json.success || !json.data) throw new Error('Respuesta inválida');
                if (json.data.html) appendUniquePosts(grid, json.data.html);
                el.dataset.offset = String(parseInt(data.offset || '0', 10) + (json.data.count || 0));
                return Boolean(json.data.has_more) && json.data.count > 0;
            });
    }

    // Botón "Cargar más" (archivo de categoría, portada tipo blog, búsqueda)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#load-more-btn');
        if (!btn || btn.disabled) return;
        e.preventDefault();

        btn.disabled = true;
        btn.textContent = 'Cargando...';

        fetchMorePosts(btn)
            .then((hasMore) => {
                if (hasMore) {
                    btn.disabled = false;
                    btn.textContent = 'Cargar más noticias';
                } else {
                    (btn.closest('.load-more-container') || btn).remove();
                }
            })
            .catch((error) => {
                console.error('Error cargando posts:', error);
                btn.disabled = false;
                btn.textContent = 'Reintentar';
            });
    });

    // Scroll infinito (páginas de categoría)
    function initInfiniteScroll(root) {
        const trigger = root.querySelector('.infinite-scroll-trigger');
        if (!trigger || !('IntersectionObserver' in window)) return;

        let isFetching = false;
        let retryTimer = null;

        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting) || isFetching) return;
            isFetching = true;

            fetchMorePosts(trigger)
                .then((hasMore) => {
                    if (!hasMore) {
                        observer.disconnect();
                        trigger.remove();
                        return;
                    }
                    isFetching = false;
                    // Volver a observar: si el disparador sigue a la vista se pide otra tanda
                    observer.unobserve(trigger);
                    observer.observe(trigger);
                })
                .catch((error) => {
                    console.error('Error cargando noticias:', error);
                    retryTimer = setTimeout(() => {
                        isFetching = false;
                        observer.unobserve(trigger);
                        observer.observe(trigger);
                    }, 4000);
                });
        }, { rootMargin: '200px' });

        observer.observe(trigger);
        onPageLeave(() => {
            observer.disconnect();
            clearTimeout(retryTimer);
        });
    }

    // ==========================================
    // 5. BARRA DE PROGRESO DE LECTURA (solo en artículos)
    // ==========================================
    function initReadingProgress() {
        window.addEventListener('scroll', () => {
            const progressBar = document.getElementById('reading-progress-bar');
            if (!progressBar) return;

            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            progressBar.style.width = (scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0) + '%';
        }, { passive: true });
    }

    // La barra se imprime en la cabecera (fuera de Swup) solo si la primera carga es un artículo
    function syncReadingProgress() {
        const isArticle = Boolean(swupContainer().querySelector('.single-post-article'));
        let container = document.getElementById('reading-progress-container');

        if (!container && isArticle) {
            const header = document.getElementById('masthead');
            if (!header) return;
            container = document.createElement('div');
            container.id = 'reading-progress-container';
            container.className = 'reading-progress-container';
            container.innerHTML = '<div id="reading-progress-bar" class="reading-progress-bar"></div>';
            header.appendChild(container);
        }

        if (container) {
            container.style.display = isArticle ? '' : 'none';
            const bar = container.querySelector('#reading-progress-bar');
            if (bar) bar.style.width = '0%';
        }
    }

    // ==========================================
    // 6. ACTUALIZACIÓN AUTOMÁTICA DE NOTICIAS (AJAX Polling)
    // ==========================================
    function mostrarNotificacionNuevasNoticias() {
        if (document.getElementById('new-posts-toast')) return;

        const toast = document.createElement('div');
        toast.id = 'new-posts-toast';
        toast.className = 'new-posts-toast';
        toast.title = 'Hay nuevas noticias. Haz clic para actualizar.';
        toast.innerHTML = '';

        toast.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
    }

    function initNewPostsPolling() {
        if (!params.ajax_url || !params.latest_date) return;

        setInterval(function () {
            const formData = new FormData();
            formData.append('action', 'pro_check_new_posts');
            formData.append('latest_date', params.latest_date);
            formData.append('nonce', params.nonce || '');

            fetch(params.ajax_url, { method: 'POST', body: formData, credentials: 'same-origin' })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success && data.data.has_new_posts) {
                        mostrarNotificacionNuevasNoticias();
                    }
                })
                .catch((error) => console.error('Error comprobando nuevas noticias:', error));
        }, 60000);
    }

    // ==========================================
    // 7. SLIDERS DE PUBLICIDAD (In-Feed, cabecera y patrocinadores)
    // ==========================================
    function initAdSliders(root, trackForCleanup) {
        root.querySelectorAll('.in-feed-ad-slider, .header-ad').forEach((slider) => {
            const slides = slider.querySelectorAll('.ad-slide');
            if (slides.length <= 1) return;

            let currentIndex = 0;
            const timer = setInterval(() => {
                slides[currentIndex].classList.remove('active');
                currentIndex = (currentIndex + 1) % slides.length;
                slides[currentIndex].classList.add('active');
            }, 5000);
            if (trackForCleanup) onPageLeave(() => clearInterval(timer));
        });

        // Banner patrocinador de categoría (desplazamiento horizontal)
        root.querySelectorAll('.pro-sponsor-slider').forEach((slider) => {
            const totalSlides = slider.querySelectorAll('.pro-sponsor-slide').length;
            if (totalSlides <= 1) return;

            let currentIndex = 0;
            const timer = setInterval(() => {
                currentIndex = (currentIndex + 1) % totalSlides;
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            }, 4000); // Rota cada 4 segundos
            if (trackForCleanup) onPageLeave(() => clearInterval(timer));
        });
    }

    // ==========================================
    // 8. IMÁGENES CON CARGA DIFERIDA DE PLUGINS
    // Los plugins de lazy-load (LiteSpeed, etc.) activan sus imágenes una sola vez
    // al cargar la página; en el contenido que llega por Swup o AJAX se quedaban
    // con el marcador vacío. La carga diferida nativa (loading="lazy") no se toca.
    // ==========================================
    function rescueLazyImages(root) {
        root.querySelectorAll('img[data-src], img[data-lazy-src], source[data-srcset], source[data-lazy-srcset]').forEach((el) => {
            const src = el.getAttribute('data-lazy-src') || el.getAttribute('data-src');
            const srcset = el.getAttribute('data-lazy-srcset') || el.getAttribute('data-srcset');
            const current = el.getAttribute('src') || '';

            if (src && (current === '' || current.indexOf('data:') === 0)) {
                el.setAttribute('src', src);
            }
            if (srcset) {
                el.setAttribute('srcset', srcset);
            }
        });
    }

    // ==========================================
    // 9. VISOR PDF DE CARTELES (LIGHTBOX)
    // ==========================================
    function closePdfModal() {
        const pdfModal = document.getElementById('pdf-lightbox-modal');
        if (!pdfModal || !pdfModal.classList.contains('active')) return;

        pdfModal.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(() => {
            const modalIframe = document.getElementById('pdf-iframe');
            if (modalIframe) modalIframe.src = ''; // Limpiar iframe después de la animación
        }, 300);
    }

    document.addEventListener('click', function (e) {
        const pdfModal = document.getElementById('pdf-lightbox-modal');

        // Cerrar con botón X o al hacer clic fuera del contenido
        if (pdfModal && (e.target.closest('.pdf-modal-close') || e.target === pdfModal)) {
            closePdfModal();
            return;
        }

        const cartel = e.target.closest('.card-cartel');
        if (!cartel || !pdfModal) return;

        e.preventDefault();
        const pdfUrl = cartel.getAttribute('data-pdf-url');
        const titleEl = cartel.querySelector('.entry-title');

        if (pdfUrl) {
            document.getElementById('pdf-modal-title').innerText = titleEl ? titleEl.innerText : 'Visor de Documento';
            document.getElementById('pdf-iframe').src = pdfUrl;
            pdfModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevenir scroll de fondo
        } else {
            alert('Este cartel no tiene un documento PDF adjunto.');
        }
    });

    // ==========================================
    // 10. FORMULARIO DE CONTACTO (AJAX)
    // ==========================================
    document.addEventListener('submit', function (e) {
        const contactForm = e.target;
        if (!contactForm || contactForm.id !== 'pro-contact-form') return;
        e.preventDefault();

        // Limpiar errores previos
        const errorLayer = document.getElementById('contact-form-error');
        errorLayer.style.display = 'none';
        errorLayer.innerText = '';

        const inputs = contactForm.querySelectorAll('input, select, textarea');
        let hasError = false;

        inputs.forEach((input) => {
            input.parentElement.classList.remove('has-error');
            const empty = input.type === 'checkbox' ? !input.checked : !input.value.trim();
            if (input.required && empty) {
                hasError = true;
                input.parentElement.classList.add('has-error');
            }
            if (input.type === 'email' && input.value.trim() !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    hasError = true;
                    input.parentElement.classList.add('has-error');
                }
            }
        });

        if (hasError) {
            errorLayer.innerText = 'Por favor, completa correctamente todos los campos marcados en rojo.';
            errorLayer.style.display = 'block';
            return;
        }

        // Preparar datos
        const formData = new FormData(contactForm);
        formData.append('action', 'pro_submit_contact_form');
        formData.append('nonce', window.pro_ajax ? window.pro_ajax.nonce : (params.nonce || ''));

        // UI Loading
        const btn = document.getElementById('contact-submit-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnSpinner = btn.querySelector('.btn-spinner');

        btn.disabled = true;
        btnText.style.display = 'none';
        btnSpinner.style.display = 'inline-block';

        const ajaxUrl = window.pro_ajax ? window.pro_ajax.ajax_url : (params.ajax_url || '/wp-admin/admin-ajax.php');

        fetch(ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then((response) => response.json())
            .then((data) => {
                btn.disabled = false;
                btnText.style.display = 'inline-block';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    // Ocultar formulario suavemente y mostrar éxito
                    contactForm.style.transition = 'opacity 0.3s ease';
                    contactForm.style.opacity = '0';
                    setTimeout(() => {
                        contactForm.style.display = 'none';
                        document.getElementById('contact-success-layer').style.display = 'block';
                    }, 300);
                } else {
                    errorLayer.innerText = (data.data && data.data.message) || 'Error desconocido al enviar.';
                    errorLayer.style.display = 'block';
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                btn.disabled = false;
                btnText.style.display = 'inline-block';
                btnSpinner.style.display = 'none';
                errorLayer.innerText = 'Ocurrió un error de conexión. Intenta nuevamente.';
                errorLayer.style.display = 'block';
            });
    });

    // Limpiar error al escribir
    document.addEventListener('input', function (e) {
        if (e.target.closest && e.target.closest('#pro-contact-form') && e.target.parentElement) {
            e.target.parentElement.classList.remove('has-error');
        }
    });

    // ==========================================
    // 11. VALIDACIÓN DE FORMULARIOS DE BÚSQUEDA
    // ==========================================
    // Letras de cualquier idioma (acentos, ñ, ü), espacios y puntuación básica.
    // La lista anterior de acentos estaba dañada por un problema de codificación
    // y rechazaba búsquedas con Á, Í o Ü.
    const SEARCH_ALLOWED = /^[\p{L}\s.,\-¿?¡!]+$/u;
    const SEARCH_URL = /(http|https|ftp|ftps):\/\/[a-zA-Z0-9\-.]+\.[a-zA-Z]{2,3}(\/\S*)?|www\.|[a-zA-Z0-9\-.]+\.(com|net|org|info)/i;

    function flagSearchInput(input, placeholder) {
        input.value = '';
        input.placeholder = placeholder;
        input.style.border = '2px solid #ef4444';
        setTimeout(() => {
            input.placeholder = 'Buscar noticias...';
            input.style.border = '';
        }, 3000);
    }

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.matches || !form.matches('form[role="search"], .search-form')) return;

        const input = form.querySelector('input[name="s"]');
        if (!input) return;

        const val = input.value.trim();

        // 1. Vacío o muy corto
        if (val.length < 2) {
            e.preventDefault();
            input.focus();
            input.style.border = '2px solid #ef4444';
            setTimeout(() => { input.style.border = ''; }, 2000);
            return;
        }

        // 2. Contiene enlaces o dominios
        if (SEARCH_URL.test(val)) {
            e.preventDefault();
            flagSearchInput(input, 'Enlaces no permitidos');
            return;
        }

        // 3. Solo letras (con acentos y ñ), espacios y puntuación básica de texto
        if (!SEARCH_ALLOWED.test(val)) {
            e.preventDefault();
            flagSearchInput(input, 'Texto inválido (evita números/símbolos)');
        }
    });

    // ==========================================
    // 12. LIGHTBOX INTERACTIVO DE PORTADAS (ZOOM & PAN)
    // El visor vive en el pie (fuera de Swup); las portadas, en el contenido.
    // ==========================================
    let closePortadaLightbox = function () {};

    function initPortadaLightbox() {
        const portadaModal = document.getElementById('portada-lightbox-modal');
        if (!portadaModal) return;

        const modalImage = document.getElementById('portada-lightbox-image');
        const modalTitle = document.getElementById('portada-modal-title');
        const downloadBtn = document.getElementById('portada-download-btn');

        // Botones de Zoom
        const zoomInBtn = document.getElementById('portada-zoom-in');
        const zoomOutBtn = document.getElementById('portada-zoom-out');
        const zoomResetBtn = document.getElementById('portada-zoom-reset');

        // Variables de Estado de Zoom y Arrastre (Pan)
        let zoomScale = 1;
        let isDragging = false;
        let startX = 0, startY = 0;
        let translateX = 0, translateY = 0;

        const zoomStep = 0.25;
        const maxZoom = 4;
        const minZoom = 0.5;

        // Función para aplicar transformaciones CSS de forma unificada
        const applyTransform = () => {
            modalImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(${zoomScale})`;
            zoomResetBtn.innerText = Math.round(zoomScale * 100) + '%';
        };

        // Abrir el Lightbox (delegado: las portadas llegan con cada página)
        document.addEventListener('click', function (e) {
            const portada = e.target.closest('.card-portada');
            if (!portada) return;

            // Si el clic proviene de un elemento de descarga, permitir comportamiento nativo
            if (e.target.closest('a[download]')) return;

            e.preventDefault();
            const fullUrl = portada.getAttribute('data-full-url');
            const titleElement = portada.querySelector('.entry-title') || portada.querySelector('.edition-title');
            if (!fullUrl) return;

            modalTitle.innerText = titleElement ? titleElement.innerText : 'Visor de Portada';
            modalImage.src = fullUrl;
            downloadBtn.href = fullUrl;

            // Resetear estado
            zoomScale = 1;
            translateX = 0;
            translateY = 0;
            applyTransform();

            portadaModal.classList.add('active');
            portadaModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden'; // Previene scroll de fondo
            document.documentElement.style.overflow = 'hidden'; // Asegura que se oculte la barra principal

            // Forzar scroll al inicio de la imagen
            const viewport = document.getElementById('portada-viewport');
            if (viewport) viewport.scrollTop = 0;
        });

        // Función para cerrar el Lightbox
        const closePortadaModal = () => {
            if (!portadaModal.classList.contains('active')) return;
            portadaModal.classList.remove('active');
            portadaModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';

            setTimeout(() => {
                modalImage.src = ''; // Limpiar src para liberar memoria
            }, 300);
        };
        closePortadaLightbox = closePortadaModal;

        // Cerrar al hacer clic en cualquier parte de la caja de luz, "tipo app"
        portadaModal.addEventListener('click', function (e) {
            // No cerrar si se hace clic en el botón de descarga o en los controles de zoom
            if (e.target.closest('#portada-download-btn') || e.target.closest('.portada-zoom-controls')) return;
            // No cerrar si hacen clic sobre la imagen (para permitir interacción, zoom y arrastre nativo)
            if (e.target.closest('#portada-lightbox-image')) return;
            closePortadaModal();
        });

        // --- CONTROL DE ZOOM ---
        zoomInBtn.addEventListener('click', () => {
            if (zoomScale < maxZoom) {
                zoomScale += zoomStep;
                applyTransform();
            }
        });

        zoomOutBtn.addEventListener('click', () => {
            if (zoomScale > minZoom) {
                zoomScale -= zoomStep;
                // Si vuelve a la escala normal o inferior, centrar imagen automáticamente
                if (zoomScale <= 1) {
                    translateX = 0;
                    translateY = 0;
                }
                applyTransform();
            }
        });

        zoomResetBtn.addEventListener('click', () => {
            zoomScale = 1;
            translateX = 0;
            translateY = 0;
            applyTransform();
        });

        // Zoom con la rueda del ratón (Mousewheel)
        portadaModal.addEventListener('wheel', (e) => {
            if (!portadaModal.classList.contains('active')) return;
            e.preventDefault();

            if (e.deltaY < 0 && zoomScale < maxZoom) {
                zoomScale += zoomStep;
            } else if (e.deltaY >= 0 && zoomScale > minZoom) {
                zoomScale -= zoomStep;
                if (zoomScale <= 1) {
                    translateX = 0;
                    translateY = 0;
                }
            }
            applyTransform();
        }, { passive: false });

        // --- CONTROL DE PANNING / DRAG (ARRASTRE) ---
        modalImage.addEventListener('mousedown', (e) => {
            if (zoomScale >= 1) { // Permitir arrastre siempre
                isDragging = true;
                modalImage.classList.add('panning');
                // Calcular posición inicial considerando la traslación actual
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                e.preventDefault();
            }
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                applyTransform();
            }
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            modalImage.classList.remove('panning');
        });

        // --- SOPORTE TACTIL (MOVILES) ---
        modalImage.addEventListener('touchstart', (e) => {
            if (zoomScale >= 1 && e.touches.length === 1) {
                isDragging = true;
                startX = e.touches[0].clientX - translateX;
                startY = e.touches[0].clientY - translateY;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (isDragging && e.touches.length === 1) {
                translateX = e.touches[0].clientX - startX;
                translateY = e.touches[0].clientY - startY;
                applyTransform();
            }
        }, { passive: true });

        document.addEventListener('touchend', () => {
            isDragging = false;
        });
    }

    // Cerrar visores con la tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        closePdfModal();
        closePortadaLightbox();
    });

    // ==========================================
    // 13. BUSCADOR COLAPSABLE EN TABLET Y MÓVIL
    // ==========================================
    function initHeaderSearch() {
        const topSearchForm = document.querySelector('.header-actions .search-form');
        const topSearchSubmit = document.querySelector('.header-actions .search-submit');
        const topSearchInput = document.querySelector('.header-actions .search-field');
        if (!topSearchForm || !topSearchSubmit || !topSearchInput) return;

        topSearchSubmit.addEventListener('click', function (e) {
            if (window.innerWidth > 1024) return;

            if (!topSearchForm.classList.contains('is-expanded')) {
                e.preventDefault();
                topSearchForm.classList.add('is-expanded');
                topSearchInput.focus();
            } else if (topSearchInput.value.trim() === '') {
                e.preventDefault();
                topSearchForm.classList.remove('is-expanded');
            }
        });

        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 1024 && topSearchForm.classList.contains('is-expanded') && !topSearchForm.contains(e.target)) {
                topSearchForm.classList.remove('is-expanded');
            }
        });
    }

    // ==========================================
    // 14. RADIO VITAL 101.5 FM
    // Un único <audio> (el del reproductor flotante, fuera de Swup) para que la
    // emisión siga sonando al navegar y la página de la radio no abra un segundo
    // stream. Ambos reproductores reflejan el mismo estado.
    // ==========================================
    const Radio = (function () {
        const STREAM = 'https://radio.diarioeloriental.com/radio.aac';
        let playing = false;
        let fillTimer = null;

        const byId = (id) => document.getElementById(id);
        const audio = () => byId('esFloatAudio') || byId('esRespAudio');

        function render(state) {
            const floatContainer = byId('esFloatingRadio');
            const floatIcon = byId('esFloatIcon');
            const floatStatus = byId('esFloatStatus');
            const pageBar = byId('esRespBar');
            const pageIcon = byId('esRespIcon');
            const pageStatus = byId('esRespStatus');
            const pageFill = byId('esRespFill');

            const labels = {
                connecting: ['CONECTANDO...', 'CONECTANDO...'],
                playing: ['EN VIVO', 'REPRODUCIENDO'],
                stopped: ['DETENIDO', 'DETENIDO'],
                error: ['ERROR', 'ERROR AL CONECTAR']
            }[state];

            if (floatContainer) floatContainer.classList.toggle('playing', state === 'playing');
            if (floatIcon) floatIcon.innerText = state === 'playing' ? 'pause' : 'play_arrow';
            if (floatStatus && labels) floatStatus.innerText = labels[0];

            if (pageBar) pageBar.classList.toggle('es-playing', state === 'playing');
            if (pageIcon) pageIcon.innerText = state === 'playing' ? 'pause' : 'play_arrow';
            if (pageStatus && labels) pageStatus.innerText = labels[1];
            if (pageFill && state !== 'playing') pageFill.style.width = '0%';
        }

        function animateFill() {
            clearInterval(fillTimer);
            let p = 0;
            fillTimer = setInterval(() => {
                const fill = byId('esRespFill');
                if (!playing) {
                    clearInterval(fillTimer);
                    return;
                }
                if (!fill) return;
                p = (p + 0.1) % 100;
                fill.style.width = p + '%';
            }, 100);
        }

        function start() {
            const el = audio();
            if (!el) return;
            el.src = STREAM + '?t=' + Date.now();
            render('connecting');
            el.play().then(() => {
                playing = true;
                render('playing');
                animateFill();
            }).catch(() => {
                render('error');
            });
        }

        function stop() {
            const el = audio();
            if (el) {
                el.pause();
                el.src = '';
            }
            playing = false;
            clearInterval(fillTimer);
            render('stopped');
        }

        return {
            toggle() {
                playing ? stop() : start();
            },
            stop,
            // Reflejar el estado actual al llegar a la página de la radio
            syncPage() {
                const pageStatus = byId('esRespStatus');
                if (!pageStatus) return;
                if (playing) {
                    render('playing');
                    animateFill();
                }
                const vol = byId('esRespVol');
                const el = audio();
                if (vol && el) vol.value = el.volume;
            },
            setVolume() {
                const vol = byId('esRespVol');
                const el = audio();
                if (!vol || !el) return;
                el.volume = vol.value;
                const mIcon = byId('esRespMuteIcon');
                if (mIcon) mIcon.innerText = parseFloat(vol.value) === 0 ? 'volume_off' : 'volume_up';
            },
            toggleMute() {
                const el = audio();
                const vol = byId('esRespVol');
                const mIcon = byId('esRespMuteIcon');
                if (!el) return;
                if (el.volume > 0) {
                    el.dataset.prevVolume = el.volume;
                    el.volume = 0;
                } else {
                    el.volume = parseFloat(el.dataset.prevVolume || '1');
                }
                if (vol) vol.value = el.volume;
                if (mIcon) mIcon.innerText = el.volume === 0 ? 'volume_off' : 'volume_up';
            }
        };
    })();

    // Funciones globales usadas por los atributos onclick de las plantillas
    window.toggleFloatRadio = Radio.toggle;
    window.toggleRespRadio = Radio.toggle;
    window.updateRespVol = Radio.setVolume;
    window.toggleRespMute = Radio.toggleMute;
    window.closeFloatRadio = function () {
        Radio.stop();
        const floatContainer = document.getElementById('esFloatingRadio');
        if (floatContainer) floatContainer.classList.add('hidden-by-user');
    };

    function initFloatingRadio() {
        const floatContainer = document.getElementById('esFloatingRadio');
        if (!floatContainer) return;

        // Show floating player automatically after 3 seconds
        setTimeout(() => {
            if (!floatContainer.classList.contains('hidden-by-user')) {
                floatContainer.classList.add('show');
            }
        }, 3000);
    }

    // ==========================================
    // 15. NAVEGACIÓN SIN RECARGA (SWUP)
    // ==========================================

    // Solo se re-ejecutan los scripts del contenido nuevo (embebidos, anuncios).
    // El antiguo SwupScriptsPlugin re-ejecutaba TODOS los scripts del documento
    // en cada navegación (jQuery, analítica, este mismo archivo...).
    function runContainerScripts(container) {
        container.querySelectorAll('script').forEach((oldScript) => {
            const type = (oldScript.getAttribute('type') || '').trim().toLowerCase();
            if (type && ['text/javascript', 'application/javascript', 'module'].indexOf(type) === -1) {
                return; // JSON-LD y otros datos no ejecutables
            }
            const script = document.createElement('script');
            Array.from(oldScript.attributes).forEach((attr) => script.setAttribute(attr.name, attr.value));
            script.textContent = oldScript.textContent;
            oldScript.replaceWith(script);
        });
    }

    // Las clases del <body> (single, page-template-..., etc.) cambian con cada página
    function syncBodyClass(visit) {
        const incoming = visit && visit.to && visit.to.document;
        if (!incoming || !incoming.body) return;
        const dark = document.body.classList.contains('dark-mode');
        document.body.className = incoming.body.className;
        document.body.classList.toggle('dark-mode', dark);
    }

    const SWUP_IGNORED_URL = /(\/wp-admin|\/wp-login\.php|\/turpial|admin-ajax\.php|\/feed\/?(\?|#|$)|[?&]preview=|\.(pdf|jpe?g|png|gif|webp|svg|zip|rar|docx?|xlsx?|pptx?|mp3|mp4)(\?|#|$))/i;

    function initSwup() {
        if (typeof window.Swup === 'undefined') return;

        const swup = new window.Swup({
            containers: ['#swup'],
            ignoreVisit: (url, { el } = {}) => Boolean(el && el.closest('[data-no-swup], #wpadminbar')) || SWUP_IGNORED_URL.test(url)
        });

        swup.hooks.before('content:replace', () => {
            teardownPage();
            closeMobileMenu();
            closePdfModal();
            closePortadaLightbox();
            document.body.style.overflow = '';
        });

        swup.hooks.on('content:replace', (visit) => {
            syncBodyClass(visit);
            runContainerScripts(swupContainer());
        });

        swup.hooks.on('page:view', () => {
            initPage(swupContainer());
        });
    }

    // ==========================================
    // ARRANQUE
    // ==========================================

    // Lo que depende del contenido de la página actual
    function initPage(root) {
        initInfiniteScroll(root);
        initAdSliders(root, true);
        rescueLazyImages(root);
        syncReadingProgress();
        Radio.syncPage();
    }

    function boot() {
        // Elementos persistentes (cabecera, pie, reproductor flotante)
        initDarkMode();
        initMobileMenu();
        initTicker();
        initReadingProgress();
        initNewPostsPolling();
        initPortadaLightbox();
        initHeaderSearch();
        initFloatingRadio();

        const header = document.getElementById('masthead');
        if (header && swupContainer() !== document.body) initAdSliders(header, false);

        initPage(swupContainer());
        initSwup();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
