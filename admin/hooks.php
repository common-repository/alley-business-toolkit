<?php


/**
 * The One Page Conference Themes theme hooks callback functionality of the plugin.
 *
 * Since One Page Conference Themes theme is hooks base theme, this file is main callback to add/remove/edit the functionality of the CosmosWP Theme
 *
 * @package    One Page Conference Themes
 */
class Alley_Business_Toolkit_Hooks {

    /**
     * current added Menu hook_suffix
     *
     * @since    1.0.0
     * @access   public
     * @var      array    $logs    Store logs and errors.
     */
    private $hook_suffix;


    /**
     * theme author name
     *
     * @since    1.0.0
     * @access   public
     * @var      array    $logs    Store logs and errors.
     */
    private $theme_author = 'WPMagPlus';

    /**
     * Main Instance
     *
     * Insures that only one instance of CosmosWP Pro exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since    1.0.0
     * @access   public
     *
     * @return object
     */
    public static function instance() {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been ran previously
        if ( null === $instance ) {
            $instance = new self();
        }

        // Always return the instance
        return $instance;
    }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {}

    /**
     * Redirect to plugin page when plugin activated
     *
     * @since 1.0.0
     */
    public static function redirect(){
        if ( get_option( '__alley_business_toolkit_do_redirect' ) ) {
            update_option( '__alley_business_toolkit_do_redirect', false );
            if ( ! is_multisite() ) {
                exit( wp_redirect( admin_url( 'themes.php?page=advanced-import' )));
            }
        }
    }
    /**
     * Add admin menus
     * @access public
     */
    public function import_menu() {
        if( !class_exists('Advanced_Import')){
            $this->hook_suffix[] = add_theme_page( esc_html__( 'Demo Import ','alley-business-toolkit' ), esc_html__( 'Demo Import' ), 'manage_options', 'advanced-import', array( $this, 'demo_import_screen' ) );
        }
    }
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles( $hook_suffix ) {
        if ( !is_array($this->hook_suffix) || !in_array( $hook_suffix, $this->hook_suffix )){
            return;
        }
        wp_enqueue_style( ALLEY_BUSINESS_TOOLKIT_PLUGIN_NAME, ALLEY_BUSINESS_TOOLKIT_URL . 'admin/css/alley-business-toolkit-admin.css',array( 'wp-admin', 'dashicons' ), ALLEY_BUSINESS_TOOLKIT_VERSION, 'all' );
    }

    public function admin_enqueue_styles( $hook_suffix ) {
        wp_enqueue_style( 'dashboard-style', ALLEY_BUSINESS_TOOLKIT_URL . 'admin/css/dashboard-style.css',array( 'wp-admin', 'dashicons' ), ALLEY_BUSINESS_TOOLKIT_VERSION, 'all' );
    }
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook_suffix ) {
        if ( !is_array($this->hook_suffix) || !in_array( $hook_suffix, $this->hook_suffix )){
            return;
        }

        wp_enqueue_script( ALLEY_BUSINESS_TOOLKIT_PLUGIN_NAME, ALLEY_BUSINESS_TOOLKIT_URL . 'admin/js/alley-business-toolkit-admin.js', array( 'jquery'), ALLEY_BUSINESS_TOOLKIT_VERSION, true );
        wp_localize_script( ALLEY_BUSINESS_TOOLKIT_PLUGIN_NAME, 'alley_business_toolkit', array(
            'btn_text' => esc_html__( 'Processing...', 'alley-business-toolkit' ),
            'nonce'    => wp_create_nonce( 'alley_business_toolkit_nonce' )
        ) );
    }

    /**
     * Show the plugin recommended screen
     * @access public
     * @return void
     */
    public function demo_import_screen() {
        ?>
        <div id="ads-notice">
            <div class="ads-container">
                <img class="ads-screenshot" src="<?php echo esc_url(alley_business_toolkit_get_theme_screenshot() )?>" alt="<?php echo esc_html( alley_business_toolkit_get_theme_name() ); ?>" />
                <div class="ads-notice">
                    <h2>
                        <?php
                        printf(
                        /* translators: 1: welcome page link starting html tag, 2: welcome page link ending html tag. */
                            esc_html__( 'Welcome! Thank you for choosing %1$s! To get started with ready-made starter site templates. Install the Advanced Import plugin and install Demo Starter Site within a single click', 'alley-business-toolkit' ), '<strong>'. wp_get_theme()->get('Name'). '</strong>');
                        ?>
                    </h2>

                    <p class="plugin-install-notice"><?php esc_html_e( 'Clicking the button below will install and activate the Advanced Import plugin.', 'alley-business-toolkit' ); ?></p>

                    <a class="ads-gsm-btn button button-primary button-hero" href="#" data-name="" data-slug="" aria-label="<?php esc_html_e( 'Get started with the Theme', 'alley-business-toolkit' ); ?>">
                        <?php esc_html_e( 'Get Started', 'alley-business-toolkit' );?>
                    </a>
                </div>
            </div>
        </div>
        <?php

    }

    /**
     * Get Started Notice
     * Active callback of wp_ajax
     * return void
     */
    public function install_advanced_import() {

        check_ajax_referer( 'alley_business_toolkit_nonce', 'security' );

        $slug   = 'advanced-import';
        $plugin = 'advanced-import/advanced-import.php';

        $status = array(
            'install' => 'plugin',
            'slug'    => sanitize_key( wp_unslash( $slug ) ),
        );
        $status['redirect'] = admin_url( '/themes.php?page=advanced-import&browse=all&at-gsm-hide-notice=welcome' );

        if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
            // Plugin is activated
            wp_send_json_success($status);
        }


        if ( ! current_user_can( 'install_plugins' ) ) {
            $status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'alley-business-toolkit' );
            wp_send_json_error( $status );
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        // Looks like a plugin is installed, but not active.
        if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
            $plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $status['plugin']     = $plugin;
            $status['pluginName'] = $plugin_data['Name'];

            if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
                $result = activate_plugin( $plugin );

                if ( is_wp_error( $result ) ) {
                    $status['errorCode']    = $result->get_error_code();
                    $status['errorMessage'] = $result->get_error_message();
                    wp_send_json_error( $status );
                }

                wp_send_json_success( $status );
            }
        }

        $api = plugins_api(
            'plugin_information',
            array(
                'slug'   => sanitize_key( wp_unslash( $slug ) ),
                'fields' => array(
                    'sections' => false,
                ),
            )
        );

        if ( is_wp_error( $api ) ) {
            $status['errorMessage'] = $api->get_error_message();
            wp_send_json_error( $status );
        }

        $status['pluginName'] = $api->name;

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $status['debug'] = $skin->get_upgrade_messages();
        }

        if ( is_wp_error( $result ) ) {
            $status['errorCode']    = $result->get_error_code();
            $status['errorMessage'] = $result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( is_wp_error( $skin->result ) ) {
            $status['errorCode']    = $skin->result->get_error_code();
            $status['errorMessage'] = $skin->result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( $skin->get_errors()->get_error_code() ) {
            $status['errorMessage'] = $skin->get_error_messages();
            wp_send_json_error( $status );
        } elseif ( is_null( $result ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            WP_Filesystem();
            global $wp_filesystem;

            $status['errorCode']    = 'unable_to_connect_to_filesystem';
            $status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'alley-business-toolkit' );

            // Pass through the error from WP_Filesystem if one was raised.
            if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
                $status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
            }

            wp_send_json_error( $status );
        }

        $install_status = install_plugin_install_status( $api );

        if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
            $result = activate_plugin( $install_status['file'] );

            if ( is_wp_error( $result ) ) {
                $status['errorCode']    = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error( $status );
            }
        }

        wp_send_json_success( $status );

    }

    /**
     * Function to fetch templates.
     *
     * @param $current_demo_list array
     * @return array
     */
    public function add_demo_lists( $current_demo_list ) {

        $theme_slug = alley_business_toolkit_get_current_theme_slug();

        $templates = alley_business_toolkit_get_templates_lists( $theme_slug );

        return array_merge( $current_demo_list, $templates );

    }
}

/**
 * Begins execution of the hooks.
 *
 * @since    1.0.0
 */
function alley_business_toolkit_hooks( ) {
    return Alley_Business_Toolkit_Hooks::instance();
}