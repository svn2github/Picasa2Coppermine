<?php
//You can change the following:
$picasa_dir = 'picasa_uploads';

require_once("picasa/xmlHandler.class");
$rss = $superCage->post->getRaw('rss');
$thumb_size = $CONFIG['thumb_width'];
$max_size = $CONFIG['max_upl_width_height'];
$albums_dir = $CONFIG['fullpath'];
$default_dir_mode = $CONFIG['default_dir_mode'];
$default_file_mode = $CONFIG['default_file_mode'];
$ecards_more_pic_target = $CONFIG['ecards_more_pic_target'];
$thumb_prefix = $CONFIG['thumb_pfx'];
?>
<link rel="STYLESHEET" type="text/css" href="picasa/style.css">

<form name='f' method='post' action='picasa/picasa_post.php'>

<h1>Picasa Uploader for Coppermine (for putting pictures on your server)</h1>

<h3>Select folder in which to upload files</h3>
<select name="foldername">
     <option value="new_folder">*Create new folder*</option>
<?php

$path = "./$albums_dir/$picasa_dir/";

$dir = opendir($path); 

while (($file = readdir($dir)) !== false) 
{ 
     if (!in_array($file, array('.', '..'))) 
     { 
          echo '<option value="'.$file .'">'.$file. "</option>\n"; 
     } 
} 

closedir($dir);  
?>
</select>
<h3>New Folder Name:</h3>
<input type="text" name="new_foldername" id="new_foldername" tabindex="1">
<div>Please note that the album selection will occur after the files have been uploaded into the designated folder.</div>
<div class='h'>Selected images</div>

<div>
<?
if($rss)
{
	$xh = new xmlHandler();
	$nodeNames = array("PHOTO:THUMBNAIL", "PHOTO:IMGSRC", "TITLE");
	$xh->setElementNames($nodeNames);
	$xh->setStartTag("ITEM");
	$xh->setVarsDefault();
	$xh->setXmlParser();
	$xh->setXmlData($rss);
	$pData = $xh->xmlParse();
	$br = 0;
	
	// Preview "tray": draw shadowed square thumbnails of size 48x48
	foreach($pData as $e) {
		echo "<img src='".$e['photo:thumbnail']."?size=thumb_size'>\r\n";
	}

	// Image request queue: add image requests for base image & clickthrough
	foreach($pData as $e) {
		// use a thumbnail if you don't want exif (saves space)
		// thumbnail requests are clamped at 144 pixels
		// (negative values give square-cropped images)
		$small = $e['photo:thumbnail']."?size=$thumb_size";
		$large = $e['photo:imgsrc']."?size=$max_size";
		
		echo "<input type=hidden name='".$large."'>\r\n";
		echo "<input type=hidden name='".$small."'>\r\n";
		echo "<input type=hidden name=\"thumb_size\" value=\"$thumb_size\">\r\n";
		echo "<input type=hidden name=\"albums_dir\" value=\"$albums_dir\">\r\n";
		echo "<input type=hidden name=\"default_dir_mode\" value=\"$default_dir_mode\">\r\n";
		echo "<input type=hidden name=\"default_file_mode\" value=\"$default_file_mode\">\r\n";
		echo "<input type=hidden name=\"ecards_more_pic_target\" value=\"$ecards_more_pic_target\">\r\n";
		echo "<input type=hidden name=\"thumb_prefix\" value=\"$thumb_prefix\">\r\n";
		echo "<input type=hidden name=\"picasa_dir\" value=\"$picasa_dir\">\r\n";
	}
?>
<textarea name="imgbody" style="visibility:hidden">
<?php

	// Next, a (hidden) textarea containing markup for our final image post
	// This could be replaced with: a rich editor, visible HTML for savvy users,
	// or with just a textbox list that's transformed on the server into a gallery
	//
	// The markup case is reasonably complex, so we show it here.
	//
	// At post time, the following content is transformed from "local" Picasa image URLs 
	// to URLs of images stored on the receiving server
	
	foreach($pData as $e) {
		$small = $e['photo:thumbnail']."?size=$thumb_size";
		$large = $e['photo:imgsrc']."?size=$max_size";
		echo "<a href='".$large."'>\r\n  <img border=0 src='".$small."'></a>\r\n";
	}
	echo "</textarea>";
} else {
	echo "Sorry, but no pictures were received. Please try again.";
}
?>
</div>

<div class='h'>
<input type=submit value="Publish!">&nbsp;
<input type=button value="Discard" onClick="location.href='minibrowser:close'"><br/>
</div>

</form>