<?php
/**
 * seed_csp4 Admin
 *
 * @package WordPress
 * @subpackage seed_csp4
 * @since 0.1.0
 */


class SEED_CSP4_ADMIN
{
    public $plugin_version = SEED_CSP4_VERSION;
    public $plugin_name = SEED_CSP4_PLUGIN_NAME;

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;
    
   /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;
    
    /**
     * Load Hooks
     */
    function __construct( )
    {
        if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
            add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts'  ) );
            add_action( 'admin_menu', array( &$this, 'create_menus'  ) );
            add_action( 'admin_init', array( &$this, 'reset_defaults' ) );
            add_action( 'admin_init', array( &$this, 'create_settings' ) );
            add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
        }  
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }
    
    /**
     * Reset the settings page. Reset works per settings id.
     *
     */
    function reset_defaults( )
    {
        if ( isset( $_POST[ 'seed_csp4_reset' ] ) ) {
            $option_page = $_POST[ 'option_page' ];
            check_admin_referer( $option_page . '-options' );
            require_once(SEED_CSP4_PLUGIN_PATH.'inc/default-settings.php');

            $_POST[ $_POST[ 'option_page' ] ] = $seed_csp4_settings_deafults[$_POST[ 'option_page' ]];
            add_settings_error( 'general', 'seed_csp4-settings-reset', __( "Settings reset." ), 'updated' );
        }  
    }
    
    /**
     * Properly enqueue styles and scripts for our theme options page.
     *
     * This function is attached to the admin_enqueue_scripts action hook.
     *
     * @since  0.1.0
     * @param string $hook_suffix The name of the current page we are on.
     */
    function admin_enqueue_scripts( $hook_suffix )
    {
        if ( $hook_suffix != $this->plugin_screen_hook_suffix )
            return;
        
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'seed_csp4-framework-js', SEED_CSP4_PLUGIN_URL . 'framework/settings-scripts.js', array( 'jquery' ), $this->plugin_version );
        wp_enqueue_script( 'theme-preview' );
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_style( 'media-upload' );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'seed_csp4-framework-css', SEED_CSP4_PLUGIN_URL . 'framework/settings-style.css', false, $this->plugin_version );
        wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', false, $this->plugin_version );
    }
    
    /**
     * Creates WordPress Menu pages from an array in the config file.
     *
     * This function is attached to the admin_menu action hook.
     *
     * @since 0.1.0
     */
    function create_menus( )
    {
      $this->plugin_screen_hook_suffix = add_options_page( 
            __( "Coming Soon", 'coming-soon' ), 
            __( "Coming Soon", 'coming-soon' ), 
            'manage_options', 
            'seed_csp4', 
            array( &$this , 'option_page' )
            );
    }
    
    /**
     * Display settings link on plugin page
     */
    function plugin_action_links( $links, $file )
    {
        $plugin_file = SEED_CSP4_SLUG;

        if ( $file == $plugin_file ) {
            $settings_link = '<a href="options-general.php?page=seed_csp4">Settings</a>';
            array_unshift( $links, $settings_link );
        }
        return $links;
    }
    
    
    /**
     * Allow Tabs on the Settings Page
     *
     */
    function plugin_options_tabs( )
    {
        $menu_slug   = null;
        $page        = $_REQUEST[ 'page' ];
        $uses_tabs   = false;
        $current_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : false;
        
        //Check if this config uses tabs
        foreach ( seed_csp4_get_options() as $v ) {
            if ( $v[ 'type' ] == 'tab' ) {
                $uses_tabs = true;
                break;
            }
        }
        
        // If uses tabs then generate the tabs
        if ( $uses_tabs ) {
            echo '<h2 class="nav-tab-wrapper" style="padding-left:20px">';
            $c = 1;
            foreach ( seed_csp4_get_options() as $v ) {
                    if ( isset( $v[ 'menu_slug' ] ) ) {
                        $menu_slug = $v[ 'menu_slug' ];
                    }
                    if ( $menu_slug == $page && $v[ 'type' ] == 'tab' ) {
                        $active = '';
                        if ( $current_tab ) {
                            $active = $current_tab == $v[ 'id' ] ? 'nav-tab-active' : '';
                        } elseif ( $c == 1 ) {
                            $active = 'nav-tab-active';
                        }
                        echo '<a class="nav-tab ' . $active . '" href="?page=' . $menu_slug . '&tab=' . $v[ 'id' ] . '">' . $v[ 'label' ] . '</a>';
                        $c++;
                    }
            }
            echo '<a class="nav-tab seed_csp4-preview thickbox-preview" href="'.home_url().'?cs_preview=true&TB_iframe=true&width=640&height=632" title="'.__('&larr; Close Window','coming-soon').'">'.__('Live Preview','coming-soon').'</a>';
            if(defined('SEED_CSP_API_KEY') === false){
                echo '<a class="nav-tab seed_csp4-support" style="color: #8a6d3b;background-color: #fcf8e3;float:right" href="http://www.seedprod.com/features/?utm_source=coming-soon-plugin&utm_medium=banner&utm_campaign=coming-soon-link-in-plugin" target="_blank"><i class="fa fa-star"></i> '.__('Upgrade to the Pro Version','coming-soon').'</a>';
            }
            echo '</h2>';

        }
    }
    
    /**
     * Get the layout for the page. classic|2-col
     *
     */
    function get_page_layout( )
    {
        $layout = 'classic';
        foreach ( seed_csp4_get_options() as $v ) {
            switch ( $v[ 'type' ] ) {
                case 'menu';
                    $page = $_REQUEST[ 'page' ];
                    if ( $page == $v[ 'menu_slug' ] ) {
                        if ( isset( $v[ 'layout' ] ) ) {
                            $layout = $v[ 'layout' ];
                        }
                    }
                    break;
            }
        }
        return $layout;
    }
    
    /**
     * Render the option pages.
     *
     * @since 0.1.0
     */
    function option_page( )
    {
        $menu_slug = null;
        $page   = $_REQUEST[ 'page' ];
        $layout = $this->get_page_layout();
        ?>
        <div class="wrap columns-2 seed-csp4">
        <?php screen_icon(); ?>
            <h2><?php echo $this->plugin_name; ?> <span class="seed_csp4-version"> <?php echo SEED_CSP4_VERSION; ?></span></h2>
            <?php //settings_errors() ?>
            <?php $this->plugin_options_tabs(); ?>
            <?php if ( $layout == '2-col' ): ?>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content" >
            <?php endif; ?>
                    <?php if(!empty($_GET['tab']))
                            do_action( 'seed_csp4_render_page', array('tab'=>$_GET['tab'])); 
                    ?>
                    <form action="options.php" method="post">
                    <p>
                    <!-- <input name="submit" type="submit" value="<?php _e( 'Save All Changes', 'coming-soon' ); ?>" class="button-primary"/> -->
                    <?php if(!empty($_GET['tab']) && $_GET['tab'] != 'seed_csp4_tab_3') { ?>
                    <!-- <input id="reset" name="reset" type="submit" value="<?php _e( 'Reset Settings', 'coming-soon' ); ?>" class="button-secondary"/>     -->
                    <?php } ?>
                    </p>
                            <?php
                            $show_submit = false;
                            foreach ( seed_csp4_get_options() as $v ) {
                                if ( isset( $v[ 'menu_slug' ] ) ) {
                                    $menu_slug = $v[ 'menu_slug' ];
                                }
                                    if ( $menu_slug == $page ) {
                                        switch ( $v[ 'type' ] ) {
                                            case 'menu';
                                                break;
                                            case 'tab';
                                                $tab = $v;
                                                if ( empty( $default_tab ) )
                                                    $default_tab = $v[ 'id' ];
                                                break;
                                            case 'setting':
                                                $current_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $default_tab;
                                                if ( $current_tab == $tab[ 'id' ] ) {
                                                    settings_fields( $v[ 'id' ] );
                                                    $show_submit = true;
                                                }
        
                                                break;
                                            case 'section':
                                                $current_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $default_tab;
                                                if ( $current_tab == $tab[ 'id' ] or $current_tab === false ) {
                                                    if ( $layout == '2-col' ) {
                                                        echo '<div id="'.$v[ 'id' ].'" class="postbox seedprod-postbox">';
                                                        $this->do_settings_sections( $v[ 'id' ],$show_submit );
                                                        echo '</div>';
                                                    } else {
                                                        do_settings_sections( $v[ 'id' ] );
                                                    }
                                                                      
                                                }
                                                break;
                                                
                                        }
                                  
                                }
                            }
                        ?>
                    <?php if($show_submit): ?>
                    <p>
                    <!-- <input name="submit" type="submit" value="<?php _e( 'Save All Changes', 'coming-soon' ); ?>" class="button-primary"/> -->
                    <!-- <input id="reset" name="reset" type="submit" value="<?php _e( 'Reset Settings', 'coming-soon' ); ?>" class="button-secondary"/> -->    
                    </p>
                    <?php endif; ?>
                    </form> 

                    <?php if ( $layout == '2-col' ): ?> 
                    </div> <!-- #post-body-content -->

                
                </div> <!-- #post-body --> 
            </div> <!-- #poststuff --> 
            <?php endif; ?>
        </div> <!-- .wrap -->

        <!-- JS login to confirm setting resets. -->    
        <script>
            jQuery(document).ready(function($) {
                $('#reset').click(function(e){
                    if(!confirm('<?php _e( 'This tabs settings be deleted and reset to the defaults. Are you sure you want to reset?', 'coming-soon' ); ?>')){
                        e.preventDefault();
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Create the settings options, sections and fields via the WordPress Settings API
     *
     * This function is attached to the admin_init action hook.
     *
     * @since 0.1.0
     */
    function create_settings( )
    {
        foreach ( seed_csp4_get_options() as $k => $v ) {

            switch ( $v[ 'type' ] ) {
                case 'menu':
                    $menu_slug = $v[ 'menu_slug' ];

                    break;
                case 'setting':
                    if ( empty( $v[ 'validate_function' ] ) ) {
                        $v[ 'validate_function' ] = array(
                             &$this,
                            'validate_machine' 
                        );
                    }
                    register_setting( $v[ 'id' ], $v[ 'id' ], $v[ 'validate_function' ] );
                    $setting_id = $v[ 'id' ];
                    break;
                case 'section':
                    if ( empty( $v[ 'desc_callback' ] ) ) {
                        $v[ 'desc_callback' ] = array(
                             &$this,
                            '__return_empty_string' 
                        );
                    } else {
                        $v[ 'desc_callback' ] = $v[ 'desc_callback' ];
                    }
                    add_settings_section( $v[ 'id' ], $v[ 'label' ], $v[ 'desc_callback' ], $v[ 'id' ] );
                    $section_id = $v[ 'id' ];
                    break;
                case 'tab':
                    break;
                default:
                    if ( empty( $v[ 'callback' ] ) ) {
                        $v[ 'callback' ] = array(
                             &$this,
                            'field_machine' 
                        );
                    }
                    
                    add_settings_field( $v[ 'id' ], $v[ 'label' ], $v[ 'callback' ], $section_id, $section_id, array(
                         'id' => $v[ 'id' ],
                        'desc' => ( isset( $v[ 'desc' ] ) ? $v[ 'desc' ] : '' ),
                        'setting_id' => $setting_id,
                        'class' => ( isset( $v[ 'class' ] ) ? $v[ 'class' ] : '' ),
                        'type' => $v[ 'type' ],
                        'default_value' => ( isset( $v[ 'default_value' ] ) ? $v[ 'default_value' ] : '' ),
                        'option_values' => ( isset( $v[ 'option_values' ] ) ? $v[ 'option_values' ] : '' ) 
                    ) );
                    
            }
        }
    }
    
    /**
     * Create a field based on the field type passed in.
     *
     * @since 0.1.0
     */
    function field_machine( $args )
    {
        extract( $args ); //$id, $desc, $setting_id, $class, $type, $default_value, $option_values
        
        // Load defaults
        $defaults = array( );
        foreach ( seed_csp4_get_options() as $k ) {
            switch ( $k[ 'type' ] ) {
                case 'setting':
                case 'section':
                case 'tab':
                    break;
                default:
                    if ( isset( $k[ 'default_value' ] ) ) {
                        $defaults[ $k[ 'id' ] ] = $k[ 'default_value' ];
                    }
            }
        }
        $options = get_option( $setting_id );
        
        $options = wp_parse_args( $options, $defaults );
        
        $path = SEED_CSP4_PLUGIN_PATH . 'framework/field-types/' . $type . '.php';
        if ( file_exists( $path ) ) {
            // Show Field
            include( $path );
            // Show description
            if ( !empty( $desc ) ) {
                echo "<small class='description'>{$desc}</small>";
            }
        }
        
    }
    
    /**
     * Validates user input before we save it via the Options API. If error add_setting_error
     *
     * @since 0.1.0
     * @param array $input Contains all the values submitted to the POST.
     * @return array $input Contains sanitized values.
     * @todo Figure out best way to validate values.
     */
    function validate_machine( $input )
    {
        $option_page = $_POST['option_page'];
        foreach ( seed_csp4_get_options() as $k ) {
            switch ( $k[ 'type' ] ) {
                case 'menu':
                case 'setting':
                    if(isset($k['id']))
                        $setting_id = $k['id'];
                case 'section':
                case 'tab';
                    break;
                default:
                    if ( !empty( $k[ 'validate' ] ) && $setting_id == $option_page ) {
                        $validation_rules = explode( ',', $k[ 'validate' ] );

                        foreach ( $validation_rules as $v ) {
                            $path = SEED_CSP4_PLUGIN_PATH . 'framework/validations/' . $v . '.php';
                            if ( file_exists( $path ) ) {
                                // Defaults Values
                                $is_valid  = true;
                                $error_msg = '';

                                // Test Validation
                                include( $path );
                                
                                // Is it valid?
                                if ( $is_valid === false ) {
                                    add_settings_error( $k[ 'id' ], 'seedprod_error', $error_msg, 'error' );
                                    // Unset invalids
                                    unset( $input[ $k[ 'id' ] ] );
                                }
                                
                            }
                        } //end foreach
                        
                    }
            }
        }
        
        return $input;
    }
    
    /**
     * Dummy function to be called by all sections from the Settings API. Define a custom function in the config.
     *
     * @since 0.1.0
     * @return string Empty
     */
    function __return_empty_string( )
    {
        echo '';
    }
    
    
    /**
     * SeedProd version of WP's do_settings_sections
     *
     * @since 0.1.0
     */
    function do_settings_sections( $page, $show_submit )
    {
        global $wp_settings_sections, $wp_settings_fields;
        
        if ( !isset( $wp_settings_sections ) || !isset( $wp_settings_sections[ $page ] ) )
            return;
        
        foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
            echo "<h3><span></span>{$section['title']}</h3>\n";
            echo '<div class="inside">';
            call_user_func( $section[ 'callback' ], $section );
            if ( !isset( $wp_settings_fields ) || !isset( $wp_settings_fields[ $page ] ) || !isset( $wp_settings_fields[ $page ][ $section[ 'id' ] ] ) )
                continue;
            echo '<table class="form-table">';
            $this->do_settings_fields( $page, $section[ 'id' ] );
            echo '</table>';
            if($show_submit): ?>
                <p>
                <input name="submit" type="submit" value="<?php _e( 'Save All Changes', 'coming-soon' ); ?>" class="button-primary"/> 
                </p>
            <?php endif;
            echo '</div>';
        }
    }

    function do_settings_fields($page, $section) {
          global $wp_settings_fields;
      
          if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
              return;
      
          foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
              echo '<tr valign="top">';
              if ( !empty($field['args']['label_for']) )
                  echo '<th scope="row"><label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label></th>';
              else
                  echo '<th scope="row"><strong>' . $field['title'] . '</strong><!--<br>'.$field['args']['desc'].'--></th>';
              echo '<td>';
              call_user_func($field['callback'], $field['args']);
              echo '</td>';
              echo '</tr>';
          }
      }
    
}

