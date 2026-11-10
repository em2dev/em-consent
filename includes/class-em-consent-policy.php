<?php
/**
 * Page « Politique relative aux témoins » : code court [em_consent_politique], construit à l'affichage
 * à partir des réglages (catégories, services, durée du choix), dans la langue de la page. La page suit
 * donc les réglages sans être regénérée. Le bouton « Créer les pages » (onglet Politique) crée la page
 * dans chaque langue active, traductions WPML ou Polylang liées.
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalogue des services, page de politique et création des pages.
 */
class EM_Consent_Policy {

	/**
	 * Branche le code court.
	 */
	public static function init() {
		add_shortcode( 'em_consent_politique', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Catalogue des services connus et de leurs témoins. Noms et durées publiés par les éditeurs
	 * (relevé de 2026-11) : à vérifier dans la politique du service, qui peut changer.
	 *
	 * Durée : array( nombre, unité ), unité parmi session, minute, jour, mois, an.
	 *
	 * @return array
	 */
	public static function catalog() {
		$catalog = array(
			'gtm'         => array(
				'nom'             => 'Google Tag Manager',
				'fournisseur'     => 'Google',
				'categorie'       => 'mesure',
				'confidentialite' => 'https://policies.google.com/privacy',
				'temoins'         => array(),
				'note'            => array(
					'fr' => 'Aucun témoin propre : charge, après votre accord, les services ci-dessous.',
					'en' => 'No cookies of its own: loads the services below, after your consent.',
				),
			),
			'ga4'         => array(
				'nom'             => 'Google Analytics 4',
				'fournisseur'     => 'Google',
				'categorie'       => 'mesure',
				'confidentialite' => 'https://policies.google.com/privacy',
				'temoins'         => array(
					array( '_ga', array( 2, 'an' ), 'Distingue les visiteurs.', 'Distinguishes visitors.' ),
					array( '_ga_*', array( 2, 'an' ), 'Conserve l’état de la session.', 'Keeps the session state.' ),
				),
			),
			'google_ads'  => array(
				'nom'             => 'Google Ads',
				'fournisseur'     => 'Google',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://policies.google.com/privacy',
				'temoins'         => array(
					array( '_gcl_au', array( 3, 'mois' ), 'Mesure les conversions des campagnes publicitaires.', 'Measures ad campaign conversions.' ),
					array( 'IDE (doubleclick.net)', array( 13, 'mois' ), 'Publicité ciblée et mesure de son efficacité.', 'Targeted advertising and its effectiveness.' ),
				),
			),
			'meta'        => array(
				'nom'             => 'Meta Pixel',
				'fournisseur'     => 'Meta',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://www.facebook.com/privacy/policy/',
				'temoins'         => array(
					array( '_fbp', array( 3, 'mois' ), 'Distingue les visiteurs pour la publicité sur Facebook et Instagram.', 'Distinguishes visitors for Facebook and Instagram advertising.' ),
					array( '_fbc', array( 3, 'mois' ), 'Conserve le dernier clic sur une publicité.', 'Stores the last ad click.' ),
				),
			),
			'linkedin'    => array(
				'nom'             => 'LinkedIn Insight Tag',
				'fournisseur'     => 'LinkedIn',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://www.linkedin.com/legal/privacy-policy',
				'temoins'         => array(
					array( 'li_sugr', array( 3, 'mois' ), 'Associe la visite à un profil pour la publicité.', 'Links the visit to a profile for advertising.' ),
					array( 'bcookie', array( 1, 'an' ), 'Identifie le navigateur.', 'Identifies the browser.' ),
					array( 'lidc', array( 1, 'jour' ), 'Choix du centre de données.', 'Data center selection.' ),
					array( 'UserMatchHistory', array( 30, 'jour' ), 'Synchronisation des identifiants publicitaires.', 'Ad ID syncing.' ),
				),
			),
			'tiktok'      => array(
				'nom'             => 'TikTok Pixel',
				'fournisseur'     => 'TikTok',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://www.tiktok.com/legal/privacy-policy',
				'temoins'         => array(
					array( '_ttp', array( 13, 'mois' ), 'Mesure et ciblage des publicités.', 'Ad measurement and targeting.' ),
				),
			),
			'clarity'     => array(
				'nom'             => 'Microsoft Clarity',
				'fournisseur'     => 'Microsoft',
				'categorie'       => 'mesure',
				'confidentialite' => 'https://privacy.microsoft.com/privacystatement',
				'temoins'         => array(
					array( '_clck', array( 1, 'an' ), 'Distingue les visiteurs.', 'Distinguishes visitors.' ),
					array( '_clsk', array( 1, 'jour' ), 'Relie les pages vues d’une même visite.', 'Links page views of one visit.' ),
					array( 'CLID (clarity.ms)', array( 1, 'an' ), 'Distingue les visiteurs.', 'Distinguishes visitors.' ),
				),
			),
			'hotjar'      => array(
				'nom'             => 'Hotjar',
				'fournisseur'     => 'Hotjar',
				'categorie'       => 'mesure',
				'confidentialite' => 'https://www.hotjar.com/legal/policies/privacy/',
				'temoins'         => array(
					array( '_hjSessionUser_*', array( 1, 'an' ), 'Distingue les visiteurs.', 'Distinguishes visitors.' ),
					array( '_hjSession_*', array( 30, 'minute' ), 'Conserve les données de la visite.', 'Keeps the visit data.' ),
				),
			),
			'youtube'     => array(
				'nom'             => 'YouTube',
				'fournisseur'     => 'Google',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://policies.google.com/privacy',
				'temoins'         => array(
					array( 'YSC', array( 0, 'session' ), 'Compte les vues des vidéos intégrées.', 'Counts views of embedded videos.' ),
					array( 'VISITOR_INFO1_LIVE', array( 6, 'mois' ), 'Estime la bande passante et suit les préférences.', 'Estimates bandwidth and tracks preferences.' ),
				),
			),
			'vimeo'       => array(
				'nom'             => 'Vimeo',
				'fournisseur'     => 'Vimeo',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://vimeo.com/privacy',
				'temoins'         => array(
					array( 'vuid', array( 2, 'an' ), 'Distingue les visiteurs du lecteur vidéo.', 'Distinguishes video player visitors.' ),
				),
			),
			'google_maps' => array(
				'nom'             => 'Google Maps',
				'fournisseur'     => 'Google',
				'categorie'       => 'marketing',
				'confidentialite' => 'https://policies.google.com/privacy',
				'temoins'         => array(
					array( 'NID (google.com)', array( 6, 'mois' ), 'Préférences et publicité de Google.', 'Google preferences and advertising.' ),
				),
			),
		);

		/**
		 * Filtre le catalogue des services (ajout d'un service, durée corrigée…).
		 *
		 * @param array $catalog Clé => service.
		 */
		return apply_filters( 'em_consent_services', $catalog );
	}

	/**
	 * Services détectés à partir des réglages (identifiants GTM et GA4).
	 *
	 * @return string[]
	 */
	public static function detected_services() {
		$detected = array();
		if ( EM_Consent_Settings::get( 'gtm_id' ) ) {
			$detected[] = 'gtm';
		}
		if ( EM_Consent_Settings::get( 'ga4_id' ) ) {
			$detected[] = 'ga4';
		}
		return $detected;
	}

	/**
	 * Services utilisés : cochés dans l'onglet Politique, plus ceux détectés.
	 *
	 * @return string[]
	 */
	public static function services() {
		$catalog = self::catalog();
		$keys    = array_unique( array_merge( self::detected_services(), (array) EM_Consent_Settings::get( 'services' ) ) );
		return array_values(
			array_filter(
				$keys,
				function ( $key ) use ( $catalog ) {
					return isset( $catalog[ $key ] );
				}
			)
		);
	}

	/**
	 * Durée lisible, dans la langue voulue.
	 *
	 * @param array  $duree Nombre et unité.
	 * @param string $lang  Code de langue.
	 * @return string
	 */
	public static function duration( $duree, $lang ) {
		$n    = (int) $duree[0];
		$unit = (string) $duree[1];
		$fr   = 'fr' === $lang;
		switch ( $unit ) {
			case 'session':
				return $fr ? 'Fin de la session' : 'End of session';
			case 'minute':
				return $fr ? sprintf( '%d minutes', $n ) : sprintf( '%d minutes', $n );
			case 'jour':
				if ( $fr ) {
					return 1 === $n ? '1 jour' : sprintf( '%d jours', $n );
				}
				return 1 === $n ? '1 day' : sprintf( '%d days', $n );
			case 'mois':
				if ( $fr ) {
					return sprintf( '%d mois', $n );
				}
				return 1 === $n ? '1 month' : sprintf( '%d months', $n );
			case 'an':
			default:
				if ( $fr ) {
					return 1 === $n ? '1 an' : sprintf( '%d ans', $n );
				}
				return 1 === $n ? '1 year' : sprintf( '%d years', $n );
		}
	}

	/**
	 * Lignes du tableau d'une catégorie : témoin, fournisseur, finalité, durée.
	 *
	 * @param string $category Catégorie.
	 * @param string $lang     Code de langue.
	 * @return array Lignes, et notes des services sans témoin propre.
	 */
	private static function rows( $category, $lang ) {
		$rows  = array();
		$notes = array();
		if ( 'necessaires' === $category ) {
			$rows[] = array(
				'temoin'      => EM_Consent_Front::COOKIE,
				'fournisseur' => wp_parse_url( home_url(), PHP_URL_HOST ),
				'lien'        => '',
				'finalite'    => 'fr' === $lang ? 'Mémorise vos choix de témoins.' : 'Remembers your cookie choices.',
				'duree'       => self::duration( array( absint( EM_Consent_Settings::get( 'duree' ) ), 'mois' ), $lang ),
			);
		}
		$catalog = self::catalog();
		foreach ( self::services() as $key ) {
			$service = $catalog[ $key ];
			if ( $service['categorie'] !== $category ) {
				continue;
			}
			$provider = $service['nom'] . ( $service['fournisseur'] !== $service['nom'] ? ' (' . $service['fournisseur'] . ')' : '' );
			if ( empty( $service['temoins'] ) && ! empty( $service['note'] ) ) {
				$notes[] = array( $provider, $service['confidentialite'], 'fr' === $lang ? $service['note']['fr'] : $service['note']['en'] );
			}
			foreach ( $service['temoins'] as $cookie ) {
				$rows[] = array(
					'temoin'      => $cookie[0],
					'fournisseur' => $provider,
					'lien'        => $service['confidentialite'],
					'finalite'    => 'fr' === $lang ? $cookie[2] : $cookie[3],
					'duree'       => self::duration( $cookie[1], $lang ),
				);
			}
		}
		return array( $rows, $notes );
	}

	/**
	 * Code court [em_consent_politique] : contenu de la page « Politique relative aux témoins ».
	 *
	 * @param array|string $atts Attributs : niveau (niveau des intertitres, 2 par défaut).
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts       = shortcode_atts( array( 'niveau' => 2 ), $atts, 'em_consent_politique' );
		$level      = min( 6, max( 2, absint( $atts['niveau'] ) ) );
		$lang       = EM_Consent_Texts::current_language();
		$allowed    = EM_Consent_Texts::allowed_html();
		$categories = array_merge( array( 'necessaires' ), EM_Consent_Settings::active_categories() );
		$months     = self::duration( array( absint( EM_Consent_Settings::get( 'duree' ) ), 'mois' ), $lang );

		$html  = '<div class="em-consent-politique">';
		$html .= '<p>' . wp_kses( EM_Consent_Texts::get( 'politique_intro', $lang ), $allowed ) . '</p>';

		foreach ( $categories as $category ) {
			list( $rows, $notes ) = self::rows( $category, $lang );
			$title                = EM_Consent_Texts::get( $category . '_titre', $lang );
			$html                .= sprintf( '<h%1$d>%2$s</h%1$d>', $level, esc_html( $title ) );
			$html                .= '<p>' . wp_kses( EM_Consent_Texts::get( $category . '_desc', $lang ), $allowed ) . '</p>';
			foreach ( $notes as $note ) {
				$html .= sprintf( '<p><a href="%1$s">%2$s</a>%3$s %4$s</p>', esc_url( $note[1] ), esc_html( $note[0] ), 'fr' === $lang ? '&nbsp;:' : ':', esc_html( $note[2] ) );
			}
			if ( ! $rows ) {
				$html .= '<p>' . esc_html( EM_Consent_Texts::get( 'politique_aucun', $lang ) ) . '</p>';
				continue;
			}
			$html .= '<div class="em-consent-politique__tableau"><table>';
			$html .= '<caption>' . esc_html( $title ) . '</caption><thead><tr>';
			foreach ( array( 'col_temoin', 'col_fournisseur', 'col_finalite', 'col_duree' ) as $col ) {
				$html .= '<th scope="col">' . esc_html( EM_Consent_Texts::get( $col, $lang ) ) . '</th>';
			}
			$html .= '</tr></thead><tbody>';
			foreach ( $rows as $row ) {
				$provider = $row['lien'] ? sprintf( '<a href="%1$s">%2$s</a>', esc_url( $row['lien'] ), esc_html( $row['fournisseur'] ) ) : esc_html( $row['fournisseur'] );
				$html    .= sprintf(
					'<tr><th scope="row"><code>%1$s</code></th><td>%2$s</td><td>%3$s</td><td>%4$s</td></tr>',
					esc_html( $row['temoin'] ),
					$provider,
					esc_html( $row['finalite'] ),
					esc_html( $row['duree'] )
				);
			}
			$html .= '</tbody></table></div>';
		}

		$html .= sprintf( '<h%1$d>%2$s</h%1$d>', $level, esc_html( EM_Consent_Texts::get( 'politique_choix_titre', $lang ) ) );
		$html .= '<p>' . wp_kses( str_replace( '{duree}', esc_html( $months ), EM_Consent_Texts::get( 'politique_choix', $lang ) ), $allowed ) . '</p>';
		$html .= '<p>' . EM_Consent_Front::shortcode( array() ) . '</p>';

		$updated = (string) EM_Consent_Settings::get( 'politique_maj' );
		if ( $updated ) {
			$date  = wp_date( 'fr' === $lang ? 'j F Y' : 'F j, Y', strtotime( $updated . ' 12:00:00' ) );
			$html .= '<p class="em-consent-politique__maj">' . esc_html( str_replace( '{date}', $date, EM_Consent_Texts::get( 'politique_maj', $lang ) ) ) . '</p>';
		}

		return $html . '</div>';
	}

	/**
	 * Page de politique dans une langue (traduction WPML de la page enregistrée), ou 0.
	 *
	 * @param string|null $lang Code de langue (langue de la page par défaut).
	 * @return int
	 */
	public static function page_id( $lang = null ) {
		$page_id = absint( EM_Consent_Settings::get( 'page_politique' ) );
		if ( ! $page_id || ! get_post( $page_id ) ) {
			return 0;
		}
		$lang = $lang ? $lang : EM_Consent_Texts::current_language();
		if ( function_exists( 'pll_get_post' ) ) {
			$page_id = (int) pll_get_post( $page_id, $lang );
		} else {
			$page_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', false, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
		}
		return $page_id && 'publish' === get_post_status( $page_id ) ? $page_id : 0;
	}

	/**
	 * Crée la page de politique dans chaque langue active, sans rien recréer : une langue qui a déjà
	 * sa page est gardée. Les traductions sont liées (WPML ou Polylang).
	 *
	 * @return array Nombre de pages créées et gardées.
	 */
	public static function create_pages() {
		$languages = array_keys( EM_Consent_Texts::languages() );
		$default   = apply_filters( 'wpml_default_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
		if ( ! $default && function_exists( 'pll_default_language' ) ) {
			$default = pll_default_language( 'slug' );
		}
		$default = $default && in_array( $default, $languages, true ) ? $default : $languages[0];
		$ordered = array_unique( array_merge( array( $default ), $languages ) );
		$content = "<!-- wp:shortcode -->\n[em_consent_politique]\n<!-- /wp:shortcode -->";
		$source  = absint( EM_Consent_Settings::get( 'page_politique' ) );
		$source  = $source && get_post( $source ) && 'trash' !== get_post_status( $source ) ? $source : 0;
		$created = 0;
		$kept    = 0;
		$pll     = array();

		foreach ( $ordered as $lang ) {
			$existing = 0;
			if ( $source ) {
				$existing = function_exists( 'pll_get_post' )
					? (int) pll_get_post( $source, $lang )
					: (int) apply_filters( 'wpml_object_id', $source, 'page', false, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
				if ( ! $existing && ! self::multilingual() ) {
					$existing = $source;
				}
			}
			if ( $existing ) {
				$pll[ $lang ] = $existing;
				++$kept;
				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => EM_Consent_Texts::get( 'politique_titre_page', $lang ),
					'post_content' => $content,
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				continue;
			}
			++$created;
			$pll[ $lang ] = $page_id;

			if ( ! $source ) {
				$source   = $page_id;
				$settings = get_option( EM_Consent_Settings::OPTION, array() );
				update_option( EM_Consent_Settings::OPTION, array_merge( (array) $settings, array( 'page_politique' => $page_id ) ) );
			}

			// Langue de la page et lien avec la page d'origine (WPML).
			$trid = $source !== $page_id ? apply_filters( 'wpml_element_trid', null, $source, 'post_page' ) : null; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
			do_action(
				'wpml_set_element_language_details', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- API de WPML.
				array(
					'element_id'           => $page_id,
					'element_type'         => 'post_page',
					'trid'                 => $trid,
					'language_code'        => $lang,
					'source_language_code' => $trid ? $default : null,
				)
			);
			if ( function_exists( 'pll_set_post_language' ) ) {
				pll_set_post_language( $page_id, $lang );
			}
		}

		if ( function_exists( 'pll_save_post_translations' ) && count( $pll ) > 1 ) {
			pll_save_post_translations( $pll );
		}

		EM_Consent_Settings::flush();
		return array(
			'creees'  => $created,
			'gardees' => $kept,
		);
	}

	/**
	 * Le site a-t-il une extension multilingue ?
	 *
	 * @return bool
	 */
	private static function multilingual() {
		return defined( 'ICL_SITEPRESS_VERSION' ) || function_exists( 'pll_get_post' );
	}
}
