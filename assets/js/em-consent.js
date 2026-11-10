/**
 * EM Consent : bannière, préférences et déblocage après consentement. Aucune dépendance.
 *
 * API publique :
 *   emConsent.get('mesure')       → true si la catégorie est acceptée
 *   emConsent.open()              → ouvre les préférences
 *   emConsent.on(function (choix) { … })
 *   document.addEventListener('em-consent:change', function (e) { e.detail … })
 * Tout lien vers « #em-consent » ouvre les préférences.
 */
(function () {
	'use strict';

	var w = window;
	var d = document;
	var cfg = w.emConsentConfig;
	if (!cfg || w.emConsent) {
		return;
	}

	var CATS = cfg.categories || [];
	var banner = d.getElementById('em-consent');
	var dialog = d.getElementById('em-consent-preferences');
	var state = readState();
	var retour = null;
	var parChoix = false;
	var charges = {};
	var abonnes = [];
	var espace = null;

	w.dataLayer = w.dataLayer || [];
	if (!w.gtag) {
		w.gtag = function () {
			w.dataLayer.push(arguments);
		};
	}

	function readState() {
		var m = d.cookie.match(new RegExp('(?:^|;\\s*)' + cfg.cookie + '=([^;]*)'));
		var s = null;
		if (!m) {
			return null;
		}
		try {
			s = JSON.parse(decodeURIComponent(m[1]));
		} catch (e) {
			return null;
		}
		return s && s.v === cfg.revision && s.c ? s : null;
	}

	function granted(cat) {
		return cat === 'necessaires' || !!(state && state.c[cat]);
	}

	function allowed(el) {
		return (el.getAttribute('data-em-consent') || '').split(/\s+/).every(function (c) {
			return !c || granted(c);
		});
	}

	function randomId() {
		var a = new Uint8Array(8);
		(w.crypto || w.msCrypto).getRandomValues(a);
		return Array.prototype.map.call(a, function (b) {
			return ('0' + b.toString(16)).slice(-2);
		}).join('');
	}

	function choixActuels() {
		var c = {};
		CATS.forEach(function (k) {
			c[k] = granted(k);
		});
		return c;
	}

	/* Enregistrement du choix ------------------------------------------------------------- */

	function save(choix) {
		var avant = state;
		state = { v: cfg.revision, c: {}, t: Date.now(), id: (avant && avant.id) || randomId() };
		CATS.forEach(function (k) {
			state.c[k] = !!choix[k];
		});
		d.cookie = cfg.cookie + '=' + encodeURIComponent(JSON.stringify(state)) +
			'; Max-Age=' + cfg.jours * 86400 + '; Path=/; SameSite=Lax' +
			(location.protocol === 'https:' ? '; Secure' : '');

		updateGoogle();
		var detail = choixActuels();
		w.dataLayer.push({ event: 'em_consent_update', em_consent_mesure: !!detail.mesure, em_consent_marketing: !!detail.marketing });
		abonnes.forEach(function (cb) {
			try {
				cb(detail);
			} catch (e) {}
		});
		d.dispatchEvent(new CustomEvent('em-consent:change', { detail: detail }));

		// Un consentement retiré : les scripts déjà exécutés ne se déchargent pas. On efface leurs
		// témoins et on recharge la page, qui repart sans eux.
		var retire = avant && CATS.some(function (k) {
			return avant.c[k] && !state.c[k];
		});
		if (retire) {
			CATS.forEach(function (k) {
				if (avant.c[k] && !state.c[k]) {
					effacer(k);
				}
			});
			location.reload();
			return;
		}
		apply();
	}

	function effacer(cat) {
		var prefixes = (cfg.effacer && cfg.effacer[cat]) || [];
		var host = location.hostname.split('.');
		d.cookie.split(';').forEach(function (paire) {
			var nom = paire.split('=')[0].trim();
			if (!prefixes.some(function (p) { return nom.indexOf(p) === 0; })) {
				return;
			}
			for (var i = 0; i < host.length - 1; i++) {
				var dom = host.slice(i).join('.');
				d.cookie = nom + '=; Max-Age=0; Path=/; Domain=.' + dom;
			}
			d.cookie = nom + '=; Max-Age=0; Path=/';
		});
	}

	/* Google et déblocage ----------------------------------------------------------------- */

	function updateGoogle() {
		if (!cfg.consentMode) {
			return;
		}
		var a = granted('mesure') ? 'granted' : 'denied';
		var k = granted('marketing') ? 'granted' : 'denied';
		w.gtag('consent', 'update', { analytics_storage: a, ad_storage: k, ad_user_data: k, ad_personalization: k });
		w.gtag('set', 'ads_data_redaction', k === 'denied');
	}

	function addScript(src) {
		var s = d.createElement('script');
		s.async = true;
		s.src = src;
		d.head.appendChild(s);
	}

	function apply() {
		if (!state) {
			return;
		}
		if (cfg.gtm && !charges.gtm && CATS.some(granted)) {
			charges.gtm = true;
			w.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
			addScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(cfg.gtm));
		}
		if (cfg.ga4 && !charges.ga4 && granted('mesure')) {
			charges.ga4 = true;
			addScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.ga4));
			w.gtag('js', new Date());
			w.gtag('config', cfg.ga4);
		}
		unblockScripts();
		unblockFrames();
	}

	// Les scripts bloqués sont réactivés un à un, dans l'ordre de la page : un script en ligne qui
	// dépend d'un fichier attend que celui-ci soit chargé.
	function unblockScripts() {
		var liste = Array.prototype.filter.call(d.querySelectorAll('script[type="text/plain"][data-em-consent]'), allowed);
		(function suivant() {
			var vieux = liste.shift();
			if (!vieux) {
				return;
			}
			var s = d.createElement('script');
			Array.prototype.forEach.call(vieux.attributes, function (a) {
				if (a.name !== 'type' && a.name !== 'data-em-consent' && a.name !== 'data-em-consent-type') {
					s.setAttribute(a.name, a.value);
				}
			});
			if (vieux.getAttribute('data-em-consent-type')) {
				s.type = vieux.getAttribute('data-em-consent-type');
			}
			s.setAttribute('data-em-consent-actif', vieux.getAttribute('data-em-consent'));
			if (vieux.src) {
				s.async = false;
				s.onload = s.onerror = suivant;
				vieux.parentNode.replaceChild(s, vieux);
			} else {
				s.text = vieux.text;
				vieux.parentNode.replaceChild(s, vieux);
				suivant();
			}
		})();
	}

	function unblockFrames() {
		Array.prototype.forEach.call(d.querySelectorAll('iframe[data-em-consent][data-src]'), function (f) {
			if (!allowed(f)) {
				return;
			}
			var bloc = f.closest('.em-consent-bloque');
			if (bloc) {
				bloc.parentNode.replaceChild(f, bloc);
			}
			f.hidden = false;
			f.src = f.getAttribute('data-src');
			f.removeAttribute('data-src');
		});
	}

	/* Bannière et préférences ------------------------------------------------------------- */

	// La bannière ne doit cacher ni la fin de la page ni l'élément qui a le focus (WCAG 2.4.11).
	function ajuster() {
		if (!banner || banner.hidden) {
			return;
		}
		var h = banner.offsetHeight;
		d.documentElement.style.scrollPaddingBottom = h + 'px';
		if (!espace) {
			espace = d.createElement('div');
			espace.className = 'em-consent-espace';
			espace.setAttribute('aria-hidden', 'true');
			d.body.appendChild(espace);
		}
		espace.style.height = h + 'px';
	}

	function showBanner() {
		if (!banner) {
			return;
		}
		banner.hidden = false;
		ajuster();
	}

	function hideBanner() {
		if (!banner) {
			return;
		}
		banner.hidden = true;
		d.documentElement.style.scrollPaddingBottom = '';
		if (espace) {
			espace.remove();
			espace = null;
		}
	}

	function openPreferences(origine) {
		if (!dialog || dialog.open) {
			return;
		}
		retour = origine || d.activeElement;
		CATS.forEach(function (k) {
			var i = dialog.querySelector('[data-em-consent-categorie="' + k + '"]');
			if (i) {
				i.checked = granted(k);
			}
		});
		dialog.showModal();
	}

	function lirePreferences() {
		var c = {};
		CATS.forEach(function (k) {
			var i = dialog && dialog.querySelector('[data-em-consent-categorie="' + k + '"]');
			c[k] = !!(i && i.checked);
		});
		return c;
	}

	// Après un choix, le focus revient au lien qui a ouvert les préférences ; s'il était dans la
	// bannière, qui disparaît, il passe au contenu principal.
	function rendreFocus(etaitDedans) {
		var el = retour;
		retour = null;
		if (el && el.focus && d.contains(el) && !(banner && banner.contains(el))) {
			el.focus();
			return;
		}
		if (!etaitDedans) {
			return;
		}
		var main = d.querySelector('main, [role="main"]') || d.body;
		if (!main.hasAttribute('tabindex')) {
			main.setAttribute('tabindex', '-1');
		}
		main.focus({ preventScroll: true });
	}

	function finish(choix) {
		var dedans = !!(banner && banner.contains(d.activeElement)) || !!(dialog && dialog.open);
		if (dialog && dialog.open) {
			parChoix = true;
			dialog.close();
		}
		hideBanner();
		rendreFocus(dedans);
		save(choix);
	}

	function tous(valeur) {
		var c = {};
		CATS.forEach(function (k) {
			c[k] = valeur;
		});
		finish(c);
	}

	if (dialog) {
		dialog.addEventListener('close', function () {
			if (parChoix) {
				parChoix = false;
				return;
			}
			// Fermée sans choix (Échap ou bouton Fermer) : retour à l'élément d'origine.
			var el = retour;
			retour = null;
			if (el && el.focus && d.contains(el)) {
				el.focus();
			}
		});
		// Un clic sur le fond (hors de la boîte) ferme la fenêtre.
		dialog.addEventListener('click', function (e) {
			if (e.target === dialog) {
				dialog.close();
			}
		});
	}

	d.addEventListener('click', function (e) {
		var t = e.target.closest && e.target.closest('[data-em-consent-action],[data-em-consent-autoriser],a[href$="#em-consent"]');
		if (!t) {
			return;
		}
		var autoriser = t.getAttribute('data-em-consent-autoriser');
		if (autoriser) {
			var bloc = t.closest('.em-consent-bloque');
			var cadre = bloc && bloc.querySelector('iframe');
			var c = choixActuels();
			c[autoriser] = true;
			retour = null;
			finish(c);
			if (cadre) {
				cadre.setAttribute('tabindex', '-1');
				cadre.focus();
			}
			return;
		}
		var action = t.getAttribute('data-em-consent-action');
		if (!action) {
			e.preventDefault();
			openPreferences(t);
			return;
		}
		if (action === 'accepter') {
			tous(true);
		} else if (action === 'refuser') {
			tous(false);
		} else if (action === 'personnaliser') {
			openPreferences(t);
		} else if (action === 'enregistrer') {
			finish(lirePreferences());
		} else if (action === 'fermer' && dialog) {
			dialog.close();
		}
	});

	w.addEventListener('resize', ajuster);

	w.emConsent = {
		get: granted,
		open: function () {
			openPreferences();
		},
		on: function (cb) {
			if (typeof cb === 'function') {
				abonnes.push(cb);
			}
		}
	};

	if (state) {
		apply();
	} else {
		showBanner();
	}
	if (location.hash === '#em-consent') {
		openPreferences();
	}
})();
