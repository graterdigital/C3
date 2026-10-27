#!/usr/bin/env python3
"""Static preview snapshot of the local WP site: inlines local CSS/JS, rewrites internal links to flat files."""
import re, sys, pathlib, urllib.request, urllib.parse

BASE = "http://localhost:8090"
OUT = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else "/home/claude/")
OUT.mkdir(parents=True, exist_ok=True)
PAGES = {
    "/": "index.html",
    "/services/": "treks.html",
    "/treks/8-day-classic-": "trek-8-day.html",
    "/about/": "about.html",
    "/reviews/": "reviews.html",
    "/faq/": "faq.html",
    "/contact/": "contact.html",
    "/sustainable-trekking/": "sustainable.html",
    "/acclimating-hikes/": "acclimating.html",
}

def get(url):
    with urllib.request.urlopen(url, timeout=30) as r:
        return r.read().decode("utf-8", "replace")

cache = {}
def asset(url):
    if url not in cache:
        cache[url] = get(url)
    return cache[url]

for path, fname in PAGES.items():
    html = get(BASE + path)
    # inline local stylesheets
    def css_repl(m):
        href = m.group(1)
        if not href.startswith(BASE):
            return m.group(0)
        css = asset(href)
        css = re.sub(r"url\((['\"]?)/", r"url(\1" + BASE + "/", css)
        return "<style>" + css + "</style>"
    html = re.sub(r"<link rel='stylesheet'[^>]*href='([^']+)'[^>]*/?>", css_repl, html)
    html = re.sub(r'<link rel="stylesheet"[^>]*href="([^"]+)"[^>]*/?>', css_repl, html)
    # inline local scripts
    def js_repl(m):
        src = m.group(1)
        if not src.startswith(BASE):
            return m.group(0)
        return "<script>" + asset(src) + "</script>"
    html = re.sub(r"<script[^>]*src=['\"]([^'\"]+)['\"][^>]*></script>", js_repl, html)
    # rewrite internal links to flat files
    for p, f in PAGES.items():
        html = html.replace(f'href="{BASE}{p}"', f'href="{f}"').replace(f"href='{BASE}{p}'", f"href='{f}'")
    html = html.replace(BASE + "/wp-content/", "wp-content/")  # leftover refs (images etc.)
    html = re.sub(r'<link rel=["\']https://api\.w\.org/["\'][^>]*>', "", html)
    (OUT / fname).write_text(html, encoding="utf-8")
    print("wrote", fname, len(html))
