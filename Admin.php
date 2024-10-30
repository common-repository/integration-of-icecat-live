<?php

namespace Nswintgricecatlive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin
{
    public static $errors = [];
    public static $icLangSelectOptions = [];
    public static $productData = [];
    
    public static function admin_init()
    {
        add_filter( 'plugin_action_links_'.NSWICLV_PLUGIN_BASENANE, array( '\Nswintgricecatlive\Admin', 'plugin_action_links' ) );        
        
        add_filter( 'mce_buttons_2', array('\Nswintgricecatlive\Admin', 'mce_buttons_2') ); 
    
        add_filter( 'mce_external_plugins', array('\Nswintgricecatlive\Admin', 'mce_external_plugins') );              
               
        add_action( 'edit_form_after_editor', array('\Nswintgricecatlive\Admin', 'edit_form_after_editor') );       
        
        add_action( 'enqueue_block_editor_assets', array('\Nswintgricecatlive\Admin', 'enqueue_block_editor_assets_action'), 50 );
        
        add_action( 'woocommerce_loaded', array('\Nswintgricecatlive\Admin', 'woocommerce_loaded') );
        
        add_action('include_tpl',  array('\Nswintgricecatlive\Admin','include_tpl') );
        
        add_action( 'admin_print_scripts', array('\Nswintgricecatlive\Admin', 'admin_inline_js') );
        
        wp_register_style('iclvshtcd_style', plugins_url('admin/css/iclvshtcd.css', __FILE__), false, '1.0.0', 'all');
        wp_enqueue_style( 'iclvshtcd_style' );
    }
    
    public static function plugin_action_links($links)
    {
        $action_links = array(
            'nswiclv_settings' => '<a href="' . admin_url( 'admin.php?page=nswiclv-configure' ) . '" >' . esc_html__( 'Settings', 'integration-of-icecat-live' ) . '</a>',
        );

        return array_merge( $action_links, $links );
        
    }
    
    public static function admin_menu()
    {
        add_options_page( __( 'Icecat Live', 'integration-of-icecat-live' ), __( 'Icecat Live', 'integration-of-icecat-live' ), 'manage_options', 'nswiclv-configure',
            array( '\Nswintgricecatlive\Admin', 'display_page' ) );
    }
    
    
    public static function display_page()
    {       
        if( isset( $_GET['page'] ) && $_GET['page'] == 'nswiclv-configure' ){           
            self::page_configure();        
        }               
    }
    
    public static function page_configure()
    {
        $view_vars = [];
        $pattern_shortcode_value = '[' . NSWICLV_SHORTNAME . ' brand="PRODUCTS_BRAND" mpn="PRODUCTS_MPN" barcode="PRODUCTS_EAN" lang="LANGUAGE_CODE"]';
        
       // $icLangSelectOptions = [];
        foreach( \Nswintgricecatlive\Icecat::$_icecatAcceptedLanguages as $icLangCode => $icLangName ){
            self::$icLangSelectOptions[] = ['value' => $icLangCode, 'label' => $icLangName];
        }
        
        $optionsData = [
            [
                'name' => NSWICLV_SHORTNAME.'_icecat_username',
                'value' => get_option(NSWICLV_SHORTNAME.'_icecat_username'),
                'label' => __('Icecat username', 'integration-of-icecat-live'),
                'required' => true,
                'input_type' => 'text',
                'filter' => 'sanitize_text_field',
                'value_type' => 'string',
                'description' => __('Requests to Icecat will use your username', 'integration-of-icecat-live'),
            ],
            [
                'name' => NSWICLV_SHORTNAME.'_ic_lang_def',
                'value' => get_option(NSWICLV_SHORTNAME.'_ic_lang_def'),
                'label' => __('Icecat language for default Wordpress language', 'integration-of-icecat-live'),
                'required' => true,
                'input_type' => 'select',
                'filter' => 'sanitize_text_field',
                'value_type' => 'string',
                'input_options' => self::$icLangSelectOptions
            ],
            [
                'name' => NSWICLV_SHORTNAME.'_brand_attr',
                'value' => get_option(NSWICLV_SHORTNAME.'_brand_attr'),
                'label' => __('Brand attribute', 'integration-of-icecat-live'),
                'required' => false,
                'input_type' => 'text',
                'filter' => 'sanitize_text_field',
                'value_type' => 'string',
                'description' => __('Woocommerce attribute to get brand name', 'integration-of-icecat-live'),
            ],
            [
                'name' => NSWICLV_SHORTNAME.'_barcode_attr',
                'value' => get_option(NSWICLV_SHORTNAME.'_barcode_attr'),
                'label' => __('Barcode attribute', 'integration-of-icecat-live'),
                'required' => false,
                'input_type' => 'text',
                'filter' => 'sanitize_text_field',
                'value_type' => 'string',
                'description' => __('Woocommerce attribute to get EAN or UPC code', 'integration-of-icecat-live'),
            ],
            
        ];
        
        $view_vars['options'] = $optionsData;
        $view_vars['pattern_shortcode_value'] = $pattern_shortcode_value;
          
        if( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ){
            do_action( 'include_tpl', $view_vars);           
            return;
        }
        if ( isset( $_POST[NSWICLV_SHORTNAME .'_wpnonce'] ) ){
            if( ! wp_verify_nonce( sanitize_text_field( wp_unslash  ( $_POST[NSWICLV_SHORTNAME .'_wpnonce'] ) ), -1) ){
                self::$errors[] = __( 'Invalid form state.', 'integration-of-icecat-live' );
            }
        }
        
        foreach( $optionsData as $oi => $optionCnf ){
            
            $optionValue = isset ( $_POST[ $optionCnf['name'] ] ) ? sanitize_text_field( esc_html( wp_unslash( $_POST[ $optionCnf['name'] ] ) ) ) : '';
            if( isset($optionCnf['filter']) && is_callable($optionCnf['filter']) ){
                $optionValue = call_user_func_array($optionCnf['filter'], [ $optionValue ]);
            }
            
            if( $optionCnf['required'] ){
                if( ($optionCnf['value_type'] == 'string') && empty($optionValue) ){
                    /* translators: %s: option label */
                    self::$errors[] = sprintf( __('Value of %s is required and can not be empty', 'integration-of-icecat-live' ), $optionCnf['label'] );
                    continue;
                }
            }
            
            $optionsData[$oi]['value'] = $optionValue;
        }
        
        $view_vars['pattern_shortcode_value'] = $pattern_shortcode_value;
        
        if( count(self::$errors) ){
            $view_vars['errors'] = self::$errors;
            $view_vars['options'] = $optionsData;

            do_action( 'include_tpl', $view_vars); 
           
            return;
        }
        
        foreach( $optionsData as $oi => $optionCnf ){
            update_option($optionCnf['name'], $optionCnf['value']);
            $optionsData[$oi]['value'] = get_option($optionCnf['name']);
        }
        
        $view_vars['success'] = true;
        $view_vars['options'] = $optionsData;
       
        do_action( 'include_tpl', $view_vars);       
        
    }

    public static function include_tpl($view_vars)
    {
        include NSWICLV_PLUGIN_DIR . '/views/configure.phtml';
    }   
    
    public static function mce_buttons_2($buttons)
    {
        //if ( ! in_array( "iclvshtcd", $buttons ) ) { 
            $buttons[] = 'iclvshtcd'; 
        //}
        return $buttons;
    }
    
    public static function mce_external_plugins($external_plugins)
    {
        $external_plugins['iclvshtcd'] = plugins_url('', __FILE__) . '/tinymce-plugins/iclvshtcd-4.js';      
        return $external_plugins;
    }

    /**
     * 
     * @param WP_Post $post
     */
    public static function edit_form_after_editor($post)
    {    
        //$icLangSelectOptions = [];
        foreach( \Nswintgricecatlive\Icecat::$_icecatAcceptedLanguages as $icLangCode => $icLangName ){
            self::$icLangSelectOptions[] = ['value' => $icLangCode, 'text' => $icLangName];
        }
        
        if( $post->post_type == 'product' ){
            $woProdFactory = new \WC_Product_Factory();
            $woProduct = $woProdFactory->get_product( $post->ID );
            
            $brand = null;
            $brandAttrName = get_option(NSWICLV_SHORTNAME.'_brand_attr');
            if( !empty($brandAttrName) ){
                $brand = $woProduct->get_attribute($brandAttrName);
            }
            
            $barcode = null;
            $barcodeAttrName = get_option(NSWICLV_SHORTNAME.'_barcode_attr');
            if( !empty($barcodeAttrName) ){
                $barcode = $woProduct->get_attribute($barcodeAttrName);
            }
            
            self::$productData = [
                'language' => get_option(NSWICLV_SHORTNAME.'_ic_lang_def'),
                'mpn' => $woProduct->get_sku(),
                'brand' => $brand ?? '',
                'barcode' => $barcode ?? ''               
            ];
            
           // echo '<script type="text/javascript">'. esc_js('var nswiclv_languages = '). wp_json_encode($icLangSelectOptions) .';'. esc_js(' var nswiclv_product = ') . wp_json_encode($productData) . '</script>';
           
            //do_action( 'admin_print_scripts' );
        }
       /* else{
            //echo '<script type="text/javascript">'. esc_js('var nswiclv_languages = '). wp_json_encode($icLangSelectOptions) . ';</script>';
            
        }*/
        
        do_action( 'admin_print_scripts' );
    }
    
    public static function enqueue_block_editor_assets_action() 
    {
        //add_action( 'admin_enqueue_scripts', array('\Nswicecatlive\Admin', 'nswiclv_scripts') ); 
        foreach( \Nswintgricecatlive\Icecat::$_icecatAcceptedLanguages as $icLangCode => $icLangName ){
            self::$icLangSelectOptions[] = ['value' => $icLangCode, 'text' => $icLangName];
        }
    }
    
    /*public static function nswiclv_scripts()
    {
        foreach( \Nswicecatlive\Icecat::$_icecatAcceptedLanguages as $icLangCode => $icLangName ){
            self::$icLangSelectOptions[] = ['value' => $icLangCode, 'text' => $icLangName];
        }
       
    }*/
    
    public function admin_inline_js()
    {
        echo "<script type='text/javascript'>\n";
        echo 'var nswiclv_languages = '. wp_json_encode(self::$icLangSelectOptions) .';';
        if ( isset( self::$productData ) && is_array( self::$productData )){
            echo 'var nswiclv_product = '. wp_json_encode( self::$productData ) .';';
        }
        echo "\n</script>";
    } 

}

