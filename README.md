# EM Consent

![Version](https://img.shields.io/badge/version-1.1.0-blue) ![WordPress](https://img.shields.io/badge/WordPress-6.5%2B-21759b) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4) ![Licence](https://img.shields.io/badge/licence-GPL%20v2%2B-green)

Consentement aux témoins conforme à la **Loi 25 du Québec**, pour tous les sites WordPress d'emsolutions.ca, faits avec le core ou non.

**Principe : aucun domaine tiers n'est contacté avant le consentement.** Google Tag Manager, GA4, pixels, vidéos et cartes ne se chargent qu'après l'accord du visiteur, catégorie par catégorie.

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Installation et mises à jour](#installation-et-mises-à-jour)
- [Mise en place sur un site](#mise-en-place-sur-un-site)
- [Bloquer un script ou une iframe](#bloquer-un-script-ou-une-iframe)
- [Google : mode de consentement v2, GTM et GA4](#google--mode-de-consentement-v2-gtm-et-ga4)
- [Page de politique des témoins](#page-de-politique-des-témoins)
- [Apparence](#apparence)
- [Textes et langues](#textes-et-langues)
- [Accessibilité](#accessibilité)
- [Performance et cache](#performance-et-cache)
- [API JavaScript](#api-javascript)
- [Hooks pour développeurs](#hooks-pour-développeurs)
- [Limites connues](#limites-connues)
- [Développement](#développement)

## Fonctionnalités

- **Bannière** Accepter, Refuser et Personnaliser dès le premier écran, les trois boutons du même style : refuser est aussi simple qu'accepter. Elle se place en bas, sans voile, et ne bloque pas la lecture de la page.
- **Catégories** : nécessaires (toujours actives), mesure d'audience, marketing. Les deux dernières sont **désactivées par défaut**, comme la Loi 25 l'exige pour toute technologie qui identifie, localise ou profile.
- **Mode de consentement de Google v2**, tout refusé par défaut. **GTM et GA4 ne se chargent qu'après l'accord.**
- **Blocage** des scripts (filtre, balisage manuel, scripts personnalisés) et des iframes de services tiers dans le contenu (YouTube, Vimeo, Google Maps, Calendly, réseaux sociaux…).
- **Lien « Gestion des témoins »** : tout lien vers `#em-consent` (menu, bloc, pied de page) ou le code court `[em_consent_gestion]` rouvre les préférences.
- **Durée du choix** réglable (12 mois par défaut). Le consentement est redemandé quand les catégories changent, ou d'un clic (« Redemander à tous »).
- **Retrait du consentement** : GA4 est coupé, les envois de dernière minute des traqueurs connus sont retenus, les témoins de la catégorie retirée sont effacés et la page se recharge sans ses scripts.
- **Page « Politique relative aux témoins »** générée à l'affichage à partir des réglages, créée d'un clic dans chaque langue, traductions liées.
- **Apparence** : six positions (cartes en bas à gauche, à droite ou au centre, barres en haut ou en bas, centre), largeur, distance des bords, taille du texte, police, couleurs.
- **Textes par langue**, modifiables par le client, en français et en anglais par défaut. Compatible WPML et Polylang, sans String Translation ni correctif.
- **Accessibilité WCAG 2.2 AA** et **performance** : aucun jQuery, aucun appel externe, environ 4 Ko de JS et 2 Ko de CSS une fois compressés (gzip).
- **Mises à jour** depuis les releases GitHub, par le mécanisme natif de WordPress.

## Installation et mises à jour

1. Téléchargez `em-consent.zip` depuis la dernière [release](https://github.com/em2dev/em-consent/releases), puis **Extensions → Ajouter → Téléverser**.
2. Activez l'extension. Les réglages par défaut sont créés à la première activation ; une réactivation ou une mise à jour n'y touche jamais.

Les mises à jour apparaissent ensuite dans **Tableau de bord → Mises à jour**, comme pour une extension de wordpress.org. L'en-tête `Update URI` branche WordPress sur `includes/class-em-consent-updater.php`, qui lit la dernière release GitHub au plus deux fois par jour, pendant la vérification des mises à jour seulement (jamais à l'affichage d'une page).

Prérequis : WordPress 6.5+, PHP 7.4+.

## Mise en place sur un site

1. **Réglages → Témoins → Général** : identifiant GTM (`GTM-…`) ou GA4 (`G-…`), catégories proposées, durée du choix.
2. **Pied de page** : ajoutez un lien « Gestion des témoins ». Dans **Apparence → Menus** (ou l'éditeur de navigation), un *lien personnalisé* avec l'adresse `#em-consent` ; ailleurs, le code court `[em_consent_gestion]`.
3. **Politique de confidentialité** : la bannière pointe vers la page de confidentialité de WordPress (**Réglages → Confidentialité**), traduite par WPML si elle l'est. Une autre adresse peut être donnée par langue dans l'onglet Textes.
4. **Scripts** : déclarez ceux du thème (filtre `em_consent_scripts`) et collez les autres dans l'onglet Scripts.
5. **Vérifiez** dans une fenêtre privée : aucune requête vers un autre domaine avant le choix (outils du navigateur, onglet Réseau), ou `npm test` (voir [Développement](#développement)).

## Bloquer un script ou une iframe

Un élément bloqué porte `data-em-consent="<catégorie>"`, avec la catégorie `mesure` ou `marketing`. Le navigateur n'exécute pas un script `type="text/plain"` et n'en télécharge pas la source ; EM Consent le réactive, dans l'ordre de la page, quand la catégorie est acceptée.

### Scripts enregistrés (`wp_enqueue_script`)

```php
add_filter( 'em_consent_scripts', function ( $scripts ) {
	$scripts['mon-pixel']        = 'marketing';
	$scripts['mon-pixel-config'] = 'marketing'; // Les scripts qui en dépendent aussi.
	return $scripts;
} );
```

Le script, ses scripts en ligne (`wp_add_inline_script`) et ses traductions sont bloqués ensemble. Les handles déclarés sont listés dans l'onglet Scripts.

### Balisage manuel

```html
<script type="text/plain" data-em-consent="mesure" src="https://…/analytics.js"></script>
<script type="text/plain" data-em-consent="marketing">
	/* code exécuté seulement après l'accord au marketing */
</script>
```

Pour un module : ajoutez `data-em-consent-type="module"`.

### Scripts personnalisés (onglet Scripts)

Une zone par catégorie, pour Meta Pixel, Clarity, Hotjar, LinkedIn… Seules les balises `<script>` sont gardées : un `<noscript>` ou une image de suivi contacterait le tiers avant le consentement. Réservé aux comptes qui ont le droit `unfiltered_html`.

### Iframes

Dans le contenu des pages, les iframes des domaines connus (YouTube, Vimeo, Google Maps, Calendly, Facebook, Instagram, X, TikTok, LinkedIn, Spotify, SoundCloud…) sont remplacées par un avis et un bouton « Accepter les témoins marketing et afficher ». La liste se modifie avec le filtre `em_consent_iframe_domains`. Dans un gabarit, balisez l'iframe vous-même :

```html
<iframe data-em-consent="marketing" data-src="https://…" hidden title="…"></iframe>
```

Les vidéos en façade du thème du core (aucune requête avant le clic) n'ont pas besoin d'être bloquées.

## Google : mode de consentement v2, GTM et GA4

Le premier élément de `<head>` est un court script en ligne qui envoie les valeurs par défaut :

| Signal | Défaut | Accordé par |
| --- | --- | --- |
| `analytics_storage` | refusé | Mesure d'audience |
| `ad_storage`, `ad_user_data`, `ad_personalization` | refusé | Marketing |
| `personalization_storage` | refusé | — |
| `functionality_storage`, `security_storage` | accordé | nécessaires |

`ads_data_redaction` reste actif tant que le marketing est refusé. Un choix déjà enregistré est appliqué dans ce même script, avant toute balise.

EM Consent applique le mode **de base** : GTM et GA4 ne sont pas chargés avant l'accord. Le mode « avancé » de Google (balises chargées tout de suite, pings sans témoins) contacterait Google avant le consentement.

- **GTM** se charge à la première catégorie acceptée. Dans le conteneur, exigez le consentement voulu sur chaque balise (*Paramètres avancés → Paramètres d'autorisation → Exiger un consentement supplémentaire* : `analytics_storage` pour GA4, `ad_storage` pour Google Ads et Meta). L'événement `em_consent_update` (variables `em_consent_mesure`, `em_consent_marketing`) peut servir de déclencheur.
- **GA4 seul** (sans GTM) se charge après l'accord à la mesure d'audience. Ne remplissez pas les deux si GA4 est déjà dans le conteneur : les visites seraient comptées deux fois (un avertissement s'affiche).

## Page de politique des témoins

**Réglages → Témoins → Politique** :

1. Cochez les **services utilisés** (Clarity, Hotjar, Google Ads, Meta Pixel, LinkedIn, TikTok, YouTube, Vimeo, Google Maps). GTM et GA4 sont détectés d'après leur identifiant. Ajouter un service redemande le consentement à tous.
2. **Créer les pages** : une page « Politique relative aux témoins » par langue active (WPML ou Polylang), traductions liées. Une langue qui a déjà sa page est gardée : rien n'est recréé ni écrasé.

La page contient le code court `[em_consent_politique]` (attribut `niveau` pour le niveau des intertitres, 2 par défaut). Il est construit **à l'affichage**, dans la langue de la page : introduction, une section par catégorie avec le tableau de ses témoins (nom, fournisseur avec lien vers sa politique, finalité, durée), la durée du choix, le lien de gestion et la date de dernière mise à jour. Cette date change d'elle-même quand les catégories, les services, les identifiants ou la durée changent. La page suit donc les réglages sans jamais être regénérée.

La bannière renvoie à cette page dans la langue de la visite (à défaut, à la page de confidentialité de WordPress ; une adresse donnée dans l'onglet Textes passe avant les deux).

Les noms et durées des témoins viennent de la documentation des éditeurs (relevé de 2026-11) : ils peuvent changer. Le filtre `em_consent_services` corrige une durée ou ajoute un service. Cette page ne remplace pas la **politique de confidentialité** exigée par la Loi 25 (responsable de la protection des renseignements personnels, fins, droits, conservation…), qui reste à rédiger et à faire valider.

## Apparence

**Réglages → Témoins → Apparence** : position (carte en bas à gauche, à droite ou centrée ; barre pleine largeur en bas ou en haut ; carte au centre, déconseillée car elle peut masquer le contenu), largeur de la carte, distance des bords, taille du texte (14 à 22 px, titres et boutons en proportion), police (celle du thème ou la police système), couleurs du fond, du texte et des boutons, arrondi. Sur cellulaire, les cartes passent en pleine largeur en bas. Une barre en haut repousse la page au lieu de la couvrir.

## Textes et langues

**Réglages → Témoins → Textes** affiche un bloc par langue active : celles de WPML, sinon de Polylang, sinon la langue du site. Un champ vide garde le texte par défaut, affiché en gris : français pour une langue `fr`, anglais pour les autres. Les textes de plusieurs lignes acceptent liens, gras et italique.

Les textes sont lus à l'affichage de la page, une fois la langue réglée par WPML : aucun mu-plugin ni String Translation n'est nécessaire, contrairement à Complianz.

## Accessibilité

Visée : WCAG 2.2 AA.

- La bannière est une région nommée (`role="region"`), imprimée au début de `<body>` (`wp_body_open`) pour être atteinte en premier au clavier. Elle n'est pas modale et ne vole pas le focus.
- Les préférences sont un `<dialog>` natif : focus piégé, Échap pour fermer, retour du focus au lien d'origine.
- Interrupteurs : de vraies cases (`role="switch"`) avec étiquette et description.
- Contrastes de 4,5:1 au moins avec les couleurs par défaut ; un avertissement s'affiche dans l'onglet Apparence sinon. Focus visible (contour de 3 px), cibles de 44 px.
- La bannière ne cache ni la fin de la page ni l'élément qui a le focus (WCAG 2.4.11) : `scroll-padding-bottom` et un espace de même hauteur en fin de page.
- Animations seulement si `prefers-reduced-motion` ne s'y oppose pas.

## Performance et cache

- JS sans dépendance, chargé en `defer` ; aucun jQuery ; aucun appel externe tant que le visiteur n'a pas accepté.
- Le HTML est le même pour tous les visiteurs : c'est le JavaScript qui lit le choix. Les pages restent dans le **cache de LiteSpeed** sans variation par témoin.
- Les scripts d'EM Consent portent `data-no-optimize`, `data-no-defer`, `data-no-minify`, `data-cfasync="false"` et `nowprocket` : LiteSpeed, WP Rocket et Cloudflare ne les retardent pas.

## API JavaScript

```js
emConsent.get('mesure');            // true si la catégorie est acceptée
emConsent.open();                   // ouvre les préférences
emConsent.on(function (choix) {});  // { mesure: true, marketing: false }
document.addEventListener('em-consent:change', function (e) { e.detail; });
```

Côté PHP, `em_consent_has( 'mesure' )` lit le témoin. Il n'est fiable que sur une page qui n'est pas mise en cache : préférez le blocage par attributs ou l'API JavaScript.

Le témoin `em_consent` (nécessaire, `SameSite=Lax`, `Secure` en HTTPS) contient la révision, les catégories, la date et un identifiant aléatoire, en prévision d'un éventuel registre des consentements. Aucune adresse IP n'est gardée.

## Hooks pour développeurs

| Filtre | Rôle |
| --- | --- |
| `em_consent_scripts` | Handles à bloquer : `handle => catégorie`. |
| `em_consent_iframe_domains` | Domaines des iframes bloquées : `domaine => catégorie`. |
| `em_consent_enabled` | Active ou non l'extension sur la page affichée. |
| `em_consent_config` | Configuration du JS (`consentMode` à `false` retire le mode de consentement ; `effacer` liste les préfixes de témoins effacés au retrait ; `traqueurs`, l’expression des envois retenus au retrait). |
| `em_consent_settings` | Réglages lus (par exemple imposer un identifiant GTM). |
| `em_consent_schema` | Schéma des réglages. |
| `em_consent_text_definitions` | Libellés et textes par défaut. |
| `em_consent_text` | Un texte affiché : `( $texte, $cle, $langue )`. |
| `em_consent_css_vars` | Variables CSS `--em-consent-*`. |
| `em_consent_services` | Catalogue des services et de leurs témoins (page de politique). |
| `em_consent_update_api` | Adresse de l'API des mises à jour (réponse au format des releases GitHub). |

## Limites connues

- Un script écrit en dur par une extension tierce, sans `wp_enqueue_script`, n'est pas bloqué : EM Consent ne réécrit pas la page entière (cela coûterait en performance et casserait le cache). Collez-le plutôt dans l'onglet Scripts et désactivez-le dans l'extension.
- Les iframes sont bloquées dans le contenu (`the_content`), pas dans les gabarits du thème ni les widgets : balisez-les vous-même.
- Un élément d'une catégorie non proposée reste bloqué pour toujours ; un avertissement le signale dans l'admin.
- Au retrait du consentement, une iframe déjà chargée avec l’accord (une vidéo YouTube, par exemple) peut encore signaler sa propre fermeture à son service : la page ne peut pas l’en empêcher.

## Développement

```bash
composer install            # WPCS 3
composer lint               # PHPCS, doit être à 0/0
npm install
npm test -- http://site.local/page-de-test/ http://site.local/en/test-page/
```

`tests/consentement.mjs` ouvre la page sans session dans Chromium et vérifie : aucun domaine tiers avant le choix, la bannière au clavier, le lien de gestion, GTM et GA4 seulement après l'accord, le retrait, les textes FR et EN, et axe-core (WCAG A/AA). La page doit contenir un lien `#em-consent`, et GTM et GA4 doivent être réglés.

**Publier une version** : mettre à jour `Version` et `EM_CONSENT_VERSION` dans `em-consent.php`, le badge de ce README et `CHANGELOG.md`, fusionner dans `main`, puis pousser l'étiquette `vX.Y.Z`. Le workflow `.github/workflows/release.yml` vérifie la version, construit `em-consent.zip` (sans les fichiers marqués `export-ignore`) et crée la release ; les sites la proposent à leur prochaine vérification.

Architecture :

```
em-consent.php                         en-tête, constantes, amorçage
includes/class-em-consent-settings.php schéma des réglages (source unique), nettoyage, contraste
includes/class-em-consent-texts.php    textes par langue (WPML, Polylang, langue du site)
includes/class-em-consent-front.php    <head>, bannière, préférences, code court
includes/class-em-consent-blocker.php  scripts et iframes bloqués
includes/class-em-consent-policy.php   catalogue des services, page de politique, création des pages
includes/class-em-consent-admin.php    Réglages → Témoins
includes/class-em-consent-updater.php  mises à jour depuis GitHub
assets/js/em-consent.js                bannière, choix, déblocage, API
assets/css/em-consent.css              styles, variables --em-consent-*
tests/consentement.mjs                 test navigateur (Playwright, axe-core)
```

## Licence

GPL v2 ou ultérieure. © Emsolutions.
