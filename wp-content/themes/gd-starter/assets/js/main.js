(function () {
	'use strict';
	var header = document.querySelector('[data-gd-header]');
	var toggle = document.querySelector('[data-gd-nav-toggle]');

	if (toggle && header) {
		toggle.addEventListener('click', function () {
			var open = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', String(!open));
			header.classList.toggle('is-nav-open', !open);
			document.documentElement.classList.toggle('gd-no-scroll', !open);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && header.classList.contains('is-nav-open')) toggle.click();
		});
	}

	/* Solid header once scrolled past the hero. */
	if (header) {
		var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 24); };
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* Reveal-on-scroll for sections (progressive enhancement). */
	if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); } });
		}, { rootMargin: '0px 0px -10% 0px' });
		document.querySelectorAll('.gd-reveal').forEach(function (el) { io.observe(el); });
	} else {
		document.querySelectorAll('.gd-reveal').forEach(function (el) { el.classList.add('is-visible'); });
	}

	/* FAQ: close siblings when one opens (details/summary pattern). */
	document.querySelectorAll('.gd-faq').forEach(function (group) {
		group.addEventListener('toggle', function (e) {
			if (e.target.open) group.querySelectorAll('details[open]').forEach(function (d) { if (d !== e.target) d.open = false; });
		}, true);
	});
	/* Get involved: Request help / Volunteer switcher (hash-driven, no reload). */
	document.querySelectorAll('[data-gd-switch]').forEach(function (root) {
		root.classList.add('is-js');
		var tabs = root.querySelectorAll('[data-gd-tab]');
		var panels = root.querySelectorAll('[data-gd-panel]');
		var show = function (id, focus) {
			if (!root.querySelector('[data-gd-panel="' + id + '"]')) id = tabs[0].getAttribute('data-gd-tab');
			tabs.forEach(function (t) { var on = t.getAttribute('data-gd-tab') === id; t.classList.toggle('is-active', on); t.setAttribute('aria-selected', String(on)); });
			panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-gd-panel') === id); });
			if (focus) { var h = root.querySelector('[data-gd-panel="' + id + '"] h2'); if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); } }
		};
		tabs.forEach(function (t) {
			t.addEventListener('click', function (e) {
				e.preventDefault();
				var id = t.getAttribute('data-gd-tab');
				history.replaceState(null, '', '#' + id);
				show(id, true);
				root.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		});
		var fromHash = function () { show((location.hash || '').replace('#', '') || tabs[0].getAttribute('data-gd-tab'), false); };
		window.addEventListener('hashchange', fromHash);
		fromHash();
		/* After a submit, the redirect carries ?form=… or ?sent=… — open that panel. */
		var q = new URLSearchParams(location.search);
		var want = q.get('form') || q.get('sent');
		if (want) show(want === 'help' ? 'request-help' : want, false);
	});
})();
