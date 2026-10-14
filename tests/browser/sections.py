"""Verifica la estructura de seis secciones en la vista previa local."""
from pathlib import Path
import argparse
from urllib.parse import urlparse
from playwright.sync_api import sync_playwright
parser=argparse.ArgumentParser()
parser.add_argument('--url',default='http://127.0.0.1:8093')
parser.add_argument('--screenshots',type=Path)
args=parser.parse_args()
assert urlparse(args.url).hostname in ('localhost','127.0.0.1','::1')
names=['Nacional','Deportes','Internacional','Farándula','Ciencias','Genéricas']
slugs=['nacional','deportes','internacional','farandula','ciencias','genericas']
if args.screenshots: args.screenshots.mkdir(parents=True,exist_ok=True)
with sync_playwright() as p:
    browser=p.chromium.launch(channel='msedge',headless=True)
    page=browser.new_page(viewport={'width':1440,'height':1000})
    errors=[]
    page.on('pageerror',lambda error:errors.append(str(error)))
    page.goto(args.url,wait_until='domcontentloaded')
    page.wait_for_function('Boolean(window.cmsEditorialNavigation)')
    assert page.locator('#primary-menu-desktop > li > a').all_text_contents()==['Inicio']+names
    assert page.locator('#primary-menu-mobile > li > a').all_text_contents()==['Inicio']+names
    assert page.locator('.footer-categories a').all_text_contents()==names
    assert page.locator('.ed-section-heading h2').all_text_contents()==names
    assert page.locator('#primary-menu-desktop .sub-menu').count()==0
    if args.screenshots: page.screenshot(path=str(args.screenshots/'sections-desktop.png'))
    for slug,name in zip(slugs,names):
        response=page.goto(args.url+'/'+slug+'/',wait_until='domcontentloaded')
        assert response.status==200,(slug,response.status)
        assert page.locator('h1').inner_text()==name
        assert page.locator('.category-grid .ed-story').count()>0
    for old,new in [('mundo','internacional'),('local','genericas'),('ciencia-y-tecnologia','ciencias'),('economia','nacional')]:
        page.goto(args.url+'/'+old+'/',wait_until='domcontentloaded')
        assert page.url.rstrip('/').endswith('/'+new),(old,page.url)
        page.goto(args.url+'/category/'+old+'/',wait_until='domcontentloaded')
        assert page.url.rstrip('/').endswith('/'+new),(old,page.url)
    page.goto(args.url,wait_until='domcontentloaded')
    for width,height in [(320,568),(390,844),(650,850),(768,1024),(1024,768)]:
        page.set_viewport_size({'width':width,'height':height})
        page.locator('.cms-menu-toggle').click()
        page.wait_for_timeout(250)
        assert page.locator('#cms-menu-drawer').is_visible()
        assert page.locator('#primary-menu-mobile > li > a').all_text_contents()==['Inicio']+names
        assert page.locator('.cms-drawer-navigation .sub-menu').count()==0
        assert page.evaluate('document.documentElement.scrollWidth<=innerWidth+2')
        if args.screenshots and width==390: page.screenshot(path=str(args.screenshots/'sections-mobile.png'))
        page.keyboard.press('Escape')
        assert page.locator('#cms-menu-drawer').is_hidden()
    assert not errors,errors
    print('OK: seis secciones en menús y portada, páginas funcionales, redirecciones y móvil sin errores.')
    browser.close()
