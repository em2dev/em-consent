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
			'titre'                 => array(
				'label' => __( 'Titre de la bannière', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Votre vie privée',
				'en'    => 'Your privacy',
			),
			'message'               => array(
				'label' => __( 'Message de la bannière', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Nous utilisons des témoins (cookies) nécessaires au fonctionnement du site. Avec votre accord, nous utilisons aussi des témoins de mesure d’audience et de marketing. Vous pouvez changer d’avis en tout temps avec le lien «' . $nbsp . 'Gestion des témoins' . $nbsp . '» au bas de chaque page.',
				'en'    => 'We use cookies that are necessary for this site to work. With your consent, we also use analytics and marketing cookies. You can change your mind at any time with the “Cookie settings” link at the bottom of every page.',
			),
			'politique_libelle'     => array(
				'label' => __( 'Lien vers la politique : texte (vide = titre de la page de politique, s’il y en a une)', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Politique de confidentialité',
				'en'    => 'Privacy policy',
			),
			'politique_url'         => array(
				'label' => __( 'Lien vers la politique : adresse (vide = page de politique des témoins, sinon page de confidentialité de WordPress)', 'em-consent' ),
				'type'  => 'url',
				'fr'    => '',
				'en'    => '',
			),
			'accepter'              => array(
				'label' => __( 'Bouton Accepter', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Tout accepter',
				'en'    => 'Accept all',
			),
			'refuser'               => array(
				'label' => __( 'Bouton Refuser', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Tout refuser',
				'en'    => 'Reject all',
			),
			'personnaliser'         => array(
				'label' => __( 'Bouton Personnaliser', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Personnaliser',
				'en'    => 'Customize',
			),
			'preferences_titre'     => array(
				'label' => __( 'Préférences : titre', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Préférences de témoins',
				'en'    => 'Cookie preferences',
			),
			'preferences_intro'     => array(
				'label' => __( 'Préférences : introduction', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Choisissez les témoins que vous acceptez. Les témoins nécessaires sont toujours actifs' . $nbsp . ': le site ne fonctionne pas sans eux.',
				'en'    => 'Choose which cookies you accept. Necessary cookies are always on: the site cannot work without them.',
			),
			'enregistrer'           => array(
				'label' => __( 'Préférences : bouton Enregistrer', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Enregistrer mes choix',
				'en'    => 'Save my choices',
			),
			'fermer'                => array(
				'label' => __( 'Préférences : bouton Fermer (lecteurs d’écran)', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Fermer',
				'en'    => 'Close',
			),
			'toujours_actifs'       => array(
				'label' => __( 'Préférences : mention « Toujours actifs »', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Toujours actifs',
				'en'    => 'Always on',
			),
			'necessaires_titre'     => array(
				'label' => __( 'Catégorie Nécessaires : nom', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Nécessaires',
				'en'    => 'Necessary',
			),
			'necessaires_desc'      => array(
				'label' => __( 'Catégorie Nécessaires : description', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Sécurité, mémorisation de vos choix de témoins et fonctions de base du site.',
				'en'    => 'Security, remembering your cookie choices and basic site features.',
			),
			'mesure_titre'          => array(
				'label' => __( 'Catégorie Mesure d’audience : nom', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Mesure d’audience',
				'en'    => 'Analytics',
			),
			'mesure_desc'           => array(
				'label' => __( 'Catégorie Mesure d’audience : description', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Statistiques de visite (par exemple Google' . $nbsp . 'Analytics) qui nous aident à améliorer le site.',
				'en'    => 'Visit statistics (for example Google Analytics) that help us improve the site.',
			),
			'marketing_titre'       => array(
				'label' => __( 'Catégorie Marketing : nom', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Marketing',
				'en'    => 'Marketing',
			),
			'marketing_desc'        => array(
				'label' => __( 'Catégorie Marketing : description', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Publicité ciblée, mesure des campagnes et contenus de tiers intégrés (vidéos, cartes, réseaux sociaux).',
				'en'    => 'Targeted advertising, campaign measurement and embedded third-party content (videos, maps, social media).',
			),
			'lien_gestion'          => array(
				'label' => __( 'Lien « Gestion des témoins » (code court)', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Gestion des témoins',
				'en'    => 'Cookie settings',
			),
			'bloque_message'        => array(
				'label' => __( 'Contenu bloqué : message', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Ce contenu est fourni par un service externe qui peut utiliser des témoins de marketing.',
				'en'    => 'This content is provided by an external service that may use marketing cookies.',
			),
			'bloque_bouton'         => array(
				'label' => __( 'Contenu bloqué : bouton', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Accepter les témoins marketing et afficher',
				'en'    => 'Accept marketing cookies and show',
			),
			'politique_titre_page'  => array(
				'label' => __( 'Titre de la page (à sa création)', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Politique relative aux témoins',
				'en'    => 'Cookie policy',
			),
			'politique_intro'       => array(
				'label' => __( 'Introduction', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Un témoin (cookie) est un petit fichier que votre navigateur enregistre pour un site. Ce site utilise des témoins nécessaires à son fonctionnement et, seulement avec votre accord, des témoins de mesure d’audience et de marketing. Avant votre accord, aucun témoin non nécessaire n’est déposé et aucun service externe n’est contacté.',
				'en'    => 'A cookie is a small file that your browser stores for a website. This site uses cookies that are necessary for it to work and, only with your consent, analytics and marketing cookies. Before you consent, no optional cookie is set and no external service is contacted.',
			),
			'col_temoin'            => array(
				'label' => __( 'Tableau : colonne Témoin', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Témoin',
				'en'    => 'Cookie',
			),
			'col_fournisseur'       => array(
				'label' => __( 'Tableau : colonne Fournisseur', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Fournisseur',
				'en'    => 'Provider',
			),
			'col_finalite'          => array(
				'label' => __( 'Tableau : colonne Finalité', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Finalité',
				'en'    => 'Purpose',
			),
			'col_duree'             => array(
				'label' => __( 'Tableau : colonne Durée', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Durée',
				'en'    => 'Duration',
			),
			'politique_aucun'       => array(
				'label' => __( 'Catégorie sans témoin', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Aucun témoin de cette catégorie n’est utilisé actuellement.',
				'en'    => 'No cookies of this category are currently used.',
			),
			'politique_choix_titre' => array(
				'label' => __( 'Intertitre « Votre choix »', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Votre choix',
				'en'    => 'Your choice',
			),
			'politique_choix'       => array(
				'label' => __( 'Texte « Votre choix » ({duree} = durée du choix)', 'em-consent' ),
				'type'  => 'html',
				'fr'    => 'Votre choix est conservé pendant {duree}. Vous pouvez le modifier ou retirer votre consentement en tout temps' . $nbsp . ': les témoins de la catégorie retirée sont alors effacés.',
				'en'    => 'Your choice is kept for {duree}. You can change it or withdraw your consent at any time: the cookies of the withdrawn category are then deleted.',
			),
			'politique_maj'         => array(
				'label' => __( 'Date de mise à jour ({date})', 'em-consent' ),
				'type'  => 'text',
				'fr'    => 'Dernière mise à jour' . $nbsp . ': {date}',
				'en'    => 'Last updated: {date}',
			),
		);

		// Groupes de l'onglet Textes, dans l'ordre d'affichage.
		$groups = array(
			'banniere'    => array( 'titre', 'message', 'politique_libelle', 'politique_url', 'accepter', 'refuser', 'personnaliser' ),
			'preferences' => array( 'preferences_titre', 'preferences_intro', 'enregistrer', 'fermer', 'toujours_actifs', 'necessaires_titre', 'necessaires_desc', 'mesure_titre', 'mesure_desc', 'marketing_titre', 'marketing_desc' ),
			'contenu'     => array( 'lien_gestion', 'bloque_message', 'bloque_bouton' ),
		);
		foreach ( $defs as $key => $def ) {
			$defs[ $key ]['groupe'] = 'politique';
			foreach ( $groups as $group => $keys ) {
				if ( in_array( $key, $keys, true ) ) {
					$defs[ $key ]['groupe'] = $group;
				}
			}
		}

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
	 * Lien de la bannière pour la langue de la page : l'adresse donnée dans les Textes, sinon la page
	 * « Politique relative aux témoins » créée par l'extension, sinon la page de confidentialité de
	 * WordPress (traduites par WPML si elles existent dans cette langue).
	 *
	 * @return array Adresse et texte du lien ; adresse vide s'il n'y a rien à lier.
	 */
	public static function policy_link() {
		$lang   = self::current_language();
		$textes = (array) EM_Consent_Settings::get( 'textes' );
		$custom = ! empty( $textes[ $lang ]['politique_libelle'] );
		$label  = self::get( 'politique_libelle', $lang );
		$url    = self::get( 'politique_url', $lang );
		if ( '' !== $url ) {
			return array( $url, $label );
		}

		$page_id = EM_Consent_Policy::page_id( $lang );
		if ( $page_id ) {
			return array( (string) get_permalink( $page_id ), $custom ? $label : get_the_title( $page_id ) );
		}

		$page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $page_id ) {
			$page_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
			if ( 'publish' === get_post_status( $page_id ) ) {
				return array( (string) get_permalink( $page_id ), $label );
			}
		}
		return array( '', $label );
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
