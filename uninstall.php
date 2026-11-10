<?php
/**
 * Désinstallation : retire les réglages et le cache des mises à jour. Le témoin em_consent des
 * visiteurs expire de lui-même.
 *
 * @package em-consent
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'em_consent_settings' );
delete_site_transient( 'em_consent_release' );
