<?php
/**
 * Plugin Name:       EM Consent
 * Plugin URI:        https://github.com/em2dev/em-consent
 * Description:       Consentement aux témoins conforme à la Loi 25 du Québec : bannière Accepter, Refuser et Personnaliser, mode de consentement de Google v2, Google Tag Manager et GA4 chargés seulement après l'accord, scripts et iframes bloqués jusqu'au consentement, page de politique des témoins générée, textes par langue.
 * Version:           1.1.0
 * Author:            Emsolutions
 * Author URI:        https://www.emsolutions.ca
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       em-consent
 * Domain Path:       /languages
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Update URI:        https://github.com/em2dev/em-consent
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EM_CONSENT_VERSION', '1.1.0' );
define( 'EM_CONSENT_FILE', __FILE__ );
define( 'EM_CONSENT_PATH', plugin_dir_path( __FILE__ ) );
define( 'EM_CONSENT_URL', plugin_dir_url( __FILE__ ) );

require_once EM_CONSENT_PATH . 'includes/class-em-consent-settings.php';
require_once EM_CONSENT_PATH . 'includes/class-em-consent-texts.php';
require_once EM_CONSENT_PATH . 'includes/class-em-consent-blocker.php';
require_once EM_CONSENT_PATH . 'includes/class-em-consent-policy.php';
require_once EM_CONSENT_PATH . 'includes/class-em-consent-front.php';
require_once EM_CONSENT_PATH . 'includes/class-em-consent-updater.php';

if ( is_admin() ) {
	require_once EM_CONSENT_PATH . 'includes/class-em-consent-admin.php';
}

/**
 * Version d'un fichier de assets/ pour le cache navigateur : la version de l'extension suivie de la
 * date de modification du fichier, pour qu'une retouche soit prise en compte sans changer de version.
 *
 * @param string $relative Chemin relatif au dossier de l'extension.
 * @return string
 */
function em_consent_asset_version( $relative ) {
	$mtime = @filemtime( EM_CONSENT_PATH . $relative ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- fichier absent : on garde la version seule.
	return $mtime ? EM_CONSENT_VERSION . '.' . $mtime : EM_CONSENT_VERSION;
}

/**
 * Chargement des traductions de l'interface d'administration.
 */
function em_consent_load_textdomain() {
	load_plugin_textdomain( 'em-consent', false, dirname( plugin_basename( EM_CONSENT_FILE ) ) . '/languages' );
}
add_action( 'init', 'em_consent_load_textdomain' );

/**
 * Activation : crée les réglages par défaut à la première installation. Une réactivation ou une mise
 * à jour ne touche jamais aux réglages existants.
 */
function em_consent_activate() {
	if ( false === get_option( EM_Consent_Settings::OPTION ) ) {
		add_option( EM_Consent_Settings::OPTION, EM_Consent_Settings::defaults() );
	}
}
register_activation_hook( __FILE__, 'em_consent_activate' );

EM_Consent_Blocker::init();
EM_Consent_Front::init();
EM_Consent_Policy::init();
EM_Consent_Updater::init();

if ( is_admin() ) {
	EM_Consent_Admin::init();
}

/**
 * Le visiteur a-t-il accepté une catégorie ? Côté serveur, cette réponse n'est fiable que sur une
 * page qui n'est pas mise en cache : préférez l'API JavaScript (emConsent.get()) ou le blocage par
 * attributs, qui fonctionnent avec le cache de page de LiteSpeed.
 *
 * @param string $category Catégorie : necessaires, mesure ou marketing.
 * @return bool
 */
function em_consent_has( $category ) {
	return EM_Consent_Front::visitor_has( $category );
}
