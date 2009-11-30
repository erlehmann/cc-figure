<?php
/*
Plugin Name: CC Figure
Plugin URI: 
Description: 
Author: Nils Dagsson Moskopp (erlehmann)
Author URI: http://dieweltistgarnichtso.net/
Version: 0.3.3
*/

/* install and uninstall */

// create database entry on install
function cc_figure_register_settings() {
    register_setting('cc_figure_options', 'cc_figure_css');
    // TODO: add default styling ?
}

// delete database entry on uninstall
function cc_figure_uninstall(){
    delete_option('cc_figure_css');
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
// <![CDATA[

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
    var url = "<?php bloginfo('url'); ?>/wp-content/plugins/cc-figure/cc-figure-generator.xhtml";
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

// ]]>
</script>

<?php
    }
}

function cc_figure_add_css() {
    if (get_option('cc_figure_css')) {
        echo '<link rel="stylesheet" href="' . rtrim(get_settings('siteurl'), '/') . '/wp-content/plugins/cc-figure/css/'.get_option('cc_figure_css').'" type="text/css"/>';
        // TODO: use bloginfo URL here
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
    <h2>CC Figure: Stylesheet</h2>
    <form method="post" action="options.php">';
        settings_fields('cc_figure_options');
    // TODO: "Kein Stylesheet" selected when in "" database
    echo'   
        <ul>'.cc_figure_admin_css().'
            <li>
                <label><input type="radio" name="cc_figure_css" value=""/> Kein Stylesheet</label>
            </li>
        </ul>
        <div class="submit">
            <input type="submit" class="button-primary" value="'._('Save Changes').'" />
        </div>
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

// add admin pages
if(is_admin()){
    add_action('admin_menu', 'cc_figure_plugin_menu');
    add_action( 'admin_init', 'cc_figure_register_settings');
}

?>
