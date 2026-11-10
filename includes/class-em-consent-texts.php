<?php
/**
 * Textes de la bannière, par langue. Chaque texte a une valeur par défaut en français et en anglais ;
 * le client peut la remplacer dans Réglages → Témoins → Textes, langue par langue. Les langues
 * viennent de WPML, sinon de Polylang, sinon de la langue du site. Les textes sont lus au moment
 * d'afficher la page, une fois la langue réglée : aucun correctif n'est nécessaire avec WPML.
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Textes par langue.
 */
class EM_Consent_Texts {

	/**
	 * Définition des textes : libellé dans l'admin, type et valeurs par défaut.
	 *
	 * Types : text (texte simple), html (liens, gras, italique), url.
	 *
	 * @return array
	 */
	public static function definitions() {
		$nbsp = "\u{00A0}";
		$defs = array(
			'titre'             => array(
				'label' => __( 'Titre de la bannière', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Votre vie privée',
				'en'    => 'Your privacy',
			),
			'message'           => array(
				'label' => __( 'Message de la bannière', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Nous utilisons des témoins (cookies) nécessaires au fonctionnement du site. Avec votre accord, nous utilisons aussi des témoins de mesure d’audience et de marketing. Vous pouvez changer d’avis en tout temps avec le lien «' . $nbsp . 'Gestion des témoins' . $nbsp . '» au bas de chaque page.',
				'en'    => 'We use cookies that are necessary for this site to work. With your consent, we also use analytics and marketing cookies. You can change your mind at any time with the “Cookie settings” link at the bottom of every page.',
			),
			'politique_libelle' => array(
				'label' => __( 'Lien vers la politique : texte', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Politique de confidentialité',
				'en'    => 'Privacy policy',
			),
			'politique_url'     => array(
				'label' => __( 'Lien vers la politique : adresse (vide = page de confidentialité de WordPress)', 'em-consent' ),
				'type'  => 'url',
				'fr'    => '',
				'en'    => '',
			),
			'accepter'          => array(
				'label' => __( 'Bouton Accepter', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Tout accepter',
				'en'    => 'Accept all',
			),
			'refuser'           => array(
				'label' => __( 'Bouton Refuser', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Tout refuser',
				'en'    => 'Reject all',
			),
			'personnaliser'     => array(
				'label' => __( 'Bouton Personnaliser', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Personnaliser',
				'en'    => 'Customize',
			),
			'preferences_titre' => array(
				'label' => __( 'Préférences : titre', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Préférences de témoins',
				'en'    => 'Cookie preferences',
			),
			'preferences_intro' => array(
				'label' => __( 'Préférences : introduction', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Choisissez les témoins que vous acceptez. Les témoins nécessaires sont toujours actifs' . $nbsp . ': le site ne fonctionne pas sans eux.',
				'en'    => 'Choose which cookies you accept. Necessary cookies are always on: the site cannot work without them.',
			),
			'enregistrer'       => array(
				'label' => __( 'Préférences : bouton Enregistrer', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Enregistrer mes choix',
				'en'    => 'Save my choices',
			),
			'fermer'            => array(
				'label' => __( 'Préférences : bouton Fermer (lecteurs d’écran)', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Fermer',
				'en'    => 'Close',
			),
			'toujours_actifs'   => array(
				'label' => __( 'Préférences : mention « Toujours actifs »', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Toujours actifs',
				'en'    => 'Always on',
			),
			'necessaires_titre' => array(
				'label' => __( 'Catégorie Nécessaires : nom', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Nécessaires',
				'en'    => 'Necessary',
			),
			'necessaires_desc'  => array(
				'label' => __( 'Catégorie Nécessaires : description', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Sécurité, mémorisation de vos choix de témoins et fonctions de base du site.',
				'en'    => 'Security, remembering your cookie choices and basic site features.',
			),
			'mesure_titre'      => array(
				'label' => __( 'Catégorie Mesure d’audience : nom', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Mesure d’audience',
				'en'    => 'Analytics',
			),
			'mesure_desc'       => array(
				'label' => __( 'Catégorie Mesure d’audience : description', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Statistiques de visite (par exemple Google' . $nbsp . 'Analytics) qui nous aident à améliorer le site.',
				'en'    => 'Visit statistics (for example Google Analytics) that help us improve the site.',
			),
			'marketing_titre'   => array(
				'label' => __( 'Catégorie Marketing : nom', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Marketing',
				'en'    => 'Marketing',
			),
			'marketing_desc'    => array(
				'label' => __( 'Catégorie Marketing : description', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Publicité ciblée, mesure des campagnes et contenus de tiers intégrés (vidéos, cartes, réseaux sociaux).',
				'en'    => 'Targeted advertising, campaign measurement and embedded third-party content (videos, maps, social media).',
			),
			'lien_gestion'      => array(
				'label' => __( 'Lien « Gestion des témoins » (code court)', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Gestion des témoins',
				'en'    => 'Cookie settings',
			),
			'bloque_message'    => array(
				'label' => __( 'Contenu bloqué : message', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Ce contenu est fourni par un service externe qui peut utiliser des témoins de marketing.',
				'en'    => 'This content is provided by an external service that may use marketing cookies.',
			),
			'bloque_bouton'     => array(
				'label' => __( 'Contenu bloqué : bouton', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Accepter les témoins marketing et afficher',
				'en'    => 'Accept marketing cookies and show',
			),
		);

		/**
		 * Filtre la définition des textes (libellés et valeurs par défaut).
		 *
		 * @param array $defs Définitions.
		 */
		return apply_filters( 'em_consent_text_definitions', $defs );
	}

	/**
	 * Langues du site : code => nom. WPML, sinon Polylang, sinon la langue de WordPress.
	 *
	 * @return array
	 */
	public static function languages() {
		$languages = array();

		$wpml = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
		if ( is_array( $wpml ) && $wpml ) {
			foreach ( $wpml as $code => $language ) {
				$languages[ sanitize_key( $code ) ] = isset( $language['native_name'] ) ? $language['native_name'] : $code;
			}
		} elseif ( function_exists( 'pll_languages_list' ) ) {
			$codes = pll_languages_list( array( 'fields' => 'slug' ) );
			$names = pll_languages_list( array( 'fields' => 'name' ) );
			foreach ( (array) $codes as $i => $code ) {
				$languages[ sanitize_key( $code ) ] = isset( $names[ $i ] ) ? $names[ $i ] : $code;
			}
		}

		if ( ! $languages ) {
			$code               = self::locale_code( get_locale() );
			$languages[ $code ] = 'fr' === $code ? 'Français' : strtoupper( $code );
		}

		return $languages;
	}

	/**
	 * Langue de la page affichée.
	 *
	 * @return string
	 */
	public static function current_language() {
		$code = apply_filters( 'wpml_current_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
		if ( ! $code && function_exists( 'pll_current_language' ) ) {
			$code = pll_current_language( 'slug' );
		}
		if ( ! $code || 'all' === $code ) {
			$code = self::locale_code( determine_locale() );
		}
		return sanitize_key( $code );
	}

	/**
	 * Code de langue à deux lettres d'une locale (fr_CA → fr).
	 *
	 * @param string $locale Locale WordPress.
	 * @return string
	 */
	private static function locale_code( $locale ) {
		return sanitize_key( substr( (string) $locale, 0, 2 ) );
	}

	/**
	 * Valeur par défaut d'un texte : le français pour une langue française, l'anglais sinon.
	 *
	 * @param string $key  Clé du texte.
	 * @param string $lang Code de langue.
	 * @return string
	 */
	public static function default_value( $key, $lang ) {
		$defs = self::definitions();
		if ( ! isset( $defs[ $key ] ) ) {
			return '';
		}
		return 'fr' === $lang ? $defs[ $key ]['fr'] : $defs[ $key ]['en'];
	}

	/**
	 * Texte affiché : la valeur du client pour cette langue, sinon la valeur par défaut.
	 *
	 * @param string      $key  Clé du texte.
	 * @param string|null $lang Code de langue (langue de la page par défaut).
	 * @return string
	 */
	public static function get( $key, $lang = null ) {
		$lang   = $lang ? $lang : self::current_language();
		$textes = (array) EM_Consent_Settings::get( 'textes' );
		$value  = isset( $textes[ $lang ][ $key ] ) ? (string) $textes[ $lang ][ $key ] : '';
		if ( '' === trim( $value ) ) {
			$value = self::default_value( $key, $lang );
		}

		/**
		 * Filtre un texte de la bannière.
		 *
		 * @param string $value Texte.
		 * @param string $key   Clé du texte.
		 * @param string $lang  Code de langue.
		 */
		return apply_filters( 'em_consent_text', $value, $key, $lang );
	}

	/**
	 * Adresse de la politique de confidentialité pour la langue de la page : le réglage, sinon la
	 * page de confidentialité de WordPress (traduite par WPML si elle existe dans cette langue).
	 *
	 * @return string
	 */
	public static function policy_url() {
		$url = self::get( 'politique_url' );
		if ( '' !== $url ) {
			return $url;
		}
		$page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $page_id ) {
			return '';
		}
		$page_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
		return 'publish' === get_post_status( $page_id ) ? (string) get_permalink( $page_id ) : '';
	}

	/**
	 * Balises permises dans les textes de type html.
	 *
	 * @return array
	 */
	public static function allowed_html() {
		return array(
			'a'      => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
			'strong' => array(),
			'em'     => array(),
			'br'     => array(),
		);
	}

	/**
	 * Nettoie les textes reçus de l'onglet Textes. Les langues absentes du formulaire sont gardées.
	 *
	 * @param array $input Textes reçus : langue => clé => valeur.
	 * @param array $old   Textes enregistrés.
	 * @return array
	 */
	public static function sanitize( $input, $old ) {
		$defs   = self::definitions();
		$output = $old;
		foreach ( $input as $lang => $values ) {
			$lang = sanitize_key( $lang );
			if ( '' === $lang || ! is_array( $values ) ) {
				continue;
			}
			$output[ $lang ] = array();
			foreach ( $defs as $key => $def ) {
				$value = isset( $values[ $key ] ) ? trim( (string) $values[ $key ] ) : '';
				if ( 'html' === $def['type'] ) {
					$value = wp_kses( $value, self::allowed_html() );
				} elseif ( 'url' === $def['type'] ) {
					$value = esc_url_raw( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
				// Un texte identique à la valeur par défaut n'est pas gardé : il suivra ses améliorations.
				if ( '' !== $value && self::default_value( $key, $lang ) !== $value ) {
					$output[ $lang ][ $key ] = $value;
				}
			}
			if ( ! $output[ $lang ] ) {
				unset( $output[ $lang ] );
			}
		}
		return $output;
	}
}
