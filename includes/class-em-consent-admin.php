<?php
/**
 * Page Réglages → Témoins : onglets Général, Textes, Apparence, Scripts et Outils.
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Administration.
 */
class EM_Consent_Admin {

	/**
	 * Identifiant de la page.
	 */
	const PAGE = 'em-consent';

	/**
	 * Branche les crochets.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_post_em_consent_outil', array( __CLASS__, 'tool' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( EM_CONSENT_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Onglets : clé => titre.
	 *
	 * @return array
	 */
	private static function tabs() {
		return array(
			'general'   => __( 'Général', 'em-consent' ),
			'textes'    => __( 'Textes', 'em-consent' ),
			'apparence' => __( 'Apparence', 'em-consent' ),
			'scripts'   => __( 'Scripts', 'em-consent' ),
			'outils'    => __( 'Outils', 'em-consent' ),
		);
	}

	/**
	 * Libellés et descriptions des réglages du schéma.
	 *
	 * @return array
	 */
	private static function fields() {
		return array(
			'gtm_id'               => array(
				__( 'Google Tag Manager', 'em-consent' ),
				__( 'Identifiant du conteneur (GTM-…). Chargé seulement après l’accord à une catégorie. Dans GTM, exigez le consentement voulu (analytics_storage, ad_storage) sur chaque balise.', 'em-consent' ),
			),
			'ga4_id'               => array(
				__( 'Google Analytics 4', 'em-consent' ),
				__( 'Identifiant de mesure (G-…), si GA4 n’est pas déjà dans le conteneur GTM. Chargé seulement après l’accord à la mesure d’audience.', 'em-consent' ),
			),
			'cat_mesure'           => array(
				__( 'Catégorie Mesure d’audience', 'em-consent' ),
				__( 'Proposée dans la bannière.', 'em-consent' ),
			),
			'cat_marketing'        => array(
				__( 'Catégorie Marketing', 'em-consent' ),
				__( 'Proposée dans la bannière. Sans elle, les iframes de services tiers ne sont pas bloquées.', 'em-consent' ),
			),
			'duree'                => array(
				__( 'Durée du choix (mois)', 'em-consent' ),
				__( 'Entre 1 et 13 mois. Ajouter ou retirer une catégorie redemande le consentement à tous.', 'em-consent' ),
			),
			'bloquer_iframes'      => array(
				__( 'Bloquer les iframes de services tiers', 'em-consent' ),
				__( 'YouTube, Vimeo, Google Maps, Calendly, réseaux sociaux… dans le contenu des pages, remplacés par un avis jusqu’au consentement.', 'em-consent' ),
			),
			'position'             => array(
				__( 'Position', 'em-consent' ),
				'',
			),
			'couleur_fond'         => array( __( 'Fond', 'em-consent' ), '' ),
			'couleur_texte'        => array( __( 'Texte', 'em-consent' ), '' ),
			'couleur_bouton'       => array( __( 'Boutons : fond', 'em-consent' ), '' ),
			'couleur_bouton_texte' => array( __( 'Boutons : texte', 'em-consent' ), '' ),
			'arrondi'              => array( __( 'Arrondi des coins (px)', 'em-consent' ), '' ),
			'scripts_mesure'       => array(
				__( 'Scripts de mesure d’audience', 'em-consent' ),
				__( 'Balises <script> seulement (Clarity, Hotjar…). Tout le reste, dont <noscript>, est retiré : il contacterait un tiers avant le consentement.', 'em-consent' ),
			),
			'scripts_marketing'    => array(
				__( 'Scripts de marketing', 'em-consent' ),
				__( 'Balises <script> seulement (Meta Pixel, LinkedIn…). Tout le reste, dont <noscript>, est retiré.', 'em-consent' ),
			),
		);
	}

	/**
	 * Ajoute la page sous Réglages.
	 */
	public static function menu() {
		add_options_page(
			__( 'EM Consent : témoins', 'em-consent' ),
			__( 'Témoins', 'em-consent' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Enregistre l'option et son nettoyage.
	 */
	public static function register() {
		register_setting(
			'em_consent',
			EM_Consent_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'EM_Consent_Settings', 'sanitize' ),
				'default'           => EM_Consent_Settings::defaults(),
			)
		);
	}

	/**
	 * Lien « Réglages » dans la liste des extensions.
	 *
	 * @param array $links Liens.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ), esc_html__( 'Réglages', 'em-consent' ) ) );
		return $links;
	}

	/**
	 * Onglet affiché.
	 *
	 * @return string
	 */
	private static function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- navigation seulement.
		return array_key_exists( $tab, self::tabs() ) ? $tab : 'general';
	}

	/**
	 * Affiche la page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$tab = self::current_tab();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'EM Consent : témoins', 'em-consent' ); ?></h1>
			<?php self::notices( $tab ); ?>
			<nav class="nav-tab-wrapper">
				<?php foreach ( self::tabs() as $key => $title ) : ?>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . self::PAGE . '&tab=' . $key ) ); ?>" class="nav-tab<?php echo $key === $tab ? ' nav-tab-active' : ''; ?>"<?php echo $key === $tab ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $title ); ?></a>
				<?php endforeach; ?>
			</nav>
			<?php
			if ( 'outils' === $tab ) {
				self::render_tools();
			} else {
				self::render_form( $tab );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Avis en tête de page : résultat d'un outil, réglages incohérents, contrastes.
	 *
	 * @param string $tab Onglet affiché.
	 */
	private static function notices( $tab ) {
		$messages = array(
			'redemande' => __( 'Le consentement sera redemandé à tous les visiteurs.', 'em-consent' ),
			'importe'   => __( 'Réglages importés.', 'em-consent' ),
			'invalide'  => __( 'Import refusé : le JSON n’est pas valide.', 'em-consent' ),
			'reinit'    => __( 'Réglages réinitialisés.', 'em-consent' ),
		);
		$msg      = isset( $_GET['em_consent_msg'] ) ? sanitize_key( wp_unslash( $_GET['em_consent_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- affichage d'un avis seulement.
		if ( isset( $messages[ $msg ] ) ) {
			printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', 'invalide' === $msg ? 'error' : 'success', esc_html( $messages[ $msg ] ) );
		}

		$warnings = array();
		if ( ! EM_Consent_Settings::active_categories() ) {
			$warnings[] = __( 'Aucune catégorie n’est proposée : la bannière ne s’affiche pas.', 'em-consent' );
		}
		if ( EM_Consent_Settings::get( 'gtm_id' ) && EM_Consent_Settings::get( 'ga4_id' ) ) {
			$warnings[] = __( 'GTM et GA4 sont tous deux remplis : si GA4 est aussi dans le conteneur GTM, les visites seront comptées deux fois.', 'em-consent' );
		}
		if ( 'apparence' === $tab ) {
			$pairs = array(
				array( 'couleur_texte', 'couleur_fond', __( 'texte sur fond', 'em-consent' ) ),
				array( 'couleur_bouton_texte', 'couleur_bouton', __( 'texte des boutons', 'em-consent' ) ),
			);
			foreach ( $pairs as $pair ) {
				$ratio = EM_Consent_Settings::contrast( EM_Consent_Settings::get( $pair[0] ), EM_Consent_Settings::get( $pair[1] ) );
				if ( $ratio < 4.5 ) {
					/* translators: 1: paire de couleurs, 2: rapport de contraste. */
					$warnings[] = sprintf( __( 'Contraste insuffisant (%1$s) : %2$s:1, il faut au moins 4,5:1 (WCAG AA).', 'em-consent' ), $pair[2], number_format_i18n( $ratio, 2 ) );
				}
			}
		}
		$inactive = array_diff( array_unique( array_values( EM_Consent_Blocker::declared_scripts() ) ), EM_Consent_Settings::active_categories() );
		if ( $inactive ) {
			/* translators: %s: catégories. */
			$warnings[] = sprintf( __( 'Des scripts sont déclarés dans une catégorie non proposée (%s) : ils ne s’exécuteront jamais.', 'em-consent' ), implode( ', ', $inactive ) );
		}
		foreach ( $warnings as $warning ) {
			printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( $warning ) );
		}
	}

	/**
	 * Formulaire d'un onglet de réglages.
	 *
	 * @param string $tab Onglet.
	 */
	private static function render_form( $tab ) {
		$name = EM_Consent_Settings::OPTION;
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'em_consent' ); ?>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[_tab]" value="<?php echo esc_attr( $tab ); ?>">
			<?php
			if ( 'textes' === $tab ) {
				self::render_texts();
			} else {
				if ( 'general' === $tab ) {
					self::render_help();
				}
				if ( 'scripts' === $tab ) {
					self::render_declared();
				}
				self::render_fields( $tab );
			}
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Mode d'emploi du lien « Gestion des témoins ».
	 */
	private static function render_help() {
		?>
		<p>
			<?php esc_html_e( 'Lien « Gestion des témoins » à mettre au pied de page : un lien personnalisé vers', 'em-consent' ); ?>
			<code>#em-consent</code>
			<?php esc_html_e( 'dans un menu, ou le code court', 'em-consent' ); ?>
			<code>[em_consent_gestion]</code>.
			<?php esc_html_e( 'Le choix du visiteur est gardé dans le témoin nécessaire em_consent.', 'em-consent' ); ?>
		</p>
		<?php
	}

	/**
	 * Champs du schéma pour un onglet.
	 *
	 * @param string $tab Onglet.
	 */
	private static function render_fields( $tab ) {
		$labels   = self::fields();
		$settings = EM_Consent_Settings::all();
		$name     = EM_Consent_Settings::OPTION;
		$can_html = current_user_can( 'unfiltered_html' );
		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( EM_Consent_Settings::schema() as $key => $field ) {
			if ( $field['tab'] !== $tab ) {
				continue;
			}
			$id          = 'em-consent-' . $key;
			$field_name  = $name . '[' . $key . ']';
			$value       = $settings[ $key ];
			$label       = isset( $labels[ $key ] ) ? $labels[ $key ][0] : $key;
			$description = isset( $labels[ $key ] ) ? $labels[ $key ][1] : '';
			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
			switch ( $field['type'] ) {
				case 'bool':
					printf( '<input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s>', esc_attr( $id ), esc_attr( $field_name ), checked( (bool) $value, true, false ) );
					break;
				case 'int':
					printf( '<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$d" min="%4$d" max="%5$d">', esc_attr( $id ), esc_attr( $field_name ), (int) $value, (int) $field['min'], (int) $field['max'] );
					break;
				case 'choice':
					$choices = array(
						'carte' => __( 'Carte en bas à gauche (pleine largeur sur cellulaire)', 'em-consent' ),
						'barre' => __( 'Barre pleine largeur en bas', 'em-consent' ),
					);
					printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $field_name ) );
					foreach ( $field['choices'] as $choice ) {
						printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $choice ), selected( $value, $choice, false ), esc_html( $choices[ $choice ] ?? $choice ) );
					}
					echo '</select>';
					break;
				case 'color':
					printf( '<input type="color" id="%1$s" name="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $field_name ), esc_attr( $value ) );
					break;
				case 'scripts':
					printf(
						'<textarea id="%1$s" name="%2$s" rows="8" class="large-text code"%3$s>%4$s</textarea>',
						esc_attr( $id ),
						esc_attr( $field_name ),
						$can_html ? '' : ' readonly',
						esc_textarea( (string) $value )
					);
					if ( ! $can_html ) {
						echo '<p class="description">' . esc_html__( 'Votre compte ne peut pas modifier ces scripts.', 'em-consent' ) . '</p>';
					}
					break;
				default:
					printf( '<input type="text" class="regular-text code" id="%1$s" name="%2$s" value="%3$s" placeholder="%4$s">', esc_attr( $id ), esc_attr( $field_name ), esc_attr( $value ), 'gtm_id' === $key ? 'GTM-…' : 'G-…' );
			}
			if ( $description ) {
				echo '<p class="description">' . esc_html( $description ) . '</p>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Liste des scripts déclarés par le thème et les extensions (filtre em_consent_scripts).
	 */
	private static function render_declared() {
		$declared = EM_Consent_Blocker::declared_scripts();
		echo '<h2>' . esc_html__( 'Scripts déclarés par le thème et les extensions', 'em-consent' ) . '</h2>';
		if ( ! $declared ) {
			echo '<p>' . esc_html__( 'Aucun. Un thème ou une extension en déclare avec le filtre em_consent_scripts (voir le README).', 'em-consent' ) . '</p>';
			return;
		}
		echo '<table class="widefat striped" style="max-width:640px"><thead><tr><th>' . esc_html__( 'Handle', 'em-consent' ) . '</th><th>' . esc_html__( 'Catégorie', 'em-consent' ) . '</th></tr></thead><tbody>';
		foreach ( $declared as $handle => $category ) {
			echo '<tr><td><code>' . esc_html( $handle ) . '</code></td><td>' . esc_html( $category ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Textes par langue : un bloc par langue active. Un champ vide reprend le texte par défaut,
	 * affiché en indication.
	 */
	private static function render_texts() {
		$name   = EM_Consent_Settings::OPTION;
		$textes = (array) EM_Consent_Settings::get( 'textes' );
		echo '<p>' . esc_html__( 'Laissez un champ vide pour garder le texte par défaut, affiché en gris. Liens, gras et italique permis dans les champs de plusieurs lignes.', 'em-consent' ) . '</p>';
		foreach ( EM_Consent_Texts::languages() as $lang => $language ) {
			echo '<h2>' . esc_html( $language ) . ' <code>' . esc_html( $lang ) . '</code></h2>';
			echo '<table class="form-table" role="presentation"><tbody>';
			foreach ( EM_Consent_Texts::definitions() as $key => $def ) {
				$id         = 'em-consent-' . $lang . '-' . $key;
				$field_name = $name . '[textes][' . $lang . '][' . $key . ']';
				$value      = isset( $textes[ $lang ][ $key ] ) ? (string) $textes[ $lang ][ $key ] : '';
				$default    = EM_Consent_Texts::default_value( $key, $lang );
				echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $def['label'] ) . '</label></th><td>';
				if ( 'html' === $def['type'] ) {
					printf( '<textarea id="%1$s" name="%2$s" rows="3" class="large-text" placeholder="%3$s">%4$s</textarea>', esc_attr( $id ), esc_attr( $field_name ), esc_attr( $default ), esc_textarea( $value ) );
				} else {
					printf( '<input type="%1$s" id="%2$s" name="%3$s" class="large-text" value="%4$s" placeholder="%5$s">', 'url' === $def['type'] ? 'url' : 'text', esc_attr( $id ), esc_attr( $field_name ), esc_attr( $value ), esc_attr( $default ) );
				}
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	/**
	 * Onglet Outils : redemander le consentement, export, import, réinitialisation.
	 */
	private static function render_tools() {
		$settings = get_option( EM_Consent_Settings::OPTION, array() );
		$action   = admin_url( 'admin-post.php' );
		?>
		<h2><?php esc_html_e( 'Redemander le consentement', 'em-consent' ); ?></h2>
		<p>
			<?php
			/* translators: %d: numéro de révision. */
			printf( esc_html__( 'Révision actuelle : %d. À faire après l’ajout d’un service qui dépose des témoins : chaque visiteur reverra la bannière.', 'em-consent' ), absint( EM_Consent_Settings::get( 'revision' ) ) );
			?>
		</p>
		<form method="post" action="<?php echo esc_url( $action ); ?>">
			<input type="hidden" name="action" value="em_consent_outil">
			<input type="hidden" name="outil" value="redemander">
			<?php wp_nonce_field( 'em_consent_outil' ); ?>
			<?php submit_button( __( 'Redemander à tous', 'em-consent' ), 'secondary', 'submit', false ); ?>
		</form>

		<h2><?php esc_html_e( 'Exporter', 'em-consent' ); ?></h2>
		<p><label for="em-consent-export"><?php esc_html_e( 'Réglages de ce site, à coller dans l’import d’un autre site.', 'em-consent' ); ?></label></p>
		<textarea id="em-consent-export" class="large-text code" rows="8" readonly><?php echo esc_textarea( (string) wp_json_encode( $settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea>

		<h2><?php esc_html_e( 'Importer', 'em-consent' ); ?></h2>
		<form method="post" action="<?php echo esc_url( $action ); ?>">
			<input type="hidden" name="action" value="em_consent_outil">
			<input type="hidden" name="outil" value="importer">
			<?php wp_nonce_field( 'em_consent_outil' ); ?>
			<p><label for="em-consent-import"><?php esc_html_e( 'JSON exporté d’un autre site. La révision augmente : le consentement est redemandé.', 'em-consent' ); ?></label></p>
			<textarea id="em-consent-import" name="json" class="large-text code" rows="8"></textarea>
			<?php submit_button( __( 'Importer', 'em-consent' ), 'secondary', 'submit', false ); ?>
		</form>

		<h2><?php esc_html_e( 'Réinitialiser', 'em-consent' ); ?></h2>
		<form method="post" action="<?php echo esc_url( $action ); ?>" onsubmit="return confirm(<?php echo esc_attr( wp_json_encode( __( 'Remettre tous les réglages à leurs valeurs par défaut ?', 'em-consent' ) ) ); ?>);">
			<input type="hidden" name="action" value="em_consent_outil">
			<input type="hidden" name="outil" value="reinitialiser">
			<?php wp_nonce_field( 'em_consent_outil' ); ?>
			<?php submit_button( __( 'Réinitialiser les réglages', 'em-consent' ), 'delete', 'submit', false ); ?>
		</form>
		<?php
	}

	/**
	 * Traite un outil (admin-post.php).
	 */
	public static function tool() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'em-consent' ), 403 );
		}
		check_admin_referer( 'em_consent_outil' );

		$tool     = isset( $_POST['outil'] ) ? sanitize_key( wp_unslash( $_POST['outil'] ) ) : '';
		$current  = array_merge( EM_Consent_Settings::defaults(), (array) get_option( EM_Consent_Settings::OPTION, array() ) );
		$revision = absint( $current['revision'] ) + 1;
		$msg      = '';

		if ( 'redemander' === $tool ) {
			$current['revision'] = $revision;
			update_option( EM_Consent_Settings::OPTION, $current );
			$msg = 'redemande';
		} elseif ( 'importer' === $tool ) {
			// Le JSON est nettoyé champ par champ par EM_Consent_Settings::sanitize() (filtre de l'option).
			$json = isset( $_POST['json'] ) ? json_decode( wp_unslash( $_POST['json'] ), true ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( is_array( $json ) ) {
				$json['revision'] = $revision;
				update_option( EM_Consent_Settings::OPTION, $json );
				$msg = 'importe';
			} else {
				$msg = 'invalide';
			}
		} elseif ( 'reinitialiser' === $tool ) {
			$defaults             = EM_Consent_Settings::defaults();
			$defaults['revision'] = $revision;
			delete_option( EM_Consent_Settings::OPTION );
			add_option( EM_Consent_Settings::OPTION, $defaults );
			$msg = 'reinit';
		}

		wp_safe_redirect( add_query_arg( 'em_consent_msg', $msg, admin_url( 'options-general.php?page=' . self::PAGE . '&tab=outils' ) ) );
		exit;
	}
}
