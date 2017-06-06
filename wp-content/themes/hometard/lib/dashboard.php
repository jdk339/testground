<?php
/**
 * Add theme dashboard page
 */

/**
 * Get theme actions required
 *
 * @return array|mixed|void
 */
function hometard_get_actions_required( ) {

    $actions = array();
    $front_page = get_option( 'page_on_front' );
    $actions['page_on_front'] = 'dismiss';
    $actions['page_template'] = 'dismiss';
    $actions['recommend_plugins'] = 'dismiss';
    if ( 'page' != get_option( 'show_on_front' ) ) {
        $front_page = 0;
    }
    if ( $front_page <= 0  ) {
        $actions['page_on_front'] = 'active';
        $actions['page_template'] = 'active';
    } else {
        if ( get_post_meta( $front_page, '_wp_page_template', true ) == 'template-parts/template-homepage.php' ) {
            $actions['page_template'] = 'dismiss';
        } else {
            $actions['page_template'] = 'active';
        }
    }

    $recommend_plugins = get_theme_support( 'recommend-plugins' );
    if ( is_array( $recommend_plugins ) && isset( $recommend_plugins[0] ) ){
        $recommend_plugins = $recommend_plugins[0];
    } else {
        $recommend_plugins[] = array();
    }

    if ( ! empty( $recommend_plugins ) ) {

        foreach ( $recommend_plugins as $plugin_slug => $plugin_info ) {
            $plugin_info = wp_parse_args( $plugin_info, array(
                'name' => '',
                'active_filename' => '',
            ) );
            if ( $plugin_info['active_filename'] ) {
                $active_file_name = $plugin_info['active_filename'] ;
            } else {
                $active_file_name = $plugin_slug . '/' . $plugin_slug . '.php';
            }
            if ( ! is_plugin_active( $active_file_name ) ) {
                $actions['recommend_plugins'] = 'active';
            }
        }

    }

    $actions = apply_filters( 'hometard_get_actions_required', $actions );
    $hide_by_click = get_option( 'hometard_actions_dismiss' );
    if ( ! is_array( $hide_by_click ) ) {
        $hide_by_click = array();
    }

    $n_active  = $n_dismiss = 0;
    $number_notice = 0;
    foreach ( $actions as $k => $v ) {
        if ( ! isset( $hide_by_click[ $k ] ) ) {
            $hide_by_click[ $k ] = false;
        }

        if ( $v == 'active' ) {
            $n_active ++ ;
            $number_notice ++ ;
            if ( $hide_by_click[ $k ] ) {
                if ( $hide_by_click[ $k ] == 'hide' ) {
                    $number_notice -- ;
                }
            }
        } else if ( $v == 'dismiss' ) {
            $n_dismiss ++ ;
        }

    }

    $return = array(
        'actions' => $actions,
        'number_actions' => count( $actions ),
        'number_active' => $n_active,
        'number_dismiss' => $n_dismiss,
        'hide_by_click'  => $hide_by_click,
        'number_notice'  => $number_notice,
    );
    if ( $return['number_notice'] < 0 ) {
        $return['number_notice'] = 0;
    }

    return $return;
}

add_action('switch_theme', 'hometard_reset_actions_required');
function hometard_reset_actions_required () {
    delete_option('hometard_actions_dismiss');
}


if ( ! function_exists( 'hometard_admin_scripts' ) ) :
    /**
     * Enqueue scripts for admin page only: Theme info page
     */
    function hometard_admin_scripts( $hook ) {
        if ( $hook === 'appearance_page_hmtd_hometard'  ) {
            wp_enqueue_style( 'hometard-admin-css', get_template_directory_uri() . '/css/admin.css' );
            // Add recommend plugin css
            wp_enqueue_style( 'plugin-install' );
            wp_enqueue_script( 'plugin-install' );
            wp_enqueue_script( 'updates' );
            add_thickbox();
        }
    }
endif;
add_action( 'admin_enqueue_scripts', 'hometard_admin_scripts' );

add_action('admin_menu', 'hometard_theme_info');
function hometard_theme_info() {

    $actions = hometard_get_actions_required();
    $number_count = $actions['number_notice'];

    if ( $number_count > 0 ){
        $update_label = sprintf( _n( '%1$s action required', '%1$s actions required', $number_count, 'hometard' ), $number_count );
        $count = "<span class='update-plugins count-".esc_attr( $number_count )."' title='".esc_attr( $update_label )."'><span class='update-count'>" . number_format_i18n($number_count) . "</span></span>";
        $menu_title = sprintf( esc_html__('Hometard Theme %s', 'hometard'), $count );
    } else {
        $menu_title = esc_html__('Hometard Theme', 'hometard');
    }

    add_theme_page( esc_html__( 'Hometard Dashboard', 'hometard' ), $menu_title, 'edit_theme_options', 'hmtd_hometard', 'hometard_theme_info_page');
}


/**
 * Add admin notice when active theme, just show one time
 *
 * @return bool|null
 */
function hometard_admin_notice() {
    if ( ! function_exists( 'hometard_get_actions_required' ) ) {
        return false;
    }
    $actions = hometard_get_actions_required();
    $number_action = $actions['number_notice'];

    if ( $number_action > 0 ) {
        $theme_data = wp_get_theme();
        ?>
        <div class="updated notice notice-success notice-alt is-dismissible">
            <p><?php printf( __( 'Welcome! Thank you for choosing %1$s! To fully take advantage of the best our theme can offer please make sure you visit our <a href="%2$s">Welcome page</a>', 'hometard' ),  $theme_data->Name, admin_url( 'themes.php?page=hmtd_hometard' )  ); ?></p>
        </div>
        <?php
    }
}

function hometard_one_activation_admin_notice(){
    global $pagenow;
    if ( is_admin() && ('themes.php' == $pagenow) && isset( $_GET['activated'] ) ) {
        add_action( 'admin_notices', 'hometard_admin_notice' );
    }
}


function hometard_render_recommend_plugins( $recommend_plugins = array() ){
    foreach ( $recommend_plugins as $plugin_slug => $plugin_info ) {
        $plugin_info = wp_parse_args( $plugin_info, array(
            'name' => '',
            'active_filename' => '',
        ) );
        $plugin_name = $plugin_info['name'];
        $status = is_dir( WP_PLUGIN_DIR . '/' . $plugin_slug );
        $button_class = 'install-now button';
        if ( $plugin_info['active_filename'] ) {
            $active_file_name = $plugin_info['active_filename'] ;
        } else {
            $active_file_name = $plugin_slug . '/' . $plugin_slug . '.php';
        }

        if ( ! is_plugin_active( $active_file_name ) ) {
            $button_txt = esc_html__( 'Install Now', 'hometard' );
            if ( ! $status ) {
                $install_url = wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'install-plugin',
                            'plugin' => $plugin_slug
                        ),
                        network_admin_url( 'update.php' )
                    ),
                    'install-plugin_'.$plugin_slug
                );

            } else {
                $install_url = add_query_arg(array(
                    'action' => 'activate',
                    'plugin' => rawurlencode( $active_file_name ),
                    'plugin_status' => 'all',
                    'paged' => '1',
                    '_wpnonce' => wp_create_nonce('activate-plugin_' . $active_file_name ),
                ), network_admin_url('plugins.php'));
                $button_class = 'activate-now button-primary';
                $button_txt = esc_html__( 'Active Now', 'hometard' );
            }

            $detail_link = add_query_arg(
                array(
                    'tab' => 'plugin-information',
                    'plugin' => $plugin_slug,
                    'TB_iframe' => 'true',
                    'width' => '772',
                    'height' => '349',

                ),
                network_admin_url( 'plugin-install.php' )
            );

            echo '<div class="rcp">';
            echo '<h4 class="rcp-name">';
            echo esc_html( $plugin_name );
            echo '</h4>';
            echo '<p class="action-btn plugin-card-'.esc_attr( $plugin_slug ).'"><a href="'.esc_url( $install_url ).'" data-slug="'.esc_attr( $plugin_slug ).'" class="'.esc_attr( $button_class ).'">'.$button_txt.'</a></p>';
            echo '<a class="plugin-detail thickbox open-plugin-details-modal" href="'.esc_url( $detail_link ).'">'.esc_html__( 'Details', 'hometard' ).'</a>';
            echo '</div>';
        }

    }
}

function hometard_admin_dismiss_actions(){
    // Action for dismiss
    if ( isset( $_GET['hometard_action_notice'] ) ) {
        $actions_dismiss =  get_option( 'hometard_actions_dismiss' );
        if ( ! is_array( $actions_dismiss ) ) {
            $actions_dismiss = array();
        }
        $action_key = stripslashes( $_GET['hometard_action_notice'] );
        if ( isset( $actions_dismiss[ $action_key ] ) &&  $actions_dismiss[ $action_key ] == 'hide' ){
            $actions_dismiss[ $action_key ] = 'show';
        } else {
            $actions_dismiss[ $action_key ] = 'hide';
        }
        update_option( 'hometard_actions_dismiss', $actions_dismiss );
        $url = $_SERVER['REQUEST_URI'];
        $url = remove_query_arg( 'hometard_action_notice', $url );
        wp_redirect( $url );
        die();
    }

    // Action for copy options
    if ( isset( $_POST['copy_from'] ) && isset( $_POST['copy_to'] ) ) {
        $from = sanitize_text_field( $_POST['copy_from'] );
        $to = sanitize_text_field( $_POST['copy_to'] );
        if ( $from && $to ) {
            $mods = get_option("theme_mods_" . $from);
            update_option("theme_mods_" . $to, $mods);

            $url = $_SERVER['REQUEST_URI'];
            $url = add_query_arg(array('copied' => 1), $url);
            wp_redirect($url);
            die();
        }
    }

}

add_action( 'admin_init', 'hometard_admin_dismiss_actions' );


/* activation notice */
add_action( 'load-themes.php',  'hometard_one_activation_admin_notice'  );

function hometard_theme_info_page() {

    $theme_data = wp_get_theme('hometard');

    if ( isset( $_GET['hometard_action_dismiss'] ) ) {
        $actions_dismiss =  get_option( 'hometard_actions_dismiss' );
        if ( ! is_array( $actions_dismiss ) ) {
            $actions_dismiss = array();
        }
        $actions_dismiss[ stripslashes( $_GET['hometard_action_dismiss'] ) ] = 'dismiss';
        update_option( 'hometard_actions_dismiss', $actions_dismiss );
    }

    // Check for current viewing tab
    $tab = null;
    if ( isset( $_GET['tab'] ) ) {
        $tab = $_GET['tab'];
    } else {
        $tab = null;
    }

    $actions_r = hometard_get_actions_required();
    $number_action = $actions_r['number_notice'];
    $actions = $actions_r['actions'];

    $current_action_link =  admin_url( 'themes.php?page=hmtd_hometard&tab=actions_required' );

    $recommend_plugins = get_theme_support( 'recommend-plugins' );
    if ( is_array( $recommend_plugins ) && isset( $recommend_plugins[0] ) ){
        $recommend_plugins = $recommend_plugins[0];
    } else {
        $recommend_plugins[] = array();
    }
    ?>
    <div class="wrap about-wrap theme_info_wrapper">
        <h1><?php printf(esc_html__('Welcome to Hometard - Version %1s', 'hometard'), $theme_data->Version ); ?></h1>
        <div class="about-text"><?php echo $theme_data->Description; ?></div>
        <h2 class="nav-tab-wrapper">
            <a href="?page=hmtd_hometard" class="nav-tab<?php echo is_null($tab) ? ' nav-tab-active' : null; ?>"><?php esc_html_e( 'Hometard', 'hometard' ) ?></a>
            <a href="?page=hmtd_hometard&tab=actions_required" class="nav-tab<?php echo $tab == 'actions_required' ? ' nav-tab-active' : null; ?>"><?php esc_html_e( 'Actions Required', 'hometard' ); echo ( $number_action > 0 ) ? "<span class='theme-action-count'>{$number_action}</span>" : ''; ?></a>
            <?php do_action( 'hometard_admin_more_tabs' ); ?>
        </h2>

        <?php if ( is_null( $tab ) ) { ?>
            <div class="theme_info info-tab-content">
                <div class="theme_info_column clearfix">
                    <div class="theme_info_left">

                        <div class="theme_link">
                            <h3><?php esc_html_e( 'Theme Customizer', 'hometard' ); ?></h3>
                            <p class="about"><?php printf(esc_html__('%s supports the Theme Customizer for all theme settings. Click "Customize" to start customize your site.', 'hometard'), $theme_data->Name); ?></p>
                            <p>
                                <a href="<?php echo admin_url('customize.php'); ?>" class="button button-primary"><?php esc_html_e('Start Customize', 'hometard'); ?></a>
                            </p>
                        </div>
                        <div class="theme_link">
                            <h3><?php esc_html_e( 'Theme Documentation', 'hometard' ); ?></h3>
                            <p class="about"><?php printf(esc_html__('Need any help to setup and configure %s? Please have a look at our documentations instructions.', 'hometard'), $theme_data->Name); ?></p>
                            <p>
                                <a href="<?php echo esc_url( 'https://hometard.com/hometard-theme-documentation/' ); ?>" target="_blank" class="button button-secondary"><?php esc_html_e('Hometard Documentation', 'hometard'); ?></a>
                            </p>
                            <?php do_action( 'hometard_dashboard_theme_links' ); ?>
                        </div>
                        <div class="theme_link">
                            <h3><?php esc_html_e( 'Having Trouble, Need Support?', 'hometard' ); ?></h3>
                            <p class="about"><?php printf(esc_html__('Support for %s WordPress theme is conducted through our contact form.', 'hometard'), $theme_data->Name); ?></p>
                            <p>
                                <a href="<?php echo esc_url('https://hometard.com/#contact' ); ?>" target="_blank" class="button button-secondary"><?php echo sprintf( esc_html('Contact form', 'hometard'), $theme_data->Name); ?></a>
                            </p>
                        </div>
                    </div>

                    <div class="theme_info_right">
                        <img src="<?php echo get_template_directory_uri(); ?>/screenshot.png" alt="Theme Screenshot" />
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if ( $tab == 'actions_required' ) { ?>
            <div class="action-required-tab info-tab-content">

                <?php if ( is_child_theme() ){
                    $child_theme = wp_get_theme();
                    ?>
                    <form method="post" action="<?php echo esc_attr( $current_action_link ); ?>" class="demo-import-boxed copy-settings-form">
                        <p>
                           <strong> <?php printf( esc_html__(  'You\'re using %1$s theme, It\'s a child theme of Hometard', 'hometard' ) ,  $child_theme->Name ); ?></strong>
                        </p>
                        <p><?php printf( esc_html__(  'Child theme uses it\'s own theme setting name, would you like to copy setting data from parent theme to this child theme?', 'hometard' ) ); ?></p>
                        <p>

                        <?php

                        $select = '<select name="copy_from">';
                        $select .= '<option value="">'.esc_html__( 'From Theme', 'hometard' ).'</option>';
                        $select .= '<option value="hometard">Hometard</option>';
                        $select .= '<option value="'.esc_attr( $child_theme->get_stylesheet() ).'">'.( $child_theme->Name ).'</option>';
                        $select .='</select>';

                        $select_2 = '<select name="copy_to">';
                        $select_2 .= '<option value="">'.esc_html__( 'To Theme', 'hometard' ).'</option>';
                        $select_2 .= '<option value="hometard">Hometard</option>';
                        $select_2 .= '<option value="'.esc_attr( $child_theme->get_stylesheet() ).'">'.( $child_theme->Name ).'</option>';
                        $select_2 .='</select>';

                        echo $select . ' to '. $select_2;

                        ?>
                        <input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Copy now', 'hometard' ); ?>">
                        </p>
                        <?php if ( isset( $_GET['copied'] ) && $_GET['copied'] == 1 ) { ?>
                            <p><?php esc_html_e( 'Your settings copied.', 'hometard' ); ?></p>
                        <?php } ?>
                    </form>

                <?php } ?>
                <?php if ( $actions_r['number_active']  > 0 ) { ?>
                    <?php $actions = wp_parse_args( $actions, array( 'page_on_front' => '', 'page_template' ) ) ?>

                    <?php if ( $actions['recommend_plugins'] == 'active' ) {  ?>
                        <div id="plugin-filter" class="recommend-plugins action-required">
                            <a  title="" class="dismiss" href="<?php echo add_query_arg( array( 'hometard_action_notice' => 'recommend_plugins' ), $current_action_link ); ?>">
                                <?php if ( $actions_r['hide_by_click']['recommend_plugins'] == 'hide' ) { ?>
                                    <span class="dashicons dashicons-hidden"></span>
                                <?php } else { ?>
                                    <span class="dashicons  dashicons-visibility"></span>
                                <?php } ?>
                            </a>
                            <h3><?php esc_html_e( 'Recommend Plugins', 'hometard' ); ?></h3>
                            <?php
                            hometard_render_recommend_plugins( $recommend_plugins );
                            ?>
                        </div>
                    <?php } ?>


                    <?php if ( $actions['page_on_front'] == 'active' ) {  ?>
                        <div class="theme_link  action-required">
                            <a title="<?php  esc_attr_e( 'Dismiss', 'hometard' ); ?>" class="dismiss" href="<?php echo add_query_arg( array( 'hometard_action_notice' => 'page_on_front' ), $current_action_link ); ?>">
                                <?php if ( $actions_r['hide_by_click']['page_on_front'] == 'hide' ) { ?>
                                    <span class="dashicons dashicons-hidden"></span>
                                <?php } else { ?>
                                    <span class="dashicons  dashicons-visibility"></span>
                                <?php } ?>
                            </a>
                            <h3><?php esc_html_e( 'Switch "Front page displays" to "A static page"', 'hometard' ); ?></h3>
                            <div class="about">
                                <p><?php _e( 'In order to have the one page look for your website, please go to Customize -&gt; Static Front Page and switch "Front page displays" to "A static page".', 'hometard' ); ?></p>
                            </div>
                            <p>
                                <a  href="<?php echo admin_url('options-reading.php'); ?>" class="button"><?php esc_html_e('Setup front page displays', 'hometard'); ?></a>
                            </p>
                        </div>
                    <?php } ?>

                    <?php if ( $actions['page_template'] == 'active' ) {  ?>
                        <div class="theme_link  action-required">
                            <a  title="<?php esc_attr_e( 'Dismiss', 'hometard' ); ?>" class="dismiss" href="<?php echo add_query_arg( array( 'hometard_action_notice' => 'page_template' ), $current_action_link ); ?>">
                                <?php if ( $actions_r['hide_by_click']['page_template'] == 'hide' ) { ?>
                                    <span class="dashicons dashicons-hidden"></span>
                                <?php } else { ?>
                                    <span class="dashicons dashicons-visibility"></span>
                                <?php } ?>
                            </a>
                            <h3><?php esc_html_e( 'Set your homepage page template to "Homepage".', 'hometard' ); ?></h3>

                            <div class="about">
                                <p><?php esc_html_e( 'In order to change homepage contents, you will need to set template "Homepage" for your homepage.', 'hometard' ); ?></p>
                            </div>
                            <p>
                                <?php
                                $front_page = get_option( 'page_on_front' );
                                if ( $front_page <= 0  ) {
                                    ?>
                                    <a  href="<?php echo admin_url('options-reading.php'); ?>" class="button"><?php esc_html_e('Setup front page displays', 'hometard'); ?></a>
                                    <?php

                                }

                                if ( $front_page > 0 && get_post_meta( $front_page, '_wp_page_template', true ) != 'template-parts/template-homepage.php' ) {
                                    ?>
                                    <a href="<?php echo get_edit_post_link( $front_page ); ?>" class="button"><?php esc_html_e('Change homepage page template', 'hometard'); ?></a>
                                    <?php
                                }
                                ?>
                            </p>
                        </div>
                    <?php } ?>
                    <?php do_action( 'hometard_more_required_details', $actions ); ?>
                <?php  } else { ?>
                    <h3><?php  printf( __( 'Keep update with %s', 'hometard' ) , $theme_data->Name ); ?></h3>
                    <p><?php _e( 'Hooray! There are no required actions for you right now.', 'hometard' ); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <?php do_action( 'hometard_more_tabs_details', $actions ); ?>

    </div> <!-- END .theme_info -->
    <script type="text/javascript">
        jQuery(  document).ready( function( $ ){
            $( 'body').addClass( 'about-php' );

            $( '.copy-settings-form').on( 'submit', function(){
                var c = confirm( '<?php echo esc_attr_e( 'Are you sure want to copy ?', 'hometard' ); ?>' );
                if ( ! c ) {
                    return false;
                }
            } );
        } );
    </script>
    <?php
}
