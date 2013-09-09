<?php
/*
 * Plugin Name: Content Scroller
 * Plugin URI: http://orange35.com/plugins
 * Description: A brief description of the Plugin.
 * Version: 1.0
 * Author: Name Of The Plugin Author
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: A "Slug" license name e.g. GPL2
 *
 * @TODO: nav title hints
 * */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'CONTENT_SCROLLER_VERSION', '1.0' );
require_once dirname( __FILE__ ) . '/defines.php';

if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/admin.php';
}

function content_scroller_styles() {
    $stylesheet = plugin_dir_url(__FILE__) . 'css/jquery.contentScroller.css';
    wp_enqueue_style( 'core', $stylesheet, false );
}
add_action( 'wp_enqueue_scripts', 'content_scroller_styles' );

function content_scroller_scripts() {
    $script = plugin_dir_url(__FILE__) . 'js/jquery.contentScroller.js';
    wp_enqueue_script( 'content-scroller-core', $script, array( 'jquery-core' ) );
}
add_action( 'wp_enqueue_scripts', 'content_scroller_scripts' );

function content_scroller_head() {
    $nav_type = content_scroller_get_current_nav_type();
    $truncate_len = content_scroller_get_current_nav_truncate_len();

    if ( $nav_type == CONTENT_SCROLLER_NAV_TYPE_DATE ) {
        $titleFunction = '
            function (index, itemNode) {
                var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
                var entryDate = $(".entry-date", itemNode).attr("datetime");
                if (entryDate) {
                    entryDate = new Date(entryDate);
                    return monthNames[entryDate.getMonth()] + " " + entryDate.getDate() + ", " + entryDate.getFullYear();
                }
                return null;
            }
        ';
    } else if ( $nav_type == CONTENT_SCROLLER_NAV_TYPE_TITLE ) {
        $titleFunction = '
            function (index, itemNode) {
                var entryTitle = $(".entry-title", itemNode).html();
                entryTitle = entryTitle.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, "");
                entryTitle = entryTitle.replace(/(^\s+)|(\s+$)/g, "");
                if (entryTitle) {
                    if (' . $truncate_len . ' >= entryTitle.length) {
                        return entryTitle;
                    } else {
                        var len = entryTitle.substring(' . $truncate_len . ').search(/\W/);
                        len = (len >= 0) ? (len + ' . $truncate_len . ') : len;
                        return entryTitle.substring(0, len) + "...";
                    }
                }
                return null;
            }
        ';
    } else {
        $titleFunction = '
            function (index, itemNode) {
                return null;
            }
        ';
    }

    $script = '
                var scrollerOptions = {};

                if ($("#wpadminbar").length) {
                    if (scrollerOptions.nav == undefined) {
                        scrollerOptions.nav = {};
                    }
                    scrollerOptions.nav["topClass"] = "cs-top-wpadmin";
                }

                if (scrollerOptions.navItem == undefined) {
                    scrollerOptions.navItem = {};
                }
                scrollerOptions.navItem["title"] = ' . $titleFunction . '

                $("#content > .post").contentScroller(scrollerOptions);
    ';

    $script = '<script type="text/javascript">jQuery(function ($) { ' . $script . ' });</script>';
    echo $script;
}
add_action( 'wp_head', 'content_scroller_head' );

?>