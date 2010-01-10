<?php
/*
Plugin Name: CC Figure
Plugin URI: 
Description: 
Author: Nils Dagsson Moskopp (erlehmann)
Author URI: http://dieweltistgarnichtso.net/
Version: 0.5
*/

/*  Copyright 2009  Nils Dagsson Moskopp (erlehmann)
    (email : nils+cc-figure@dieweltistgarnichtso.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* install and uninstall */

// create database entry on install
function cc_figure_register_settings() {
    register_setting('cc_figure_options', 'cc_figure_css');
    register_setting('cc_figure_options', 'cc_figure_metadata_standard');
    update_option( 'figure_metadata_standard', 'microdata' );
}

// delete database entry on uninstall
function cc_figure_uninstall(){
    delete_option('cc_figure_css');
    delete_option('cc_figure_metadata_standard');
}

// uninstall hooks
register_uninstall_hook(__FILE__, 'cc_figure_uninstall');
register_deactivation_hook(__FILE__, 'cc_figure_uninstall');

/* actual functionality */

// define quicktag button
function cc_figure_add_quicktag() {
    if (strpos($_SERVER['REQUEST_URI'], 'post.php') ||
        strpos($_SERVER['REQUEST_URI'], 'post-new.php') ||
        strpos($_SERVER['REQUEST_URI'], 'page-new.php') ||
        strpos($_SERVER['REQUEST_URI'], 'bookmarklet.php')) {
?>

<script type="text/javascript">
//    <![CDATA[

var cc_figure_toolbar = document.getElementById("ed_toolbar");

if (cc_figure_toolbar) {
    var cc_figure_button = document.createElement('input');

    cc_figure_button.type = 'button';
    cc_figure_button.value = 'CC Figure';
    cc_figure_button.onclick = cc_figure_button_pressed;
    cc_figure_button.className = 'ed_button';
    cc_figure_button.title = 'CC Figure';
    cc_figure_button.id = 'ed_Pluggy';

    cc_figure_toolbar.appendChild(cc_figure_button);
}

function cc_figure_button_pressed(querystr) {
    var url = "<?php
        $url = get_bloginfo('url');
        if ($_SERVER["HTTPS"] == "on") { $url = str_ireplace("http://", "https://", $url); };
        echo $url;
        ?>/wp-content/plugins/cc-figure/cc-figure-generator-<?php $microdata = get_option("cc_figure_metadata_standard");
        echo $microdata;
        ?>.xhtml";

    var name = "cc_figure_popup";
    var w = 540;
    var h = 428;
    var valLeft = (screen.width) ? (screen.width-w)/2 : 0;
    var valTop = (screen.height) ? (screen.height-h)/2 : 0;
    var features = "width="+w+",height="+h+",left="+valLeft+",top="+valTop+",locationbar=0,menubar=0,personalbar=0,resizable=0,scrollbars=0";
//    var features = "width="+w+",height="+h+",locationbar=0,menubar=0,personalbar=0,resizable=0,scrollbars=0";
    var cc_figure_window = window.open(url, name, features);
    cc_figure_window.focus();

    return false;
}

//    ]]>
</script>

<?php
    }
}

function cc_figure_add_css() {
    if (get_option('cc_figure_css')) {
        echo '<link rel="stylesheet" href="' . bloginfo('url') . '/wp-content/plugins/cc-figure/css/'.get_option('cc_figure_css').'" type="text/css"/>';
    }
}

// add button to editor
add_filter('admin_footer', 'cc_figure_add_quicktag');

// add CSS to all pages
add_action('wp_head', 'cc_figure_add_css');

/* admin thingys follow */

// admin menu entry
function cc_figure_plugin_menu() {
    add_options_page('CC Figure', 'CC Figure', 8, __FILE__, 'cc_figure_plugin_admin');
}

// actual admin page
function cc_figure_plugin_admin() {
    echo '
<style type="text/css" scoped="scoped">
    img { vertical-align: middle; }
    label { display: inline-block; min-width: 160px; }
</style>

<div class="wrap">

    <form method="post" action="options.php">';
        settings_fields('cc_figure_options');
    echo '
        <h2>Stylesheet</h2>
        <p>
            Ein Stylesheet beeinflusst das Aussehen des Lizenzhinweises.
        </p>
        <ul>'.cc_figure_admin_css().'
            <li>
                <label><input type="radio" name="cc_figure_css" value="" '.cc_figure_admin_checked("cc_figure_css","").' /> Kein Stylesheet</label>
            </li>
        </ul>

        <div class="submit">
            <input type="submit" class="button-primary" value="'._('Save Changes').'" />
        </div>

        <h2>Metadata-Standard</h2>
        <p>
            Ändere die Einstellungen zum Metadata-Standard nur, wenn du weißt, was du tust.
        </p>
        <ul>
            <li>
                <label><input type="radio" name="cc_figure_metadata_standard" value="microdata" '.cc_figure_admin_checked("cc_figure_metadata_standard","").' '.cc_figure_admin_checked("cc_figure_metadata_standard","microdata").' /> Microdata (HTML5, XHTML5)</label>
            </li>
            <li>
                <label><input type="radio" name="cc_figure_metadata_standard" value="rdfa" '.cc_figure_admin_checked("cc_figure_metadata_standard","rdfa").'/> RDFa (XHTML only)</label>
            </li>
        </ul>

    </form>
</div>';
}

// list of CSS files
function cc_figure_admin_css() {
    $inputs = '';
    $current = get_option('cc_figure_css');
    $path = '../wp-content/plugins/cc-figure/css/';
    $handle = opendir($path);
    if($handle){
        while (false !== ($file = readdir($handle))) {
            if($file !== '.' && $file !== '..'){
                $inputs .= '<li>';
                $inputs .= '<label><input type="radio" name="cc_figure_css" value="'.$file.'"';
                $inputs .= ($file == $current) ? ' checked="checked"' : '';
                $inputs .= ' /> '.$file.'</label>';
                $inputs .= ' <img src="'.substr($path, 0, -4).'preview/'.substr($file, 0, -3).'png"/>';
                $inputs .= "</li>\n";
            }
        }
    }
    closedir($handle);
    return $inputs;
}

// output checked attribute if appropriate
function cc_figure_admin_checked($key, $value) {
    if (get_option($key) == $value) {
        return 'checked="checked"';
    }
}

// add admin pages
if(is_admin()){
    add_action('admin_menu', 'cc_figure_plugin_menu');
    add_action( 'admin_init', 'cc_figure_register_settings');
}

?>
