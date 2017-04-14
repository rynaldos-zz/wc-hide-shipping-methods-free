<?php

/*
 Plugin Name: WC Hide Shipping Methods
 Plugin URI: https://profiles.wordpress.org/rynald0s
 Description: This plugin, when enabled hides all other shipping methods when "Free shipping" is available during checkout. It includes a setting to let you keep local pickup option, when "Free shipping" is available.
 Author: Rynaldo Stoltz
 Author URI: https://github.com/rynaldos
 Version: 1.0
 License: GPLv3 or later License
 URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

/**
 * Add settings
 */

add_filter( 'woocommerce_get_settings_shipping','rs_woo_account_settings', 10, 2 );
function rs_woo_account_settings( $settings ) {
    
    /**
     * Check the current section is what we want
     **/

        $settings[] = array( 'title' => __( 'Hide shipping methods', 'woocommerce' ), 'type' => 'title', 'id' => 'wc_hide_shipping' );


        $settings[] = array(
                'title'    => __( 'When "Free Shipping" is available during checkout: ', 'woocommerce' ),
                'desc'     => __( '', 'woocommerce' ),
                'id'       => 'wc_hide_shipping_options',
                'type'     => 'radio',
                'desc_tip' => true,
                'options'  => array( 'hide_all' => 'Hide all other shipping methods and only show "Free Shipping"', 'hide_except_local' => 'Hide all other shipping methods and only show "Free Shipping" and "Local Pickup" ' ),
            );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'wc_hide_shipping' );
        return $settings;

}

if ( get_option( 'wc_hide_shipping_options' ) == 'hide_all' ) {

add_filter( 'woocommerce_package_rates', 'wc_hide_shipping_when_free_is_available', 10, 2 ); 

function wc_hide_shipping_when_free_is_available( $rates ) {
    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate -> method_id ) {
            $free[$rate_id] = $rate;
            break;
        }
    }
    return !empty( $free ) ? $free : $rates;
    }
}

if ( get_option( 'wc_hide_shipping_options') == 'hide_except_local' ) {

add_filter( 'woocommerce_package_rates', 'wc_hide_shipping_when_free_is_available_keep_local', 10, 2 ); 

function wc_hide_shipping_when_free_is_available_keep_local( $rates, $package ) {
    $new_rates = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate -> method_id ) {
            $new_rates[ $rate_id ] = $rate;
            break;
        }
    }

    if ( ! empty( $new_rates ) ) {
        foreach ( $rates as $rate_id => $rate ) {
            if ('local_pickup' === $rate->method_id ) {
                $new_rates[ $rate_id ] = $rate;
                break;
            }
        }
        return $new_rates;
    }

    return $rates;
              }
        }
}

function rs_update_default_option(){
    update_option( 'wc_hide_shipping_options', 'hide_all' );
}

register_activation_hook( __FILE__, 'rs_update_default_option' );


