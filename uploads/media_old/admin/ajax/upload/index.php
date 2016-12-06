<?php
/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   index.php                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: anonymous <anonymous@student.42.fr>        +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/01/11 04:42:26 by mgaschet          #+#    #+#             */
/*   Updated: 2016/09/14 12:12:09 by anonymous        ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

/*
 * /ajax/upload/index.php
 * Ajax upload file
 * /ajax/upload/index.php by @michel_gaschet
 *
 * This script use the "jQuery File Upload Plugin" UploadHandler.php file
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * "jQuery File Upload Plugin" Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * "jQuery File Upload Plugin" Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
//Pour éviter que n'importe qui puisse upload des fichiers, on vérifie s'il est bien accédé via un include et pas via un accès direct http(s)
if (!defined('IS_UPLOAD_INCLUDED'))
  die(nl2br("You can't call this file directly\n\nVous ne pouvez pas accéder directement à ce fichier"));

function simple_upload_form($input_name)
{
  ?>
    <!-- The fileinput-button span is used to style the file input field as button -->
    <span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Select files...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="<?php echo $input_name; ?>_file" type="file" name="files[]">
    </span>
    <br>
    <br>
    <!-- The global progress bar -->
    <div id="progress_<?php echo $input_name; ?>" class="progress">
        <div class="progress-bar progress-bar-success"></div>
    </div>
<script>
/*jslint unparam: true */
/*global window, $ */
$(function () {
    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = '/admin/ajax/upload/upload.php';
    $('#<?php echo $input_name; ?>_file').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
		$.each(data.result.files, function(index, file) {
      if (typeof file.error !== 'undefined') {
        alert(file.error);
      }
      else {
		    document.getElementById('<?php echo $input_name; ?>').value = file.name;
      }
		});
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress_<?php echo $input_name; ?> .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
</script>
<?php
}
