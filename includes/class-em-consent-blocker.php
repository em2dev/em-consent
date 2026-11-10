<?php
/**
 * Blocage jusqu'au consentement. Un élément bloqué porte data-em-consent="<catégorie>" :
 * - un script : type="text/plain", que le navigateur n'exécute pas et dont il ne télécharge pas la source ;
 * - une iframe : adresse rangée dans data-src, avec un avis et un bouton pour l'autoriser.
 * Le JavaScript de l'extension réactive ces éléments quand la catégorie est acceptée.
 *
 * Trois façons de bloquer un script :
 * 1. le filtre em_consent_scripts (handle => catégorie) pour les scripts enregistrés avec wp_enqueue_script ;
 * 2. le balisage manuel <script type="text/plain" data-em-consent="mesure"> dans un gabarit ;
 * 3. les scripts personnalisés de Réglages → Témoins → Scripts.
 *
 * Aucun tampon de sortie sur la page entière : un script écrit en dur par une extension tierce, sans
 * wp_enqueue_script, n'est pas bloqué (voir le README).
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocage des scripts et des iframes.
 */
class EM_Consent_Blocker {

	/**
	 * Branche les filtres.
	 */
	public static function init() {
		add_filter( 'script_loader_tag', array( __CLASS__, 'filter_script_tag' ), 20, 2 );
		add_filter( 'the_content', array( __CLASS__, 'filter_iframes' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_custom_scripts' ), 20 );
	}

	/**
	 * Scripts déclarés par les thèmes et les extensions : handle => catégorie.
	 *
	 * @return array
	 */
	public static function declared_scripts() {
		/**
		 * Déclare des scripts à bloquer jusqu'au consentement.
		 *
		 * Exemple :
		 *
		 *     add_filter( 'em_consent_scripts', function ( $scripts ) {
		 *         $scripts['mon-pixel'] = 'marketing';
		 *         return $scripts;
		 *     } );
		 *
		 * Déclarez aussi les scripts qui dépendent d'un script bloqué : ils échoueraient sans lui.
		 *
		 * @param array $scripts Handle => catégorie (mesure ou marketing).
		 */
		$scripts = apply_filters( 'em_consent_scripts', array() );
		$clean   = array();
		foreach ( (array) $scripts as $handle => $category ) {
			$category = sanitize_key( (string) $category );
			if ( in_array( $category, EM_Consent_Settings::CATEGORIES, true ) ) {
				$clean[ (string) $handle ] = $category;
			}
		}
		return $clean;
	}

	/**
	 * Bloque un script enregistré, avec ses scripts en ligne et ses traductions.
	 *
	 * @param string $tag    Balises du script.
	 * @param string $handle Handle du script.
	 * @return string
	 */
	public static function filter_script_tag( $tag, $handle ) {
		if ( is_admin() || ! EM_Consent_Front::is_enabled() ) {
			return $tag;
		}
		$declared = self::declared_scripts();
		if ( ! isset( $declared[ $handle ] ) ) {
			return $tag;
		}
		return self::block_scripts( $tag, $declared[ $handle ] );
	}

	/**
	 * Passe chaque <script> exécutable d'un fragment HTML en type="text/plain", lié à une catégorie.
	 * Les scripts de données (JSON, JSON-LD) ne sont pas touchés.
	 *
	 * @param string $html     Fragment HTML.
	 * @param string $category Catégorie.
	 * @return string
	 */
	public static function block_scripts( $html, $category ) {
		$processor = new WP_HTML_Tag_Processor( $html );
		while ( $processor->next_tag( array( 'tag_name' => 'SCRIPT' ) ) ) {
			$type = $processor->get_attribute( 'type' );
			$type = is_string( $type ) ? strtolower( trim( $type ) ) : '';
			if ( ! in_array( $type, array( '', 'text/javascript', 'application/javascript', 'module' ), true ) ) {
				continue;
			}
			if ( 'module' === $type ) {
				$processor->set_attribute( 'data-em-consent-type', 'module' );
			}
			$processor->set_attribute( 'type', 'text/plain' );
			$processor->set_attribute( 'data-em-consent', $category );
		}
		return $processor->get_updated_html();
	}

	/**
	 * Ne garde que les éléments <script> d'un fragment : un <noscript>, une image ou une iframe
	 * (pixel de suivi sans JavaScript) contacterait un domaine tiers avant le consentement.
	 *
	 * @param string $html Fragment HTML saisi dans les réglages.
	 * @return string
	 */
	public static function keep_scripts_only( $html ) {
		if ( ! preg_match_all( '#<script\b[^>]*>.*?</script\s*>#is', $html, $matches ) ) {
			return '';
		}
		return implode( "\n", array_map( 'trim', $matches[0] ) );
	}

	/**
	 * Imprime les scripts personnalisés des catégories utilisées, bloqués jusqu'au consentement.
	 */
	public static function print_custom_scripts() {
		if ( ! EM_Consent_Front::is_enabled() ) {
			return;
		}
		foreach ( EM_Consent_Settings::active_categories() as $category ) {
			$scripts = (string) EM_Consent_Settings::get( 'scripts_' . $category );
			if ( '' !== $scripts ) {
				// Saisi par un compte autorisé à publier du HTML non filtré, réduit à des <script> bloqués.
				echo self::block_scripts( $scripts, $category ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Domaines dont les iframes sont bloquées : domaine => catégorie. Un domaine couvre ses
	 * sous-domaines (youtube.com couvre www.youtube.com).
	 *
	 * @return array
	 */
	public static function iframe_domains() {
		$domains = array(
			'youtube.com'           => 'marketing',
			'youtube-nocookie.com'  => 'marketing',
			'youtu.be'              => 'marketing',
			'vimeo.com'             => 'marketing',
			'google.com'            => 'marketing',
			'google.ca'             => 'marketing',
			'calendly.com'          => 'marketing',
			'facebook.com'          => 'marketing',
			'instagram.com'         => 'marketing',
			'twitter.com'           => 'marketing',
			'x.com'                 => 'marketing',
			'tiktok.com'            => 'marketing',
			'linkedin.com'          => 'marketing',
			'spotify.com'           => 'marketing',
			'soundcloud.com'        => 'marketing',
			'dailymotion.com'       => 'marketing',
			'pinterest.com'         => 'marketing',
			'typeform.com'          => 'marketing',
			'googletagmanager.com'  => 'marketing',
			'doubleclick.net'       => 'marketing',
			'player.twitch.tv'      => 'marketing',
			'embed.music.apple.com' => 'marketing',
		);

		/**
		 * Filtre les domaines dont les iframes sont bloquées jusqu'au consentement.
		 *
		 * @param array $domains Domaine => catégorie.
		 */
		return apply_filters( 'em_consent_iframe_domains', $domains );
	}

	/**
	 * Catégorie d'une adresse d'iframe, ou chaîne vide si elle n'est pas bloquée.
	 *
	 * @param string $src Adresse.
	 * @return string
	 */
	public static function iframe_category( $src ) {
		$host = strtolower( (string) wp_parse_url( $src, PHP_URL_HOST ) );
		if ( '' === $host ) {
			return '';
		}
		foreach ( self::iframe_domains() as $domain => $category ) {
			$domain = strtolower( $domain );
			if ( $host === $domain || substr( $host, -strlen( $domain ) - 1 ) === '.' . $domain ) {
				return sanitize_key( $category );
			}
		}
		return '';
	}

	/**
	 * Bloque les iframes de services tiers dans le contenu (vidéos, cartes, réseaux sociaux).
	 * Seules les catégories utilisées sur le site sont bloquées.
	 *
	 * @param string $content Contenu.
	 * @return string
	 */
	public static function filter_iframes( $content ) {
		if ( ! EM_Consent_Front::is_enabled() || ! EM_Consent_Settings::get( 'bloquer_iframes' ) || false === stripos( $content, '<iframe' ) ) {
			return $content;
		}
		return preg_replace_callback( '#<iframe\b[^>]*>.*?</iframe\s*>#is', array( __CLASS__, 'block_iframe' ), $content );
	}

	/**
	 * Remplace une iframe par sa version bloquée, précédée d'un avis.
	 *
	 * @param array $matches Iframe trouvée.
	 * @return string
	 */
	private static function block_iframe( $matches ) {
		$processor = new WP_HTML_Tag_Processor( $matches[0] );
		if ( ! $processor->next_tag( 'iframe' ) || null !== $processor->get_attribute( 'data-em-consent' ) ) {
			return $matches[0];
		}
		$src      = (string) $processor->get_attribute( 'src' );
		$category = self::iframe_category( $src );
		if ( '' === $category || ! in_array( $category, EM_Consent_Settings::active_categories(), true ) ) {
			return $matches[0];
		}

		$processor->remove_attribute( 'src' );
		$processor->set_attribute( 'data-src', $src );
		$processor->set_attribute( 'data-em-consent', $category );
		$processor->set_attribute( 'hidden', true );

		$width  = absint( $processor->get_attribute( 'width' ) );
		$height = absint( $processor->get_attribute( 'height' ) );
		$style  = $width && $height ? sprintf( ' style="aspect-ratio:%d/%d"', $width, $height ) : '';

		return sprintf(
			'<div class="em-consent-bloque" data-em-consent-bloque="%1$s"%2$s><div class="em-consent-bloque__avis"><p>%3$s</p><button type="button" class="em-consent-bloque__bouton" data-em-consent-autoriser="%1$s">%4$s</button></div>%5$s</div>',
			esc_attr( $category ),
			$style,
			wp_kses( EM_Consent_Texts::get( 'bloque_message' ), EM_Consent_Texts::allowed_html() ),
			esc_html( EM_Consent_Texts::get( 'bloque_bouton' ) ),
			$processor->get_updated_html()
		);
	}
}
