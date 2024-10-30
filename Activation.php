<?php

namespace Nswintgricecatlive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activation
{
    public static function activate()
    {
        add_option( NSWICLV_SHORTNAME.'_icecat_username' );
        add_option( NSWICLV_SHORTNAME.'_ic_lang_def' );
        add_option( NSWICLV_SHORTNAME.'_brand_attr' );
        add_option( NSWICLV_SHORTNAME.'_barcode_attr' );
    }
    
    public static function deactivate()
    {
        delete_option( NSWICLV_SHORTNAME.'_icecat_username' );
        delete_option( NSWICLV_SHORTNAME.'_ic_lang_def' );
        delete_option( NSWICLV_SHORTNAME.'_brand_attr' );
        delete_option( NSWICLV_SHORTNAME.'_barcode_attr' );
    }
}

