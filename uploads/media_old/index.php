<?php 
include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
?>

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
<!-- <link rel="stylesheet" href="/css/bootstrap.min.css"> -->
<!-- <link rel="stylesheet" href="/css/bootstrap-theme.css"> -->
<!-- skin -->
<link rel="stylesheet" href="/skin/default.css">
</head>

<body>

<!-- spacer section:testimonial -->





<section id="testimonials" class="section" data-stellar-background-ratio="0.5">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="align-center">
          <div class="testimonial pad-top40 pad-bot40 clearfix" style="background-color:rgba(0,0,0,0.50); border-radius:5px;">
            <h2 class="slogan"> <font color="#FFFFFF">media.42.fr</font> </h2>
            <p> <i>La revue de presse vidéo de 42</i></p>
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
<?php
  //Génération du tableau contenant les paramètres
  $req_video = $mysql->prepare("SELECT * FROM `media_list` ORDER BY `id` DESC");
  $req_video->execute();

  $i = 0;
  while($video = $req_video->fetch())
  {
    ?>
    <article class="col-sm-6 col-md-6 <?php if(($i % 2) == 0){echo "col-md-offset-0";} ?> isotopeItem webdesign">
      <hr style="color:#666; width:100%;"/>
      <h4 style="margin-left:15px;"><?php echo $video['press_name']; ?> - <font style="font-size:small;"><i><?php echo $video['press_date']; ?></i></font></h4>
      <h3><i></i></h3>
      <div class="portfolio-item">
        <video width="100%" controls preload="none" poster="/vids/<?php echo $video['thumbnails']; ?>">
          <source src="/vids/<?php echo $video['video_mp4']; ?>" type="video/mp4" />
          <source src="/vids/<?php echo $video['video_webm']; ?>" type="video/webm" />
          <source src="/vids/<?php echo $video['video_ogg']; ?>" type="video/ogg" />
          Ce site utilise les balises vidéos html5. Si aucune vidéo ne s'affiche, veuillez procédez à une mise a jour de votre navigateur. </video>
      </div>
    </article>
    <?php $i++; } ?>
  </div>
</section>

<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
