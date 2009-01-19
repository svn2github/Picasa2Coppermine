<?php
/*

$superCage = Inspekt::makeSuperCage();

$thumb_size = $superCage->post->getDigits('thumb_size');
$default_dir_mode = $superCage->post->getDigits('default_dir_mode');
$default_file_mode = $superCage->post->getDigits('default_file_mode');

$new_foldername = $superCage->post->getDir('new_foldername');
$albums_dir = $superCage->post->getDir('albums_dir');
$thumb_prefix = $superCage->post->getDir('thumb_prefix');
$picasa_dir = $superCage->post->getDir('picasa_dir');
$foldername = $superCage->post->getDir('foldername');

$ecards_more_pic_target = $superCage->post->getPath('ecards_more_pic_target');
*/
//digits
$thumb_size = stripslashes($_POST['thumb_size']);
$default_dir_mode = stripslashes($_POST['default_dir_mode']);
$default_file_mode = stripslashes($_POST['default_file_mode']);

//foldernames
$new_foldername = stripslashes($_POST['new_foldername']);
$albums_dir = stripslashes($_POST['albums_dir']);
$thumb_prefix = stripslashes($_POST['thumb_prefix']);
$picasa_dir = stripslashes($_POST['picasa_dir']);
$foldername = stripslashes($_POST['foldername']);

//url
$ecards_more_pic_target = stripslashes($_POST['ecards_more_pic_target']);

$upload_dir = "../$albums_dir/$picasa_dir";

if ($foldername == 'new_folder' && $new_foldername !== '') {
	$foldername = $new_foldername;
} elseif ($foldername == 'new_folder' && $new_foldername == '') {
	$foldername = 'Untitled_Folder';
}

$foldername = trim(preg_replace("/[^a-z0-9-]/", "-", strtolower($foldername)),'-');
// store uploaded images by name
if($_FILES) {
	if(!file_exists("$upload_dir"))
	{
	    mkdir("$upload_dir", $default_dir_mode);
	}
	if(!file_exists("$upload_dir/$foldername"))
	{
		mkdir("$upload_dir/$foldername", $default_dir_mode);
	}

	foreach($_FILES as $key => $file) {
	
		if (!empty($file)) {
		
			// you can obtain the original filename from Picasa like this:
				$tmpfile  = $file['tmp_name'];
				$fname    = $file['name'];
				$sizepos = strpos($key, "size=");
				$size = "";
				if ($sizepos) {
					$size = substr($key, $sizepos + 5);
					$size = str_replace("-", "c", $size);
				}
				if ($size == $thumb_size ) {
					$fname = $thumb_prefix.'_'.$fname;
				} 
				$localfn  = "$upload_dir/".$foldername."/".$fname;
			
			if (move_uploaded_file($tmpfile, $localfn)) {
				chmod($localfn, $default_file_mode);
			}
			
		}
	}

	echo $ecards_more_pic_target."searchnew.php?startdir=$picasa_dir/$foldername";
}

?>