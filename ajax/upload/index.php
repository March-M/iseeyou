<?php
/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   index.php                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: mgaschet <mgaschet@student.42.fr>          +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/01/11 04:42:26 by mgaschet          #+#    #+#             */
/*   Updated: 2016/02/14 20:33:15 by mgaschet         ###   ########.fr       */
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

function complete_upload_form($textarea_name) 
{
  ?>
  <script type="text/javascript">
    $(function () {
      'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: '/admin/ajax/upload/upload.php'
      });
  });
  </script>
  <div class="container">
    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload" action="/admin/ajax/upload/upload.php" method="POST" enctype="multipart/form-data">
      <!-- Redirect browsers with JavaScript disabled to the origin page -->
      <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
      <div class="row fileupload-buttonbar">
        <div class="col-lg-12">
          <!-- The fileinput-button span is used to style the file input field as button -->
          <span class="btn btn-success fileinput-button">
            <i class="glyphicon glyphicon-plus"></i>
            <span>Uploader un fichier...</span>
            <input type="file" name="files[]" multiple>
          </span>
          <button type="submit" class="btn btn-primary start">
            <i class="glyphicon glyphicon-upload"></i>
            <span>Lancer l'upload de tout les fichiers</span>
          </button>
          <button type="reset" class="btn btn-warning cancel">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span>Annuler tout les uploads</span>
          </button>
          <button type="button" class="btn btn-danger delete">
            <i class="glyphicon glyphicon-trash"></i>
            <span>Supprimer les fichiers sélectionné</span>
          </button>
          <input type="checkbox" class="toggle">
          <!-- The global file processing state -->
          <span class="fileupload-process"></span>
        </div>
        <!-- The global progress state -->
        <div class="col-lg-5 fileupload-progress fade">
          <!-- The global progress bar -->
          <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
          </div>
          <!-- The extended global progress state -->
          <div class="progress-extended">&nbsp;</div>
        </div>
      </div>
      <!-- The table listing the files available for upload/download -->
      <table role="presentation" class="table table-striped" style="word-break: break-all; width: 97%;"><tbody class="files"></tbody></table>
    </form>
    <br>
  </div>
  <!-- The template to display files available for upload -->
  <script id="template-upload" type="text/x-tmpl">
    <?php include($_SERVER['DOCUMENT_ROOT']."/dist/tmpl/ajax_upload_template_upload.tmpl"); ?>
  </script>
  <!-- The template to display files available for download -->
  <script id="template-download" type="text/x-tmpl">
    <?php include($_SERVER['DOCUMENT_ROOT']."/dist/tmpl/ajax_upload_template_download.tmpl"); ?>
  </script>
<?php
}

function simple_upload_form($input_name)
{
  ?>
  <div class="row">
    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload_<?php echo $input_name ?>" action="/admin/ajax/upload/upload.php" method="POST" enctype="multipart/form-data">
      <!-- Redirect browsers with JavaScript disabled to the origin page -->
      <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
      <div class="col-md-12">
        <div id="upload_<?php echo $input_name ?>" name="upload_<?php echo $input_name ?>">
          <div id="before_upload_<?php echo $input_name ?>" name="before_upload">
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn btn-success fileinput-button">
              <i class="glyphicon glyphicon-plus"></i>
              <span>Select files...</span>
              <!-- The file input field used as target for the file upload widget -->
              <input id="<?php echo $input_name ?>" type="file" name="files[]" multiple>
            </span>
            <!-- The global progress bar -->
            <div id="progress_<?php echo $input_name ?>" class="progress">
              <div class="progress-bar progress-bar-success"></div>
            </div>
            <!-- The container for the uploaded files -->
          </div>
        </div>
      </div>
      <!-- The table listing the files available for upload/download -->
      <table role="presentation" class="table table-striped" style="word-break: break-all; width: 97%;"><tbody class="files"></tbody></table>
    </form>
    <br>
  <script>
    /*jslint unparam: true */
    /*global window, $ */
    $(function () {
      'use strict';
      $('#<?php echo $input_name ?>').fileupload({
        url: '/admin/ajax/upload/upload.php',
        dataType: 'json',
        done: function (e, data) {
          $.each(data.result.files, function (index, file) {
            $('#before_upload_<?php echo $input_name ?>').hide('slow', function()
            { 
              //$(this).remove(); 
              $(this).style="display: none;";
            }); 
            $('#upload_<?php echo $input_name ?>').append(`<div id='after_upload_<?php echo $input_name ?>' name='after_upload_<?php echo $input_name ?>'><a href="#" onclick="return confirm_delete_<?php echo $input_name ?>('Voulez vous vraiment remplacer l&#145;image ?', '` + file.name + `');"><img src='` + file.url + `' style='width: 25%; height: 25%;'/></a><input type='hidden' name='<?php echo $input_name ?>' id='<?php echo $input_name ?>' value='` + file.url + `' /></div>`);
          });
        },
        progressall: function (e, data) {
          var progress = parseInt(data.loaded / data.total * 100, 10);
          $('#progress_<?php echo $input_name ?> .progress-bar').css(
            'width',
            progress + '%'
            );
        }
      }).prop('disabled', !$.support.fileInput)
.parent().addClass($.support.fileInput ? undefined : 'disabled');
});



function confirm_delete_<?php echo $input_name ?>(question, filename) {

  if(confirm(question)){
    $.ajax(
    {
      url : '/admin/ajax/upload/upload.php?file='+filename, 
      type : 'DELETE', 
      dataType: 'json', 
      success : function(data, statut)
      { 
        $('#after_upload_<?php echo $input_name ?>').hide('slow', function()
        { 
          $(this).remove(); 
        }); 
        $('#progress_<?php echo $input_name ?> .progress-bar').css(
          'width',
          0 + '%'
          );
        $('#before_upload_<?php echo $input_name ?>').show('slow', function()
        { 
          $(this).style="display: block;";
        }); 
        //document.getElementById('before_upload_<?php echo $input_name ?>').style="display: block;";
      } 
    });
  }else{
    return false;  
  }
}
</script>
<?php
}