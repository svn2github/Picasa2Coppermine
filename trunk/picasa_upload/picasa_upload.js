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
$(document).ready(function(){
    $('#new_album_block').hide();
    
    $('#album').change(function() {
        if ($('#album').val() == -1) {
            // New album option selected
            $('#new_album_block').show();
            $('#album_name').focus();
        } else {
            $('#album_name').val('');
            $('#new_album_block').hide();
        }
    });
    
    $('#picasa_form').submit(function(){
        if (!$('#album').val()) {
            alert('Please select album to upload the selected photos');
            $('#album').focus();
            return false;
        }
        
        if ($('#album').val() == -1 && !$('#album_name').val()) {
            alert('Please enter the name for new album');
            $('#album_name').focus();
            return false;
        }
        
        alert("Uploading your files. Please be patient.\nIt may take few minutes, depending on the number of files you are uploading, before you see the status for your action.");
        return true;
    });
});