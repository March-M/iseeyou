<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->
<head>
<!-- BASICS -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>media.42.fr</title>
<meta name="description" content="La revue de presse vidéo de 42">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="/css/isotope.css" media="screen" />
<link rel="stylesheet" href="/js/fancybox/jquery.fancybox.css" type="text/css" media="screen" />
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/bootstrap.css">
<!-- <link rel="stylesheet" href="/dist/css/bootstrap.min.css"> -->
<!-- <link rel="stylesheet" href="/dist/css/bootstrap-theme.css"> -->
<!-- skin -->
<link rel="stylesheet" href="/skin/default.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="/js/jquery.ui.widget.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="/js/jquery.fileupload.js"></script>
<script>
//Afficher les tooltips si on passe sur un champ en comprenant
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});

//afficher les deux champs de changement de mot de passe uniquement si le select de changement de password vaut 1 (oui)
$(document).ready(function(){
    //On le masque en JS dès le début
    $('#changepass').hide();
    //Puis on retire l'attribut CSS qui permettais de le cacher avant l'action du JS, devenu inutile
    $('#changepass').removeClass("hidden");

    //on l'affiche si la valeur du select correspond
    $('#change_password').change(function(){
      if ($(this).val() == "1") {
        $('#changepass').show('slow');
      } else {
        $('#changepass').hide('slow');
      }
    });
  });
</script>
<style>
@charset "UTF-8";
/*
 * jQuery File Upload Plugin CSS
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2013, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

.fileinput-button {
  position: relative;
  overflow: hidden;
  display: inline-block;
}
.fileinput-button input {
  position: absolute;
  top: 0;
  right: 0;
  margin: 0;
  opacity: 0;
  -ms-filter: 'alpha(opacity=0)';
  font-size: 200px !important;
  direction: ltr;
  cursor: pointer;
}

/* Fixes for IE < 8 */
@media screen\9 {
  .fileinput-button input {
    filter: alpha(opacity=0);
    font-size: 100%;
    height: 100%;
  }
}

.user-profile-picture
{
  display:inline-block;
  height:60px;
  width:60px;
  background-repeat:no-repeat;
  background-size:100%;
  background-position:50% 50%;
  border-radius:50%;
  margin:0 40px
}
</style>

</head>

<body>

<!-- spacer section:testimonial -->
<?php
      $req_user_data = $mysql->prepare("SELECT pseudo, photo_link FROM media_users WHERE id=:id ");
      $req_user_data->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
      $req_user_data->execute();
      $user_data = $req_user_data->fetch();
?>
<section id="testimonials" class="section" data-stellar-background-ratio="0.5">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="align-center">
          <div class="testimonial pad-top40 pad-bot40 clearfix" style="background-color:rgba(0,0,0,0.50); border-radius:5px;">
            <h2 class="slogan"> <font color="#FFFFFF">media.42.fr</font> </h2>
            <p> <i>La revue de presse vidéo de 42</i></p>
      <div>
        <p> <i>Connecté en tant que <?php echo $user_data['pseudo']; ?></i> </p>
    <div class="user-profile-picture" style="background-image: url('<?php echo $user_data['photo_link']; ?>')"></div>
      </div>
            <a href="https://twitter.com/42born2code" target="_blank"><img src="/img/icons/twitter.png" style="width:30px; margin:5px;" /></a>
            <a href="https://www.facebook.com/42Born2Code" target="_blank"><img src="/img/icons/facebook.png" style="width:30px; margin:5px;" /></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<section id="section-about" class="section appear clearfix" style="background:url(/img/site42-bg.gif) repeat;">
<div class="container">
  <div class="row">
  <div class="login-panel">
    <div class="align-center">
      <a href="/admin/manage_profil.php"><span class="btn btn-success fileinput-button"><span>Modifier son profil</span></span></a>
      <a href="/admin/manage_accounts.php"><span class="btn btn-success fileinput-button"><span>Gérer les comptes</span></span></a>
      <a href="/admin/manage_videos.php"><span class="btn btn-success fileinput-button"><span>Gérer les vidéos</span></span></a>
      <a href="/admin/logout.php"><span class="btn btn-danger fileinput-button"><span>Déconnexion</span></span></a></div>
