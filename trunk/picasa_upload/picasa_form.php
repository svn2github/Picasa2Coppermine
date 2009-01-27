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
  $HeadURL:$
  $Revision:$
  $LastChangedBy:$
  $Date:$
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

if (!USER_ID) {
    $redirect = $redirect . "index.php?file=picasa_upload/picasa_login";
    $redirect .= '&referer='.urlencode('index.php?file=picasa_upload/picasa_login');
    header("Location: $redirect");
    exit();
}

//You can change the following:
$picasa_dir = 'picasa_uploads';

require_once("xmlHandler.class");
$rss = $superCage->post->getRaw('rss');
$thumb_size = $CONFIG['thumb_width'];
$max_size = $CONFIG['max_upl_width_height'];

$user_pass = $cpg_udb->get_user_pass(USER_ID);
js_include('plugins/picasa_upload/picasa_upload.js');
pageheader_mini('Upload', true);
echo '<link rel="stylesheet" href="plugins/picasa_upload/picasa_upload.css" type="text/css" />';
//echo "<pre>".htmlentities($rss)."</pre>";
?>
Curently logged in as <strong><?php echo USER_NAME; ?></strong> | <a href="index.php?p_force_logout=1&file=picasa_upload/picasa_login">Login as different user</a> | <a href="logout.php">Logout</a>
<div align="center">
	<img src="images/coppermine-logo.png" />
    <h1><?php echo $CONFIG['gallery_name']; ?></h1>
</div>

<?php
if($rss)
{
    if (GALLERY_ADMIN_MODE) {
        $public_albums = cpg_db_query("SELECT aid, title, cid, name FROM {$CONFIG['TABLE_ALBUMS']} INNER JOIN {$CONFIG['TABLE_CATEGORIES']} ON cid = category WHERE category < " . FIRST_USER_CAT);
        //select albums that don't belong to a category
        $public_albums_no_cat = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = 0");
    } else {
        $public_albums = cpg_db_query("SELECT aid, title, cid, name FROM {$CONFIG['TABLE_ALBUMS']} INNER JOIN {$CONFIG['TABLE_CATEGORIES']} ON cid = category WHERE category < " . FIRST_USER_CAT . " AND ((uploads='YES' AND (visibility = '0' OR visibility IN ".USER_GROUP_SET.")) OR (owner=".USER_ID."))");
        //select albums that don't belong to a category
        $public_albums_no_cat = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = 0 AND ((uploads='YES' AND (visibility = '0' OR visibility IN ".USER_GROUP_SET.")) OR (owner=".USER_ID."))");
    }
    
    
    if (mysql_num_rows($public_albums)) {
        $public_albums_list = cpg_db_fetch_rowset($public_albums);
    } else {
        $public_albums_list = array();
    }
    
    //do the same for non-categorized albums
    if (mysql_num_rows($public_albums_no_cat)) {
        $public_albums_list_no_cat = cpg_db_fetch_rowset($public_albums_no_cat);
    } else {
        $public_albums_list_no_cat = array();
    }
    
    //merge the 2 album arrays
    $public_albums_list = array_merge($public_albums_list, $public_albums_list_no_cat);
    
    
    if (USER_ID) {
        $user_albums = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category='" . (FIRST_USER_CAT + USER_ID) . "' ORDER BY title");
        if (mysql_num_rows($user_albums)) {
            $user_albums_list = cpg_db_fetch_rowset($user_albums);
        } else {
            $user_albums_list = array();
        }
    } else {
        $user_albums_list = array();
    }
    
    if (count($public_albums_list) || count($user_albums_list)) {
    ?>
    <h2>Upload photos to your site from Picasa</h2>
    <form name="f" method="post" id="picasa_form" action="upload.php">
    <input type="hidden" name="plugin_process" />
    <input type="hidden" name="user" value="<?php echo base64_encode(serialize($user_pass)); ?>" />
    <div class="h">Select album in which to upload files</div>
        <div>
            <select name="album" class="listbox" id="album">
            <?php
            // Get the ancestry of the categories
            $vQuery = "SELECT cid, parent, name FROM " . $CONFIG['TABLE_CATEGORIES'] . " WHERE 1";
            $vResult = cpg_db_query($vQuery);
            $vRes = cpg_db_fetch_rowset($vResult);
            mysql_free_result($vResult);
            foreach ($vRes as $vResI => $vResV) {
                $vResRow = $vRes[$vResI];
                $catParent[$vResRow['cid']] = $vResRow['parent'];
                $catName[$vResRow['cid']] = $vResRow['name'];
            }
            $catAnces = array();
            foreach ($catParent as $cid => $cid_parent) {
                $catAnces[$cid] = '';
                while ($cid_parent != 0) {
                    $catAnces[$cid] = $catName[$cid_parent] . ($catAnces[$cid]?' - '.$catAnces[$cid]:'');
                    $cid_parent = $catParent[$cid_parent];
                }
            }
        
            // Reset counter
            $list_count = 0;
        
            // Cycle through the User albums
            foreach($user_albums_list as $album) {
        
                // Add to multi-dim array for later sorting
                $listArray[$list_count]['cat'] = $lang_common['personal_albums'];
                $listArray[$list_count]['aid'] = $album['aid'];
                $listArray[$list_count]['title'] = $album['title'];
                $listArray[$list_count]['cid'] = -1;
                $list_count++;
            }
        
            // Cycle through the public albums
            foreach($public_albums_list as $album) {
        
                // Set $album_id to the actual album ID
                $album_id = $album['aid'];
        
                // Add to multi-dim array for sorting later
                if (isset($album['name']) && $album['name']) {
                    $listArray[$list_count]['cat'] = $catAnces[$album['cid']] . ($catAnces[$album['cid']]?' - ':'') . $album['name'];
                    $listArray[$list_count]['cid'] = $album['cid'];
                } else {
                    $listArray[$list_count]['cat'] = $lang_common['albums_no_category'];
                    $listArray[$list_count]['cid'] = 0;
                }
                $listArray[$list_count]['aid'] = $album['aid'];
                $listArray[$list_count]['title'] = $album['title'];
                $list_count++;
            }
        
            // Sort the pulldown options by category and album name
            $listArray = array_csort($listArray,'cat','title');     // alphabetically by category name
        
            // Finally, print out the nicely sorted and formatted drop down list
            $alb_cid = '';
            echo '                <option value="">' . $lang_common['select_album'] . "</option>\n";
            foreach ($listArray as $val) {
                //if ($val['cat'] != $alb_cat) {  // old method compared names which might not be unique
                if ($val['cid'] !== $alb_cid) {
                    if ($alb_cid) {
                        echo "                </optgroup>\n";
                    }
                    echo '                <optgroup label="' . $val['cat'] . '">' . "\n";
                    $alb_cid = $val['cid'];
                }
                echo '                <option value="' . $val['aid'] . '"' . ($val['aid'] == $sel_album ? ' selected' : '') . '>   ' . $val['title'] . "</option>\n";
            }
            if ($alb_cid) {
                echo "                </optgroup>\n";
            }
            ?>
            </select>
        </div>
		
		<div class="h">Selected images</div>
    
			<div>
    <?
        $xh = new xmlHandler();
        $nodeNames = array("PHOTO:THUMBNAIL", "PHOTO:IMGSRC", "TITLE", "DESCRIPTION", "MEDIA:GROUP", "MEDIA:CONTENT");
        $xh->setElementNames($nodeNames);
        $xh->setStartTag("ITEM");
        $xh->setVarsDefault();
        $xh->setXmlParser();
        $xh->setXmlData($rss);
        $pData = $xh->xmlParse();
        $br = 0;
        
        // Preview "tray": draw shadowed square thumbnails of size 48x48
        foreach($pData as $e) {
            echo "<img src='".$e['photo:thumbnail']."?size=100x100' />&nbsp;\r\n";
        }
    
        // Image request queue: add image requests for base image & clickthrough
        foreach($pData as $e) {
            if ($e['media:video']) {
                $large = $e['media:content'];
            } else {
                $large = $e['media:content:image']."?size=$max_size";
            }
            
            echo "<input type=hidden name='".$large."'>\r\n";
            echo "<input type=hidden name='title[]' value=\"{$e['description']}\">\r\n";
        }
    ?>
			</div>

		<div class="h">
			<input type="submit" value="Publish!">&nbsp;<input type="button" value="Discard" onClick="location.href='minibrowser:close'">
		</div>
		
		</form>
    <?php
    } else {
        // no albums
        msg_box (ERROR, 'Sorry there is no album where you are allowed to upload files', '', '', 'error');
    }
} else {
    msg_box(ERROR, "Sorry, but no pictures were received. Please try again.", '', '', 'error');
}
pagefooter_mini();
?>