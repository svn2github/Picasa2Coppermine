<?php 
define('IN_COPPERMINE', true);
require('include/init.inc.php');

pageheader('Picasa uploader');

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}
global $CONFIG;
require_once("picasa/picasa_form.inc.php");
pagefooter(); 
?>