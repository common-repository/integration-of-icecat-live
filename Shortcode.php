<?php

namespace Nswintgricecatlive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode
{
    public static $datasheetIdentifiers = array();
    
    public static function init()
    {
        add_shortcode('nswiclv', array( '\Nswintgricecatlive\Shortcode', 'icecat_live' ));
        
        add_action( 'admin_print_scripts', array('\Nswintgricecatlive\Shortcode', 'admin_inline_js_shortcode') );
        
        wp_register_script( 'nswiclv-live-icecat', 'https://live.icecat.biz/js/live-current-2.js');
	    wp_enqueue_script( 'nswiclv-live-icecat' );
    }
    
    public static function icecat_live($atts = [])
    {
        // normalize attribute keys, lowercase
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );
        $defaults = ['lang' => '', 'mpn' => '', 'brand' => '', 'barcode' => '', 'icid' => ''];
        $atts = array_merge($defaults, $atts);
        $icLangCode = trim($atts['lang']);
        if( !in_array($icLangCode, array_keys(\Nswintgricecatlive\Icecat::$_icecatAcceptedLanguages)) ){
            return null;
        }
        
        //$datasheetIdentifiers = [];
        $mpn = trim( filter_var($atts['mpn']) );
        $brand = trim( filter_var($atts['brand']) );
        $barcode = trim( preg_replace('#^[^\d]+$#', '', $atts['barcode']) );
        $icProductId = intval($atts['icid']);
        
        if( !empty( $mpn ) && !empty( $brand ) ){
            self::$datasheetIdentifiers['ProductCode'] = $mpn;
            self::$datasheetIdentifiers['Brand'] = $brand;
            //$datasheetIdentifiers['ProductCode'] = $mpn;
            //$datasheetIdentifiers['Brand'] = $brand;
        }
        elseif( !empty( $barcode ) ){
            self::$datasheetIdentifiers['GTIN'] = $barcode;
            //$datasheetIdentifiers['GTIN'] = $barcode;
        }
        elseif( !empty( $icProductId ) ){
            self::$datasheetIdentifiers['IcecatProductId'] = $icProductId;
           // $datasheetIdentifiers['IcecatProductId'] = $icProductId;
        }
        
        if( !count( self::$datasheetIdentifiers ) ){
            return null;
        }
        
        //$datasheetIdentifiers['UserName'] = get_option(NSWICLV_SHORTNAME.'_icecat_username');
        //$datasheetIdentifiers['icLangCode'] = $icLangCode;
        self::$datasheetIdentifiers['UserName'] = get_option(NSWICLV_SHORTNAME.'_icecat_username');
        
        //do_action( 'admin_print_scripts' );
        
        $html = apply_filters( 'admin_print_scripts', $icLangCode );
        /*$html .= 
            '<script type="text/javascript">'
            .'setTimeout(function(){'
                .'IcecatLive.getDatasheet("#IcecatLive", '. wp_json_encode($datasheetIdentifiers) .', "'. $icLangCode .'")'
            .'}, 200);'
            .'</script>'
        ;*/
        return $html;
        
    }
    
    public function admin_inline_js_shortcode( $icLangCode )
    {
        if( empty( self::$datasheetIdentifiers ) || count( self::$datasheetIdentifiers ) === 0 ){
            return;
        }
        else{
           
            $html = '';
            $html .= '<div id="IcecatLive">Here is short code</div>';
            
            $html .= "<script type='text/javascript'>\n";
            $html .= 'setTimeout(function(){'
                .'IcecatLive.getDatasheet("#IcecatLive", '. wp_json_encode( self::$datasheetIdentifiers ) .', "'. $icLangCode .'")'
                .'}, 200)';
           
            $html .= "\n</script>";
            
            return  $html;
        }
    } 
}

