<?php
/**
 * Mises à jour depuis les releases GitHub, par le mécanisme natif de WordPress : l'en-tête
 * « Update URI: https://github.com/em2dev/em-consent » de l'extension active le filtre
 * update_plugins_github.com. Le paquet est l'archive em-consent.zip jointe à chaque release par
 * .github/workflows/release.yml (jamais le zipball de GitHub, dont le dossier porte un autre nom).
 *
 * L'appel à l'API de GitHub se fait seulement quand WordPress vérifie les mises à jour (tâche
 * planifiée ou écran des mises à jour), jamais pendant l'affichage d'une page, et au plus deux fois
 * par jour grâce au cache.
 * Pour servir les mises à jour d'ailleurs plus tard (serveur emsolutions.ca), filtrer
 * em_consent_update_api : l'adresse doit répondre le même JSON que l'API des releases de GitHub.
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mises à jour.
 */
class EM_Consent_Updater {

	/**
	 * Dépôt GitHub.
	 */
	const REPO = 'em2dev/em-consent';

	/**
	 * Transitoire qui garde la dernière release lue.
	 */
	const TRANSIENT = 'em_consent_release';

	/**
	 * Nom de l'archive jointe aux releases.
	 */
	const ASSET = 'em-consent.zip';

	/**
	 * Branche les filtres.
	 */
	public static function init() {
		add_filter( 'update_plugins_github.com', array( __CLASS__, 'check' ), 10, 3 );
		add_filter( 'plugins_api', array( __CLASS__, 'details' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush' ) );
	}

	/**
	 * Dernière release : version, adresse du paquet, notes. Mise en cache 12 h (1 h après un échec).
	 *
	 * @return array|null
	 */
	public static function release() {
		$force = is_admin() && isset( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- « Vérifier à nouveau » de WordPress.
		$cache = get_site_transient( self::TRANSIENT );
		if ( ! $force && is_array( $cache ) ) {
			return $cache['release'];
		}

		/**
		 * Adresse de l'API qui décrit la dernière release.
		 *
		 * @param string $url Adresse.
		 */
		$url      = apply_filters( 'em_consent_update_api', 'https://api.github.com/repos/' . self::REPO . '/releases/latest' );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'em-consent/' . EM_CONSENT_VERSION,
				),
			)
		);

		$release = null;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( is_array( $data ) && ! empty( $data['tag_name'] ) && empty( $data['draft'] ) && empty( $data['prerelease'] ) ) {
				$package = '';
				foreach ( (array) ( $data['assets'] ?? array() ) as $asset ) {
					if ( isset( $asset['name'], $asset['browser_download_url'] ) && self::ASSET === $asset['name'] ) {
						$package = (string) $asset['browser_download_url'];
					}
				}
				if ( $package ) {
					$release = array(
						'version' => ltrim( (string) $data['tag_name'], 'vV' ),
						'package' => $package,
						'url'     => (string) ( $data['html_url'] ?? 'https://github.com/' . self::REPO ),
						'notes'   => (string) ( $data['body'] ?? '' ),
						'date'    => (string) ( $data['published_at'] ?? '' ),
					);
				}
			}
		}

		set_site_transient( self::TRANSIENT, array( 'release' => $release ), $release ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
		return $release;
	}

	/**
	 * Répond à WordPress quand il vérifie les mises à jour de l'extension.
	 *
	 * @param array|false $update      Mise à jour trouvée par un autre filtre.
	 * @param array       $plugin_data En-tête de l'extension.
	 * @param string      $plugin_file Fichier de l'extension.
	 * @return array|false
	 */
	public static function check( $update, $plugin_data, $plugin_file ) {
		if ( plugin_basename( EM_CONSENT_FILE ) !== $plugin_file ) {
			return $update;
		}
		$release = self::release();
		if ( ! $release ) {
			return $update;
		}
		return array(
			'id'           => 'https://github.com/' . self::REPO,
			'slug'         => 'em-consent',
			'plugin'       => $plugin_file,
			'version'      => $release['version'],
			'new_version'  => $release['version'],
			'url'          => $release['url'],
			'package'      => version_compare( $release['version'], EM_CONSENT_VERSION, '>' ) ? $release['package'] : '',
			'requires_php' => $plugin_data['RequiresPHP'] ?? '',
			'requires'     => $plugin_data['RequiresWP'] ?? '',
		);
	}

	/**
	 * Fenêtre « Voir les détails » de la liste des extensions.
	 *
	 * @param false|object|array $result Résultat.
	 * @param string             $action Action demandée.
	 * @param object             $args   Arguments.
	 * @return false|object|array
	 */
	public static function details( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'em-consent' !== $args->slug ) {
			return $result;
		}
		$release = self::release();
		$version = $release ? $release['version'] : EM_CONSENT_VERSION;
		return (object) array(
			'name'          => 'EM Consent',
			'slug'          => 'em-consent',
			'version'       => $version,
			'author'        => '<a href="https://www.emsolutions.ca">Emsolutions</a>',
			'homepage'      => 'https://github.com/' . self::REPO,
			'requires'      => '6.5',
			'requires_php'  => '7.4',
			'last_updated'  => $release ? $release['date'] : '',
			'download_link' => $release ? $release['package'] : '',
			'sections'      => array(
				'description' => esc_html__( 'Consentement aux témoins conforme à la Loi 25 du Québec.', 'em-consent' ),
				'changelog'   => $release && $release['notes'] ? wpautop( esc_html( $release['notes'] ) ) : esc_html__( 'Voir CHANGELOG.md dans le dépôt.', 'em-consent' ),
			),
		);
	}

	/**
	 * Oublie la release en cache après une mise à jour.
	 */
	public static function flush() {
		delete_site_transient( self::TRANSIENT );
	}
}
