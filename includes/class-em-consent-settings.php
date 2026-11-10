<?php
/**
 * Réglages d'EM Consent : le schéma est la source unique. La page de réglages, le nettoyage, l'export
 * et les valeurs par défaut en sont tous dérivés.
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lecture, valeurs par défaut et nettoyage des réglages.
 */
class EM_Consent_Settings {

	/**
	 * Nom de l'option qui contient tous les réglages.
	 */
	const OPTION = 'em_consent_settings';

	/**
	 * Catégories facultatives, dans l'ordre d'affichage. Les témoins nécessaires sont toujours actifs.
	 */
	const CATEGORIES = array( 'mesure', 'marketing' );

	/**
	 * Réglages lus une fois par requête.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Schéma des réglages : onglet, type, valeur par défaut et bornes.
	 *
	 * Types : text (motif facultatif), bool, int (min, max), choice (choices), color, scripts.
	 *
	 * @return array
	 */
	public static function schema() {
		$schema = array(
			'gtm_id'               => array(
				'tab'     => 'general',
				'type'    => 'text',
				'default' => '',
				'pattern' => '/^GTM-[A-Z0-9]{4,12}$/',
			),
			'ga4_id'               => array(
				'tab'     => 'general',
				'type'    => 'text',
				'default' => '',
				'pattern' => '/^G-[A-Z0-9]{4,14}$/',
			),
			'cat_mesure'           => array(
				'tab'     => 'general',
				'type'    => 'bool',
				'default' => true,
			),
			'cat_marketing'        => array(
				'tab'     => 'general',
				'type'    => 'bool',
				'default' => true,
			),
			'duree'                => array(
				'tab'     => 'general',
				'type'    => 'int',
				'default' => 12,
				'min'     => 1,
				'max'     => 13,
			),
			'bloquer_iframes'      => array(
				'tab'     => 'general',
				'type'    => 'bool',
				'default' => true,
			),
			'position'             => array(
				'tab'     => 'apparence',
				'type'    => 'choice',
				'default' => 'carte',
				'choices' => array( 'carte', 'barre' ),
			),
			'couleur_fond'         => array(
				'tab'     => 'apparence',
				'type'    => 'color',
				'default' => '#ffffff',
			),
			'couleur_texte'        => array(
				'tab'     => 'apparence',
				'type'    => 'color',
				'default' => '#1f2328',
			),
			'couleur_bouton'       => array(
				'tab'     => 'apparence',
				'type'    => 'color',
				'default' => '#1f2328',
			),
			'couleur_bouton_texte' => array(
				'tab'     => 'apparence',
				'type'    => 'color',
				'default' => '#ffffff',
			),
			'arrondi'              => array(
				'tab'     => 'apparence',
				'type'    => 'int',
				'default' => 8,
				'min'     => 0,
				'max'     => 24,
			),
			'scripts_mesure'       => array(
				'tab'     => 'scripts',
				'type'    => 'scripts',
				'default' => '',
			),
			'scripts_marketing'    => array(
				'tab'     => 'scripts',
				'type'    => 'scripts',
				'default' => '',
			),
		);

		/**
		 * Filtre le schéma des réglages.
		 *
		 * @param array $schema Schéma.
		 */
		return apply_filters( 'em_consent_schema', $schema );
	}

	/**
	 * Valeurs par défaut de tous les réglages.
	 *
	 * @return array
	 */
	public static function defaults() {
		$defaults = array();
		foreach ( self::schema() as $key => $field ) {
			$defaults[ $key ] = $field['default'];
		}
		$defaults['textes']   = array();
		$defaults['revision'] = 1;
		return $defaults;
	}

	/**
	 * Tous les réglages, complétés par les valeurs par défaut.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
		}

		/**
		 * Filtre les réglages lus (par exemple pour imposer un identifiant GTM dans un thème).
		 *
		 * @param array $settings Réglages.
		 */
		return apply_filters( 'em_consent_settings', self::$cache );
	}

	/**
	 * Un réglage.
	 *
	 * @param string $key Clé du réglage.
	 * @return mixed
	 */
	public static function get( $key ) {
		$all = self::all();
		return $all[ $key ] ?? null;
	}

	/**
	 * Vide le cache après une écriture.
	 */
	public static function flush() {
		self::$cache = null;
	}

	/**
	 * Catégories facultatives utilisées sur ce site.
	 *
	 * @return string[]
	 */
	public static function active_categories() {
		$active = array();
		foreach ( self::CATEGORIES as $category ) {
			if ( self::get( 'cat_' . $category ) ) {
				$active[] = $category;
			}
		}
		return $active;
	}

	/**
	 * Nettoyage à l'enregistrement. Un formulaire d'onglet envoie « _tab » : seuls les champs de cet
	 * onglet sont lus (une case non cochée vaut faux) et les autres sont gardés. Sans « _tab » (import,
	 * écriture par l'extension), chaque clé présente est nettoyée et les clés absentes sont gardées.
	 *
	 * @param mixed $input Valeurs reçues.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$old    = array_merge( self::defaults(), (array) get_option( self::OPTION, array() ) );
		$input  = is_array( $input ) ? $input : array();
		$tab    = isset( $input['_tab'] ) ? sanitize_key( $input['_tab'] ) : '';
		$output = $old;

		foreach ( self::schema() as $key => $field ) {
			if ( '' !== $tab && $field['tab'] !== $tab ) {
				continue;
			}
			if ( ! array_key_exists( $key, $input ) ) {
				if ( '' !== $tab && 'bool' === $field['type'] ) {
					$output[ $key ] = false;
				}
				continue;
			}
			$output[ $key ] = self::sanitize_field( $input[ $key ], $field, $old[ $key ] );
		}

		if ( ( '' === $tab || 'textes' === $tab ) && isset( $input['textes'] ) && is_array( $input['textes'] ) ) {
			$output['textes'] = EM_Consent_Texts::sanitize( $input['textes'], (array) $old['textes'] );
		}

		if ( '' === $tab && isset( $input['revision'] ) ) {
			$output['revision'] = max( 1, absint( $input['revision'] ) );
		}

		// Les catégories ont changé : le choix des visiteurs ne vaut plus, on redemande.
		if ( '' !== $tab ) {
			foreach ( self::CATEGORIES as $category ) {
				if ( (bool) $old[ 'cat_' . $category ] !== (bool) $output[ 'cat_' . $category ] ) {
					$output['revision'] = absint( $old['revision'] ) + 1;
					break;
				}
			}
		}

		self::flush();
		return $output;
	}

	/**
	 * Nettoie une valeur selon son type.
	 *
	 * @param mixed $value Valeur reçue.
	 * @param array $field Définition du champ.
	 * @param mixed $old   Valeur actuelle, gardée si la nouvelle est refusée.
	 * @return mixed
	 */
	private static function sanitize_field( $value, $field, $old ) {
		switch ( $field['type'] ) {
			case 'bool':
				return (bool) $value;

			case 'int':
				$value = (int) $value;
				return min( $field['max'], max( $field['min'], $value ) );

			case 'choice':
				$value = sanitize_key( (string) $value );
				return in_array( $value, $field['choices'], true ) ? $value : $field['default'];

			case 'color':
				$value = sanitize_hex_color( (string) $value );
				return $value ? $value : $field['default'];

			case 'scripts':
				// Du JavaScript brut : réservé aux comptes autorisés à publier du HTML non filtré.
				if ( ! current_user_can( 'unfiltered_html' ) ) {
					return $old;
				}
				return EM_Consent_Blocker::keep_scripts_only( (string) $value );

			case 'text':
			default:
				$value = strtoupper( trim( sanitize_text_field( (string) $value ) ) );
				if ( '' !== $value && ! empty( $field['pattern'] ) && ! preg_match( $field['pattern'], $value ) ) {
					return $old;
				}
				return $value;
		}
	}

	/**
	 * Durée de conservation du choix, en jours.
	 *
	 * @return int
	 */
	public static function duration_days() {
		return (int) round( absint( self::get( 'duree' ) ) * 30.44 );
	}

	/**
	 * Rapport de contraste WCAG entre deux couleurs hexadécimales.
	 *
	 * @param string $a Couleur 1.
	 * @param string $b Couleur 2.
	 * @return float
	 */
	public static function contrast( $a, $b ) {
		$la = self::luminance( $a );
		$lb = self::luminance( $b );
		return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
	}

	/**
	 * Luminance relative d'une couleur hexadécimale.
	 *
	 * @param string $hex Couleur #rgb ou #rrggbb.
	 * @return float
	 */
	private static function luminance( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$rgb = array();
		foreach ( array( 0, 2, 4 ) as $i ) {
			$c     = hexdec( substr( $hex, $i, 2 ) ) / 255;
			$rgb[] = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		}
		return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
	}
}
