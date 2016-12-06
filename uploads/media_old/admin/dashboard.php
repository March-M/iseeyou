<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/functions.php');
define('IS_UPLOAD_INCLUDED', TRUE);
include($_SERVER['DOCUMENT_ROOT'].'/admin/ajax/upload/index.php');

$check['exec_check'] = TRUE;
//on vérifie les résultats des tests de session valide
if(isset($check['exec_check']) && $check['exec_check'] != FALSE)
{
  $check['session'] = Check_session();
  $check['cookie'] = Check_cookie();

  //on vérifie les résultats des tests précédents
  if($check['cookie'] == FALSE OR $check['session'] == FALSE)
  {
    header('Location: /admin/index.php?no');
   exit();
  }
}
if (isset($_POST['press_name']) && isset($_POST['press_date']) && isset($_POST['thumbnail']) && isset($_POST['video_mp4']) && isset($_POST['video_webm']) && isset($_POST['video_ogg']) && isset($_POST['video_hash']) && isset($_POST['video_CSRF']) && !empty($_POST['press_name']) && !empty($_POST['press_date']) && !empty($_POST['thumbnail']) && !empty($_POST['video_mp4']) && !empty($_POST['video_webm']) && !empty($_POST['video_ogg']) && !empty($_POST['video_hash']) && !empty($_POST['video_CSRF']))
{

  //On vérifie le token
  if (verify_token($_POST['video_hash'], $_POST['video_CSRF']) == 1)
  {

    if(!isset($_SESSION['token']['article']) or empty($_SESSION['token']['article']))
      $_SESSION['token']['article'] = "";
    //On vérifie si le token du contenu n'est pas en session pour éviter les problème avec les refresh
    if ($_SESSION['token']['article'] != $_POST['video_hash'])
    {
      //On place le token qui à été utilisé et validé en session afin d'éviter la soumission de plusieurs formulaire
      $_SESSION['token']['article'] = $_POST['video_hash'];
      //On récupère le contenu et on le traite
      $req_article_add_comment = $mysql->prepare("INSERT INTO `media_list`(`id`, `press_name`, `press_date`, `thumbnails`, `video_mp4`, `video_webm`, `video_ogg`) VALUES (NULL,:pressname,:pressdate,:thumbnail,:mp4,:webm,:ogg)");
      $req_article_add_comment->bindValue(':pressname', $_POST['press_name'], PDO::PARAM_STR);
      $req_article_add_comment->bindValue(':pressdate', $_POST['press_date'], PDO::PARAM_STR);
      $req_article_add_comment->bindValue(':thumbnail', $_POST['thumbnail'], PDO::PARAM_STR);
      $req_article_add_comment->bindValue(':mp4', $_POST['video_mp4'], PDO::PARAM_STR);
      $req_article_add_comment->bindValue(':webm', $_POST['video_webm'], PDO::PARAM_STR);
      $req_article_add_comment->bindValue(':ogg', $_POST['video_ogg'], PDO::PARAM_STR);
      $req_article_add_comment->execute();
      $msg = Generate_Popup_result(0, 'L\'article à été enregistré avec succès !');

    }
    //Sinon on affiche l'erreur qui correspond
    else
      $msg = Generate_Popup_result(1, 'Ouups, Vous avez déjà envoyé cet article !');
  }
  //Sinon on affiche l'erreur qui correspond
  else
    $msg = Generate_Popup_result(2, 'Le token CSRF est périmé. Veuillez réessayer');
}
//Sinon on affiche l'erreur qui correspond
else if (isset($_POST['press_name']) OR isset($_POST['press_date']) OR isset($_POST['thumbnail']) OR isset($_POST['video_mp4']) OR isset($_POST['video_webm']) OR isset($_POST['video_ogg']))
{
  $msg = Generate_Popup_result(2, "Une erreur indéterminée est survenue lors de la soumission de votre article, il n'a pas été enregistré :(");
}
include($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');
//Afficher l'infobulle
if(isset($msg) && $msg != "")
{
  echo $msg."<br />";
}
?>
      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Ajouter une vidéo</h3>
      </div>
      <div class="panel-body">

                <form method="POST" action="" accept-charset="UTF-8">
          <div class="input-group-addon">Nom de l'article</div>
          <input type="text" id="press_name" class="form-control login-panel-input" name="press_name" placeholder="Nom de l'article de presse" required="" autofocus="">
          <div class="input-group-addon">Date</div>
            <input type="text" id="press_date" class="form-control login-panel-input" name="press_date" placeholder="Date de publication" required="" autofocus="">
          <div class="input-group-addon">Thumbnail</div>
            <input type="hidden" id="thumbnail" name="thumbnail" required="">
            <?php
              simple_upload_form('thumbnail');
            ?>
          <div class="input-group-addon">MP4</div>
            <input type="hidden" id="video_mp4" name="video_mp4" required="">
            <?php
              simple_upload_form('video_mp4');
            ?>
          <div class="input-group-addon">WEBM</div>
            <input type="hidden" id="video_webm" name="video_webm" required="">
            <?php
              simple_upload_form('video_webm');
            ?>
          <div class="input-group-addon">OGG</div>
            <input type="hidden" id="video_ogg" name="video_ogg" value="" required=""/>
            <?php
              simple_upload_form('video_ogg');
              $uniqid = uniqid();
             ?>
            <input type="hidden" name="video_CSRF" id="video_CSRF" value="<?php echo $uniqid; ?>" >
           <input type="hidden" name="video_hash" id="video_hash" value="<?php echo generate_token($uniqid); ?>" >
  <button type="submit" name="submit" class="btn btn-info btn-block">Ajouter</button>
        </form>
              </div>
    </div>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
