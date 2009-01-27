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
    $('#picasa_form').submit(function(){
        if (!$('#album').val()) {
            alert('Please select album to upload the selected photos');
            return false;
        }
        return true;
    });
});