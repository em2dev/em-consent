# Journal des modifications

Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/), versions selon [SemVer](https://semver.org/lang/fr/).

## [1.0.0] — 2026-11-10

Première version.

### Ajouté

- Bannière Accepter, Refuser et Personnaliser dès le premier écran, boutons de même poids ; carte en bas à gauche ou barre pleine largeur, sans voile.
- Préférences dans un `<dialog>` natif : nécessaires (toujours actifs), mesure d'audience et marketing, désactivées par défaut.
- Mode de consentement de Google v2 refusé par défaut, dès le premier élément de `<head>` ; `ads_data_redaction` tant que le marketing est refusé.
- Google Tag Manager et GA4 chargés seulement après l'accord (mode de base : aucun domaine tiers avant le consentement) ; événement `em_consent_update` dans le dataLayer.
- Blocage des scripts : filtre `em_consent_scripts` (handles enregistrés, avec leurs scripts en ligne et traductions), balisage `type="text/plain" data-em-consent="…"`, scripts personnalisés par catégorie (balises `<script>` seulement).
- Blocage des iframes de services tiers dans le contenu (YouTube, Vimeo, Google Maps, Calendly, réseaux sociaux…), avec avis et bouton d'autorisation ; filtre `em_consent_iframe_domains`.
- Lien « Gestion des témoins » : tout lien vers `#em-consent` et le code court `[em_consent_gestion]` rouvrent les préférences.
- Choix gardé dans le témoin nécessaire `em_consent` (12 mois par défaut, de 1 à 13) ; consentement redemandé quand les catégories changent ou d'un clic.
- Retrait du consentement : témoins de la catégorie effacés et page rechargée.
- Textes par langue (WPML, Polylang ou langue du site), français et anglais par défaut, modifiables dans l'admin.
- Page Réglages → Témoins : Général, Textes, Apparence (couleurs, arrondi, avertissement de contraste), Scripts, Outils (redemander, export, import, réinitialisation).
- API JavaScript `emConsent` et événement `em-consent:change` ; fonction PHP `em_consent_has()`.
- Mises à jour depuis les releases GitHub (`Update URI`, filtre `update_plugins_github.com`) et workflow de publication.
- Test navigateur `tests/consentement.mjs` (Playwright, axe-core) ; PHPCS (WPCS 3).
