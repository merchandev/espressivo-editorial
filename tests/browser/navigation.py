"""Prueba del menú en la instalación WordPress local, con Playwright y Edge."""
import argparse
from pathlib import Path
from urllib.parse import urlparse
from playwright.sync_api import sync_playwright

parser = argparse.ArgumentParser()
parser.add_argument('--url', default='http://127.0.0.1:8093')
parser.add_argument('--screenshots', type=Path)
args = parser.parse_args()
assert urlparse(args.url).hostname in ('127.0.0.1', 'localhost', '::1'), 'Usa una instalación local de pruebas.'
if args.screenshots:
    args.screenshots.mkdir(parents=True, exist_ok=True)

with sync_playwright() as p:
    browser = p.chromium.launch(channel='msedge', headless=True)
    page = browser.new_page(viewport={'width': 650, 'height': 850})
    errors = []
    page.on('pageerror', lambda error: errors.append(str(error)))
    page.goto(args.url, wait_until='domcontentloaded')
    page.wait_for_function('Boolean(window.cmsEditorialNavigation)')
    overlay = page.locator('#cms-menu-drawer')
    toggle = page.locator('.cms-menu-toggle')
    nav = page.locator('.cms-drawer-navigation')

    def screenshot(name):
        if args.screenshots:
            page.wait_for_timeout(220)
            page.screenshot(path=str(args.screenshots / name))

    def open_menu():
        toggle.click()
        page.wait_for_timeout(250)
        assert overlay.is_visible()
        assert page.locator('#page').evaluate('e=>e.inert')
        assert page.locator('.cms-menu-close').evaluate('e=>e===document.activeElement')
        assert nav.evaluate('e=>e.scrollTop') == 0, 'Cada apertura comienza arriba'

    def closed():
        assert overlay.is_hidden()
        assert toggle.get_attribute('aria-expanded') == 'false'
        assert not page.locator('#page').evaluate('e=>e.inert')
        assert page.evaluate('document.body.style.overflow') == ''

    def no_overflow():
        assert page.evaluate('document.documentElement.scrollWidth<=innerWidth+2'), page.evaluate('({width:innerWidth,scroll:document.documentElement.scrollWidth,overflow:[...document.querySelectorAll("body *")].filter(e=>e.getClientRects().length&&e.getBoundingClientRect().right>innerWidth+2).map(e=>({tag:e.tagName,cls:e.className,right:e.getBoundingClientRect().right})).slice(0,8)})')
        assert nav.evaluate('e=>e.scrollWidth<=e.clientWidth+2')

    ids = page.locator('[id]').evaluate_all('els=>els.map(e=>e.id)')
    assert len(ids) == len(set(ids)), 'IDs duplicados en los dos menús'
    open_menu()
    assert nav.locator('.sub-menu:visible').count() == 0
    screenshot('menu-tablet-collapsed.png')
    # Cuando el menú inicial es plano, crear ramas únicamente en el DOM de esta prueba.
    if nav.locator('.cms-menu-list > li > button[aria-controls]').count()<2:
        nav.evaluate('''nav=>{
            [...nav.querySelector('.cms-menu-list').children].slice(1,3).forEach((item,index)=>{
                item.classList.add('menu-item-has-children');
                const id='qa-drawer-children-'+index;
                item.insertAdjacentHTML('beforeend','<button class="cms-submenu-toggle" type="button" aria-expanded="false" aria-controls="'+id+'" aria-label="Subsecciones de prueba"><span class="cms-menu-chevron" aria-hidden="true"></span></button><ul class="sub-menu" id="'+id+'"><li><a href="#qa-child">Subsección de prueba</a></li></ul>');
            });
        }''')
    groups = nav.locator('.cms-menu-list > li > button[aria-controls]')
    assert groups.count() >= 2, 'La instalación debe tener al menos dos secciones con subsecciones.'
    first, second = groups.nth(0), groups.nth(1)
    first.click()
    children = page.locator('#' + first.get_attribute('aria-controls'))
    assert children.is_visible()
    assert children.evaluate('e=>getComputedStyle(e).position') == 'static'
    assert children.bounding_box()['height'] < 500, 'El submenú no debe ocupar una pantalla completa'
    assert children.bounding_box()['y'] - first.bounding_box()['y'] - first.bounding_box()['height'] < 12
    screenshot('menu-tablet-expanded.png')
    second.click()
    assert first.get_attribute('aria-expanded') == 'false'
    assert second.get_attribute('aria-expanded') == 'true'
    no_overflow()
    nav.evaluate('e=>e.scrollTop=e.scrollHeight')
    assert page.locator('.cms-menu-close').bounding_box()['y'] < 60
    page.locator('.cms-menu-close').focus()
    page.keyboard.press('Shift+Tab')
    assert page.locator('.cms-menu-footer a').evaluate('e=>e===document.activeElement')
    page.keyboard.press('Tab')
    assert page.locator('.cms-menu-close').evaluate('e=>e===document.activeElement')
    page.keyboard.press('/')
    assert page.locator('#cms-menu-search').evaluate('e=>e===document.activeElement')
    page.keyboard.press('Escape')
    closed()
    assert toggle.evaluate('e=>e===document.activeElement')

    for width, height in [(320, 568), (390, 844), (768, 1024), (1024, 768)]:
        page.set_viewport_size({'width': width, 'height': height})
        open_menu()
        no_overflow()
        assert page.locator('.cms-menu-panel').bounding_box()['height'] <= height + 1
        if width == 390:
            first.evaluate('e=>{if(e.getAttribute("aria-expanded")!="false")e.click()}')
            screenshot('menu-mobile-collapsed.png')
            first.click()
            screenshot('menu-mobile.png')
        page.keyboard.press('Escape')
        closed()
        if width == 320:
            page.keyboard.press('/')
            page.wait_for_timeout(230)
            assert page.locator('.ajax-search-input').is_visible()
            assert page.evaluate('document.documentElement.scrollWidth<=innerWidth+2')
            search_bounds = page.locator('.header-actions').bounding_box()
            assert toggle.bounding_box()['x'] + toggle.bounding_box()['width'] <= search_bounds['x'] + 1
            page.keyboard.press('Escape')
    page.set_viewport_size({'width': 768, 'height': 1024})
    open_menu()
    page.locator('.cms-menu-backdrop').click(position={'x': 20, 'y': 200})
    closed()
    page.set_viewport_size({'width': 390, 'height': 844})
    page.locator('#dark-mode-toggle').click()
    open_menu()
    assert page.locator('.cms-menu-panel').evaluate('e=>getComputedStyle(e).backgroundColor') == 'rgb(15, 23, 42)'
    screenshot('menu-mobile-dark.png')
    page.keyboard.press('Escape')
    page.locator('#dark-mode-toggle').click()

    page.set_viewport_size({'width': 1440, 'height': 1000})
    page.wait_for_function('!document.body.classList.contains("cms-menu-compact")')
    assert not toggle.is_visible()
    assert page.evaluate('document.documentElement.scrollWidth<=innerWidth+2')
    desktop = page.locator('.cms-desktop-menu')
    if desktop.locator('.cms-menu-list > li > button[aria-controls]').count()==0:
        desktop.evaluate('''nav=>{
            const list=nav.querySelector('.cms-menu-list');
            list.insertAdjacentHTML('beforeend','<li class="menu-item-has-children cms-menu-wide"><button class="cms-menu-group" type="button" aria-expanded="false" aria-controls="qa-desktop-children"><span>Más de prueba</span><span class="cms-menu-chevron" aria-hidden="true"></span></button><ul id="qa-desktop-children" class="sub-menu">'+Array.from({length:12},(_,i)=>'<li><a href="/qa-'+i+'/">Enlace de prueba '+i+'</a></li>').join('')+'</ul></li>');
        }''')
    rows = desktop.locator('.cms-menu-list > li').evaluate_all('els=>els.map(e=>Math.round(e.getBoundingClientRect().top))')
    assert len(set(rows)) == 1, 'Menú de escritorio en una sola fila'
    desktop_group = desktop.locator('.cms-menu-list > li > button[aria-controls]').last
    desktop_group.focus()
    page.keyboard.press('Space')
    desktop_children = page.locator('#' + desktop_group.get_attribute('aria-controls'))
    assert desktop_children.is_visible()
    assert desktop_children.bounding_box()['x'] >= 0
    assert desktop_children.bounding_box()['x'] + desktop_children.bounding_box()['width'] <= 1440
    screenshot('menu-desktop.png')
    # El menú inicial puede tener el desplegable Más plano; añadir una rama solo al DOM de prueba.
    if desktop_children.locator('li > button[aria-controls]').count() == 0:
        desktop_children.evaluate('''menu=>{
            const item=menu.firstElementChild;
            item.classList.add('menu-item-has-children');
            const button=document.createElement('button');
            button.type='button';button.className='cms-submenu-toggle';
            button.setAttribute('aria-controls','cms-test-third-level');button.setAttribute('aria-expanded','false');
            button.setAttribute('aria-label','Subsecciones de prueba');
            button.innerHTML='<span class="cms-menu-chevron" aria-hidden="true"></span>';
            const children=document.createElement('ul');children.id='cms-test-third-level';children.className='sub-menu';
            children.innerHTML='<li><a href="#test-third-level">Tercer nivel de prueba</a></li>';
            item.append(button,children);
        }''')
    nested = desktop_children.locator('li > button[aria-controls]').first
    if nested.count():
        nested.focus()
        page.keyboard.press('Space')
        submenu = page.locator('#' + nested.get_attribute('aria-controls'))
        assert submenu.is_visible()
        assert submenu.bounding_box()['x'] >= 0
        assert submenu.bounding_box()['x'] + submenu.bounding_box()['width'] <= 1440
    page.keyboard.press('Escape')
    page.set_viewport_size({'width': 1440, 'height': 450})
    desktop_group.focus()
    if desktop_group.get_attribute('aria-expanded') == 'true':
        page.keyboard.press('Space')
    page.keyboard.press('Space')
    bounds = desktop_children.bounding_box()
    assert bounds['y'] + bounds['height'] <= 450, 'El desplegable respeta el alto disponible en horizontal'
    page.set_viewport_size({'width': 1440, 'height': 1000})
    page.wait_for_function('(id)=>parseFloat(document.getElementById(id).style.maxHeight)>400',arg=desktop_group.get_attribute('aria-controls'))

    desktop.locator('.cms-menu-list > li > a').first.evaluate('e=>e.textContent+=" Sección con un nombre editorial muy largo".repeat(8)')
    page.wait_for_function('document.body.classList.contains("cms-menu-compact")')
    assert toggle.is_visible(), 'Menús largos pasan a panel antes de desbordarse'
    assert page.evaluate('document.documentElement.scrollWidth<=innerWidth+2')
    page.goto(args.url, wait_until='domcontentloaded')
    page.set_viewport_size({'width': 390, 'height': 844})
    open_menu()
    page.set_viewport_size({'width': 1440, 'height': 1000})
    page.wait_for_function('document.getElementById("cms-menu-drawer").hidden')
    closed()

    page.set_viewport_size({'width': 390, 'height': 844})
    open_menu()
    destination = nav.locator('.cms-menu-list > li > a').nth(1)
    destination_url = destination.get_attribute('href')
    destination.click()
    page.wait_for_url(destination_url)
    closed()
    page.wait_for_function('Boolean(document.querySelector(".cms-drawer-navigation a[aria-current=page]"))')
    open_menu()
    page.locator('#cms-menu-search').fill('Tecnología')
    page.locator('#cms-menu-search').press('Enter')
    page.wait_for_url('**/?s=*')
    assert page.locator('h1').inner_text().startswith('Resultados para')
    closed()
    assert not errors, errors
    print('OK: menú de 320–1440 px, acordeones, tercer nivel, foco, cierre, búsqueda y navegación.')
    browser.close()
