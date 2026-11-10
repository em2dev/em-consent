<?php
/**
 * Côté visiteur : valeurs par défaut du mode de consentement de Google dans <head>, bannière,
 * fenêtre de préférences et lien « Gestion des témoins ».
 *
 * Le HTML est le même pour tous les visiteurs : c'est le JavaScript qui lit le choix enregistré.
 * Les pages restent donc dans le cache de LiteSpeed sans variation par témoin.
 *
 * @package em-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bannière et chargement côté visiteur.
 */
class EM_Consent_Front {

	/**
	 * Nom du témoin qui garde le choix du visiteur (témoin nécessaire).
	 */
	const COOKIE = 'em_consent';

	/**
	 * La bannière a-t-elle déjà été imprimée ?
	 *
	 * @var bool
	 */
	private static $printed = false;

	/**
	 * Branche les crochets.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'print_head' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'script_loader_tag', array( __CLASS__, 'exclude_from_optimizers' ), 10, 2 );
		add_action( 'wp_body_open', array( __CLASS__, 'print_banner' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'print_banner' ), 5 );
		add_shortcode( 'em_consent_gestion', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * L'extension agit-elle sur cette page ? Il faut au moins une catégorie facultative.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$enabled = ! is_admin() && ! is_feed() && ! is_embed() && ! is_customize_preview() && EM_Consent_Settings::active_categories();

		/**
		 * Active ou désactive l'extension sur la page affichée.
		 *
		 * @param bool $enabled Actif.
		 */
		return (bool) apply_filters( 'em_consent_enabled', $enabled );
	}

	/**
	 * Configuration transmise au JavaScript (la même pour tous les visiteurs).
	 *
	 * @return array
	 */
	public static function config() {
		$config = array(
			'cookie'      => self::COOKIE,
			'revision'    => absint( EM_Consent_Settings::get( 'revision' ) ),
			'jours'       => EM_Consent_Settings::duration_days(),
			'categories'  => EM_Consent_Settings::active_categories(),
			'gtm'         => (string) EM_Consent_Settings::get( 'gtm_id' ),
			'ga4'         => (string) EM_Consent_Settings::get( 'ga4_id' ),
			'consentMode' => true,
			// Envois retenus pendant le retrait du consentement (expression régulière sur l'adresse).
			'traqueurs'   => 'google-analytics\.com|analytics\.google\.com|googletagmanager\.com|google\.[a-z.]+/(g|ccm|pagead)/|doubleclick\.net|facebook\.com/tr|clarity\.ms|hotjar\.(com|io)|linkedin\.com/(px|collect)|analytics\.tiktok\.com|bat\.bing\.com',
			// Témoins effacés quand une catégorie est retirée (préfixes de noms).
			'effacer'     => array(
				'mesure'    => array( '_ga', '_gid', '_gat', '_clck', '_clsk', '_hj' ),
				'marketing' => array( '_gcl_', '_fbp', '_fbc', '_ttp', '_uet', '_pin_', 'li_', 'IDE' ),
			),
		);

		/**
		 * Filtre la configuration transmise au JavaScript. consentMode à false retire le mode de
		 * consentement de Google ; effacer liste les préfixes de témoins effacés au retrait d'une catégorie.
		 *
		 * @param array $config Configuration.
		 */
		return apply_filters( 'em_consent_config', $config );
	}

	/**
	 * Premier élément de <head> : valeurs par défaut du mode de consentement de Google v2 (tout refusé
	 * sauf le nécessaire), puis le choix déjà enregistré. Aucun domaine tiers n'est contacté ici.
	 */
	public static function print_head() {
		if ( ! self::is_enabled() ) {
			return;
		}
		$config = self::config();
		$js     = 'window.emConsentConfig=' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP ) . ';';
		if ( $config['consentMode'] ) {
			$js .= <<<'JS'
(function(w,d){var c=w.emConsentConfig;w.dataLayer=w.dataLayer||[];w.gtag=w.gtag||function(){w.dataLayer.push(arguments);};
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',personalization_storage:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});
gtag('set','ads_data_redaction',true);
var m=d.cookie.match(new RegExp('(?:^|;\\s*)'+c.cookie+'=([^;]*)')),s=null;try{s=m&&JSON.parse(decodeURIComponent(m[1]));}catch(e){}
if(s&&s.v===c.revision&&s.c){var a=s.c.mesure?'granted':'denied',k=s.c.marketing?'granted':'denied';
gtag('consent','update',{analytics_storage:a,ad_storage:k,ad_user_data:k,ad_personalization:k});gtag('set','ads_data_redaction',!s.c.marketing);}
})(window,document);
JS;
		}
		wp_print_inline_script_tag( $js, self::optimizer_attributes( array( 'id' => 'em-consent-defaut' ) ) );
	}

	/**
	 * Attributs qui excluent un script des optimiseurs (LiteSpeed, WP Rocket, Cloudflare Rocket
	 * Loader) : retardé ou combiné, il ne pourrait plus régler le consentement avant les balises.
	 *
	 * @param array $attributes Attributs.
	 * @return array
	 */
	private static function optimizer_attributes( $attributes ) {
		return array_merge(
			$attributes,
			array(
				'data-no-optimize' => '1',
				'data-no-defer'    => '1',
				'data-no-minify'   => '1',
				'data-cfasync'     => 'false',
				'nowprocket'       => true,
			)
		);
	}

	/**
	 * Feuille de style et script de la bannière.
	 */
	public static function enqueue() {
		if ( ! self::is_enabled() ) {
			return;
		}
		wp_enqueue_style( 'em-consent', EM_CONSENT_URL . 'assets/css/em-consent.css', array(), em_consent_asset_version( 'assets/css/em-consent.css' ) );
		wp_add_inline_style( 'em-consent', self::css_vars() );
		wp_enqueue_script(
			'em-consent',
			EM_CONSENT_URL . 'assets/js/em-consent.js',
			array(),
			em_consent_asset_version( 'assets/js/em-consent.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);
	}

	/**
	 * Ajoute les attributs d'exclusion des optimiseurs au script de la bannière.
	 *
	 * @param string $tag    Balise.
	 * @param string $handle Handle.
	 * @return string
	 */
	public static function exclude_from_optimizers( $tag, $handle ) {
		if ( 'em-consent' !== $handle ) {
			return $tag;
		}
		$processor = new WP_HTML_Tag_Processor( $tag );
		while ( $processor->next_tag( array( 'tag_name' => 'SCRIPT' ) ) ) {
			foreach ( self::optimizer_attributes( array() ) as $name => $value ) {
				$processor->set_attribute( $name, $value );
			}
		}
		return $processor->get_updated_html();
	}

	/**
	 * Variables CSS tirées des réglages d'apparence.
	 *
	 * @return string
	 */
	private static function css_vars() {
		$vars = array(
			'--em-consent-fond'          => EM_Consent_Settings::get( 'couleur_fond' ),
			'--em-consent-texte'         => EM_Consent_Settings::get( 'couleur_texte' ),
			'--em-consent-bouton'        => EM_Consent_Settings::get( 'couleur_bouton' ),
			'--em-consent-bouton-texte'  => EM_Consent_Settings::get( 'couleur_bouton_texte' ),
			'--em-consent-arrondi'       => absint( EM_Consent_Settings::get( 'arrondi' ) ) . 'px',
			'--em-consent-arrondi-petit' => round( absint( EM_Consent_Settings::get( 'arrondi' ) ) * 0.75 ) . 'px',
		);

		/**
		 * Filtre les variables CSS de la bannière.
		 *
		 * @param array $vars Variable => valeur.
		 */
		$vars = apply_filters( 'em_consent_css_vars', $vars );
		$css  = '';
		foreach ( $vars as $name => $value ) {
			$value = str_replace( array( ';', '{', '}', '<' ), '', (string) $value );
			$css  .= sanitize_key( ltrim( $name, '-' ) ) ? $name . ':' . $value . ';' : '';
		}
		return ':root{' . $css . '}';
	}

	/**
	 * Bannière et fenêtre de préférences. Imprimées tôt (wp_body_open) pour être atteintes en premier
	 * au clavier ; wp_footer sert de repli aux thèmes qui n'appellent pas wp_body_open().
	 */
	public static function print_banner() {
		if ( self::$printed || ! self::is_enabled() ) {
			return;
		}
		self::$printed = true;

		$categories = EM_Consent_Settings::active_categories();
		$position   = 'barre' === EM_Consent_Settings::get( 'position' ) ? 'barre' : 'carte';
		$policy_url = EM_Consent_Texts::policy_url();
		$allowed    = EM_Consent_Texts::allowed_html();
		?>
<div id="em-consent" class="em-consent em-consent--<?php echo esc_attr( $position ); ?>" role="region" aria-labelledby="em-consent-titre" hidden>
	<div class="em-consent__boite">
		<p class="em-consent__titre" id="em-consent-titre"><?php echo esc_html( EM_Consent_Texts::get( 'titre' ) ); ?></p>
		<p class="em-consent__message">
			<?php echo wp_kses( EM_Consent_Texts::get( 'message' ), $allowed ); ?>
			<?php if ( $policy_url ) : ?>
				<a href="<?php echo esc_url( $policy_url ); ?>"><?php echo esc_html( EM_Consent_Texts::get( 'politique_libelle' ) ); ?></a>
			<?php endif; ?>
		</p>
		<div class="em-consent__boutons">
			<button type="button" class="em-consent__bouton" data-em-consent-action="accepter"><?php echo esc_html( EM_Consent_Texts::get( 'accepter' ) ); ?></button>
			<button type="button" class="em-consent__bouton" data-em-consent-action="refuser"><?php echo esc_html( EM_Consent_Texts::get( 'refuser' ) ); ?></button>
			<button type="button" class="em-consent__bouton" data-em-consent-action="personnaliser" aria-haspopup="dialog"><?php echo esc_html( EM_Consent_Texts::get( 'personnaliser' ) ); ?></button>
		</div>
	</div>
</div>
<dialog id="em-consent-preferences" class="em-consent-preferences" aria-labelledby="em-consent-preferences-titre">
	<div class="em-consent-preferences__corps">
		<div class="em-consent-preferences__tete">
			<h2 class="em-consent-preferences__titre" id="em-consent-preferences-titre"><?php echo esc_html( EM_Consent_Texts::get( 'preferences_titre' ) ); ?></h2>
			<button type="button" class="em-consent-preferences__fermer" data-em-consent-action="fermer" aria-label="<?php echo esc_attr( EM_Consent_Texts::get( 'fermer' ) ); ?>"><span aria-hidden="true">&times;</span></button>
		</div>
		<p class="em-consent-preferences__intro"><?php echo wp_kses( EM_Consent_Texts::get( 'preferences_intro' ), $allowed ); ?></p>
		<ul class="em-consent-preferences__liste">
			<li class="em-consent-categorie">
				<div class="em-consent-categorie__tete">
					<label class="em-consent-categorie__nom" for="em-consent-necessaires"><?php echo esc_html( EM_Consent_Texts::get( 'necessaires_titre' ) ); ?></label>
					<span class="em-consent-categorie__etat">
						<span class="em-consent-categorie__toujours"><?php echo esc_html( EM_Consent_Texts::get( 'toujours_actifs' ) ); ?></span>
						<input type="checkbox" role="switch" class="em-consent-interrupteur" id="em-consent-necessaires" checked disabled aria-describedby="em-consent-necessaires-desc">
					</span>
				</div>
				<p class="em-consent-categorie__desc" id="em-consent-necessaires-desc"><?php echo wp_kses( EM_Consent_Texts::get( 'necessaires_desc' ), $allowed ); ?></p>
			</li>
			<?php foreach ( $categories as $category ) : ?>
				<li class="em-consent-categorie">
					<div class="em-consent-categorie__tete">
						<label class="em-consent-categorie__nom" for="em-consent-<?php echo esc_attr( $category ); ?>"><?php echo esc_html( EM_Consent_Texts::get( $category . '_titre' ) ); ?></label>
						<span class="em-consent-categorie__etat">
							<input type="checkbox" role="switch" class="em-consent-interrupteur" id="em-consent-<?php echo esc_attr( $category ); ?>" data-em-consent-categorie="<?php echo esc_attr( $category ); ?>" aria-describedby="em-consent-<?php echo esc_attr( $category ); ?>-desc">
						</span>
					</div>
					<p class="em-consent-categorie__desc" id="em-consent-<?php echo esc_attr( $category ); ?>-desc"><?php echo wp_kses( EM_Consent_Texts::get( $category . '_desc' ), $allowed ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="em-consent__boutons">
			<button type="button" class="em-consent__bouton" data-em-consent-action="accepter"><?php echo esc_html( EM_Consent_Texts::get( 'accepter' ) ); ?></button>
			<button type="button" class="em-consent__bouton" data-em-consent-action="refuser"><?php echo esc_html( EM_Consent_Texts::get( 'refuser' ) ); ?></button>
			<button type="button" class="em-consent__bouton" data-em-consent-action="enregistrer"><?php echo esc_html( EM_Consent_Texts::get( 'enregistrer' ) ); ?></button>
		</div>
	</div>
</dialog>
		<?php
	}

	/**
	 * Code court [em_consent_gestion] : lien qui rouvre les préférences. Un lien de menu vers
	 * « #em-consent » fait la même chose sans code court.
	 *
	 * @param array|string $atts Attributs : texte (facultatif), class (facultatif).
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts  = shortcode_atts(
			array(
				'texte' => '',
				'class' => '',
			),
			$atts,
			'em_consent_gestion'
		);
		$text  = '' !== $atts['texte'] ? $atts['texte'] : EM_Consent_Texts::get( 'lien_gestion' );
		$class = trim( 'em-consent-lien ' . implode( ' ', array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) $atts['class'] ) ) ) );
		return sprintf(
			'<a href="#em-consent" class="%1$s" aria-haspopup="dialog">%2$s</a>',
			esc_attr( $class ),
			esc_html( $text )
		);
	}

	/**
	 * Choix du visiteur lu dans son témoin (côté serveur, fiable seulement hors cache de page).
	 *
	 * @param string $category Catégorie.
	 * @return bool
	 */
	public static function visitor_has( $category ) {
		if ( 'necessaires' === $category ) {
			return true;
		}
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return false;
		}
		$state = json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ), true );
		if ( ! is_array( $state ) || ! isset( $state['v'], $state['c'] ) || absint( $state['v'] ) !== absint( EM_Consent_Settings::get( 'revision' ) ) ) {
			return false;
		}
		return ! empty( $state['c'][ $category ] );
	}
}
