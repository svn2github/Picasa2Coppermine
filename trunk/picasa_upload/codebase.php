<?php
/****************************
  Picasa to Coppermine Upload
  ***************************
  Copyright (c) Aditya Mooley <adityamooley@sanisoft.com>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  ********************************************
  Version: 1
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

$thisplugin->add_action('plugin_install','picasa_plugin_install');
$thisplugin->add_action('plugin_configure','picasa_plugin_configure');
$thisplugin->add_action('plugin_uninstall','picsa_plugin_uninstall');
$thisplugin->add_filter('sub_menu', 'picasa_sub_menu_button');
$thisplugin->add_action('upload_process','picasa_process_upload_form');


/**
 * Function to perform some actions when installing this plugin
 * We will create the .pbz file required by Picasa at install time and link the file directly to the menu link
 */
function picasa_plugin_install()
{
    global $CONFIG;
    
    $superCage = Inspekt::makeSuperCage();
    
    if ($superCage->post->keyExists('picsa_upload_config')) {
        include('archive.php');
    
        $basedir = dirname(dirname(dirname(__FILE__)));
        $albumdir = $basedir . DIRECTORY_SEPARATOR . $CONFIG['fullpath'];
        
        //First create a usable .pbf and save in edit folder since it's already writable
        $pbfStr = file_get_contents($basedir.'/plugins/picasa_upload/{86a1ab87-00e2-441c-806b-23aeff6bcf0d}.pbf');
        
        //Replace the {UPLOAD_LINK} with correct link based on user's gallery url
        $pbfStr = str_replace('{UPLOAD_LINK}', $CONFIG['ecards_more_pic_target'].'index.php?file=picasa_upload/picasa_form', $pbfStr);
        
        //Write this string to the file.
        file_put_contents($albumdir.'/edit/{86a1ab87-00e2-441c-806b-23aeff6bcf0d}.pbf', $pbfStr);
        
        // Now we have both the files needed to create the pbz file.
        $zip = new zip_file('coppermine.pbz');
        $options = array(
                'basedir'    => $albumdir.'/edit',
                'overwrite'  => 1,
                'inmemory'   => 0,
                'recurse'    => 0,
                'storepaths' => 0,
                'name'       => 'coppermine.pbz',
                'type'       => 'zip',
            );
        
        $filelist = array($basedir.'/plugins/picasa_upload/{86a1ab87-00e2-441c-806b-23aeff6bcf0d}.psd', $albumdir.'/edit/{86a1ab87-00e2-441c-806b-23aeff6bcf0d}.pbf');
        $zip->set_options($options);
        $zip->add_files($filelist);
        
        // Save the pbz file in edit folder. We will be linking to this file directly as required by Picasa.
        $zip->create_archive();
        
        // The file must get created. Otherwise the plugin won't install.
        if ($zip->error) {
            return false;
        } else {
            // zip file created. Now add the config setting for thumb creation in config table
            return picasa_update_config();
        }
    } else {
        return 1;
    }
}// end picasa_plugin_install()

function picasa_update_config()
{
    global $CONFIG;
    
    $superCage = Inspekt::makeSuperCage();
    
    $value = $superCage->post->keyExists('plugin_picasa_thumb') ? $superCage->post->getInt('plugin_picasa_thumb') : 0;
    
    if (array_key_exists('plugin_picasa_thumb', $CONFIG) == FALSE) {
        $f = cpg_db_query("INSERT INTO {$CONFIG['TABLE_CONFIG']} VALUES ('plugin_picasa_thumb', $value)");
    } else {
        $f = cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = $value WHERE name = 'plugin_picasa_thumb'");
    }
    
    return $f;
}


/**
 * Function to show the configuration options for this plugin
 */
function picasa_plugin_configure()
{
    global $CONFIG;
    
    $superCage = Inspekt::makeSuperCage();
    
    $action = $superCage->server->getEscaped('REQUEST_URI');
    $checked = (isset($CONFIG['plugin_picasa_thumb']) && $CONFIG['plugin_picasa_thumb'] == 1) ? 'checked' : '';
    
    $help = '&nbsp;'.cpg_display_help('f=empty.htm&amp;base=64&amp;h='.urlencode(base64_encode(serialize('Allowing Picasa to create thumbnails'))).'&amp;t='.urlencode(base64_encode(serialize('Allowing Picasa to create thumbnails will put less load on your server while processing the uploaded images. On the flip side this will require more bandwidth since Picasa will send two files per image.<br />Thumbnails will be based on your current config settings.'))),470,245);

echo <<< EOT
    <form action="{$action}" method="post">
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td class="tablef">Set configuration options for Picasa Upload Plugin</td>
            </tr>
            <tr>
                <td class="tableb">
                    <input type="checkbox" name="plugin_picasa_thumb" id="plugin_picasa_thumb" value="1" $checked />
                    <label for="plugin_picasa_thumb">Let Picasa create thumbnails for me.</label>&nbsp;$help
                </td>
            </tr>
            <tr>
                <td class="tableb">
                    <input type="submit" name="picsa_upload_config" value="Submit" class="button" />
                </td>
            </tr>
        </table>
    </form>
EOT;
}// end picasa_plugin_configure


/**
 * Cleanup while uninstalling
 */
function picsa_plugin_uninstall()
{
    global $CONFIG;
    $f= cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE `name` = 'plugin_picasa_thumb'");
    
    return true;
}// end picsa_plugin_uninstall()

/**
 * Function to provide the link for installing Picasa Coppermine button in sub-menu. The link will be shown in the
 * themes supporting this plugin hook. Others can use the link from plugin manager page.
 */
function picasa_sub_menu_button($menu)
{
    global $CONFIG;
    
    if (!USER_ID) {
        return $menu;
    }
    
    $new_button = array();
    $new_button[0][0] = 'Install Picasa Button';
    $new_button[0][1] = 'Install Picasa Button';
    $new_button[0][2] = 'picasa://importbutton/?url='.$CONFIG['ecards_more_pic_target'].'albums/edit/coppermine.pbz';
    $new_button[0][3] = 'class_name';
    $new_button[0][4] = '::';
    $new_button[0][5] = 'rel="nofolow"';

    // Add the link array to the existing array correctly and return the modified menu
    array_splice($menu, count($menu)-1, 0, $new_button);

    return $menu;
}

/**
 * Function to handle the files uploaded by Picasa. We are making sure that the script never dies. The end result must
 * always be a URL echo'ed by the script. Picasa will show the supplied URL in user's default browser. There we will show
 * the status of the script to user.
 */
function picasa_process_upload_form($upload_form)
{
    global $CONFIG, $lang_db_input_php, $PIC_NEED_APPROVAL;
    
    include_once('picmgmt.inc.php');
    
    $superCage = Inspekt::makeSuperCage();
    
    $album = $superCage->post->getInt('album');

    if (!USER_CAN_UPLOAD_PICTURES) {
        picasa_redirect('error', 'Permission Denied');
    }
    
    // Check if the album id provided is valid
    if (!(GALLERY_ADMIN_MODE || user_is_allowed())) {
        $result = cpg_db_query("SELECT category FROM {$CONFIG['TABLE_ALBUMS']} WHERE aid = $album AND (uploads = 'YES' OR category = " . (USER_ID + FIRST_USER_CAT) . ")");
 
        if (mysql_num_rows($result) == 0) {
            picasa_redirect('error', 'Unknown album');
        }
        
        $row = mysql_fetch_assoc($result);
        mysql_free_result($result);
        
        $category = $row['category'];
    } else {
        $query = "SELECT category FROM {$CONFIG['TABLE_ALBUMS']} WHERE aid = $album";
        $result = cpg_db_query($query);

        if (mysql_num_rows($result) == 0) {
            picasa_status('error', 'Unknown album');
        }

        $row = mysql_fetch_assoc($result);
        mysql_free_result($result);

        $category = $row['category'];
    }
    
    /**
     * We will be keeping the count of successful and failed uploads so that we can show the final status to the user
     * once the script ends. We don't want the script to die even if any of the uploads fails.
     */
    $success = $failed = 0;
    $imageCount = count($superCage->files->_source);
    
    if (!$imageCount) {
        picasa_redirect('error', 'No Images to upload');
    }
    
    // Pictures are moved in a directory named 10000 + USER_ID
    if (USER_ID && $CONFIG['silly_safe_mode'] != 1) {
    
        $filepath = $CONFIG['userpics'] . (USER_ID + FIRST_USER_CAT);
        $dest_dir = $CONFIG['fullpath'] . $filepath;
        
        if (!is_dir($dest_dir)) {
            mkdir($dest_dir, octdec($CONFIG['default_dir_mode']));
            
            if (!is_dir($dest_dir)) {
                picasa_redirect('error', sprintf('Unable to create directory %s', $dest_dir));
            }
            
            chmod($dest_dir, octdec($CONFIG['default_dir_mode']));
            
            $ft = fopen($dest_dir . '/index.html', 'w');
            fwrite($ft, ' ');
            fclose($ft);
        }
        
        $dest_dir .= '/';
        $filepath .= '/';
    } else {
        $filepath = $CONFIG['userpics'];
        $dest_dir = $CONFIG['fullpath'] . $filepath;
    }
    
    $dest_dir = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . $dest_dir;

    // Check that target dir is writable
    if (!is_writable($dest_dir)) {
        picasa_redirect('error', sprintf('%s is not writable', $dest_dir));
    }
    
    $counter = -1;
    /**
     * Picasa doesn't allow us to set the keys for _FILES and there is no way in Inspekt to get all the data from
     * super globals. So, using the following workaround by directly quering the '_source' array in superCage to get keys
     */
    foreach ($superCage->files->_source as $key => $file) {
        set_time_limit(0);
        $counter++;
        // Test if the filename of the temporary uploaded picture is empty
        if ($superCage->files->_source[$key]['tmp_name'] == '') {
            $failed++;
            continue;
        }
        
        if (get_magic_quotes_gpc()) {
            //Using getRaw() as we have custom sanitization code below
            $picture_name = stripslashes($superCage->files->_source[$key]['name']);
        } else {
            $picture_name = $superCage->files->_source[$key]['name'];
        }
    
        // Replace forbidden chars with underscores
        $picture_name = replace_forbidden($picture_name);
    
        // Check that the file uploaded has a valid extension
        if (!preg_match("/(.+)\.(.*?)\Z/", $picture_name, $matches)) {
            $matches[1] = 'invalid_fname';
            $matches[2] = 'xxx';
        }
        
        if ($matches[2] == '' || !is_known_filetype($matches)) {
            $failed++;
            continue;
        }
        
        // Create a unique name for the uploaded file
        $nr = 0;
        $picture_name = $matches[1] . '.' . $matches[2];
        
        while (file_exists($dest_dir . $picture_name)) {
            $picture_name = $matches[1] . '~' . $nr++ . '.' . $matches[2];
        }
    
        $uploaded_pic = $dest_dir . $picture_name;
        
        // Check whether the current file is actually a thumbnail
        if ($CONFIG['plugin_picasa_thumb']) {
            $p_size = substr($key, strrpos($key, '=')+1);
            if ($p_size == $CONFIG['thumb_width']) {
                // This is a thumbnail. Upload it and continue
                $thumb_pic = $dest_dir . $CONFIG['thumb_pfx'] . $picture_name;
                move_uploaded_file($superCage->files->_source[$key]['tmp_name'], $thumb_pic);
                
                // This is a thumbnail image. decrement the counter so that we can get correct image title in the next iteration.
                $counter--;
                continue;
            }
        }
        
        // Move the picture into its final location
        if (!move_uploaded_file($superCage->files->_source[$key]['tmp_name'], $uploaded_pic)) {
            $failed++;
            continue;
        }
    
        // Change file permission
        chmod($uploaded_pic, octdec($CONFIG['default_file_mode']));
        
        // Get picture information
        // Check that picture file size is lower than the maximum allowed
        if (filesize($uploaded_pic) > ($CONFIG['max_upl_size'] << 10)) {
            @unlink($uploaded_pic);
            $failed++;
            continue;
    
        } elseif (is_image($picture_name)) {
            $imginfo = cpg_getimagesize($uploaded_pic);
                
            if ($imginfo == null) {
                // getimagesize does not recognize the file as a picture
                @unlink($uploaded_pic);
                $failed++;
                continue;
            } elseif ($imginfo[2] != GIS_JPG && $imginfo[2] != GIS_PNG && $CONFIG['GIF_support'] == 0) {
                // JPEG and PNG only are allowed with GD
                @unlink($uploaded_pic);
                $failed++;
                continue;
                
                // Check that picture size (in pixels) is lower than the maximum allowed
            } elseif (max($imginfo[0], $imginfo[1]) > $CONFIG['max_upl_width_height']) {
                if ((USER_IS_ADMIN && $CONFIG['auto_resize'] == 1) || (!USER_IS_ADMIN && $CONFIG['auto_resize'] > 0)) {
                    resize_image($uploaded_pic, $uploaded_pic, $CONFIG['max_upl_width_height'], $CONFIG['thumb_method'], $CONFIG['thumb_use']);
                } else {
                    @unlink($uploaded_pic);
                    $failed++;
                    continue;
                }
            } // Image is ok
        }
        
        // Upload is ok
        // Create thumbnail and internediate image and add the image into the DB
        $result = add_picture($album, $filepath, $picture_name, 0, $superCage->post->getEscaped("/title/$counter"));
        
        if (!$result) {
            @unlink($uploaded_pic);
            $failed++;
            continue;
        } else {
            $success++;
        }
    } //end files foreach
    
    // If there is atleast one successful upload and the picture needs approval then notify the admin
    if ($success && $PIC_NEED_APPROVAL && $CONFIG['upl_notify_admin_email']) {
        include_once('mailer.inc.php');
        cpg_mail('admin', sprintf('%s - Upload notification', $CONFIG['gallery_name']), sprintf('A picture has been uploaded by %s that needs your approval. Visit %s', USER_NAME, $CONFIG['ecards_more_pic_target'].(substr($CONFIG["ecards_more_pic_target"], -1) == '/' ? '' : '/') .'editpics.php?mode=upload_approval'));
    }
    
    if ($success) {
        picasa_redirect('success', "Status: $success files uploaded, $failed files failed.", $album);
    } else {
        picasa_redirect('error', 'No files where uploaded');
    }
    
    exit;
}// end picasa_process_upload_form()


/**
 * This function is like cpg_die. It outputs the URL so that Picasa can open the target page in the user's default browser
 * and stops the execution of this script after that.
 */
function picasa_redirect($status='', $mesg='', $album=0)
{
    global $CONFIG;
    
    if ($album) {
        $albStr = "aid=$album&";
    } else {
        $albStr = '';
    }
    
    echo $CONFIG['ecards_more_pic_target'].'index.php?'.$albStr.'status='.$status.'&mesg='.urlencode($mesg).'&file=picasa_upload/status';
    exit;
}// end picasa_redirect
?>