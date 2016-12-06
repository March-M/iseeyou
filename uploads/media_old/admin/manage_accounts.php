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

    if(!isset($_SESSION['token']['video']) or empty($_SESSION['token']['video']))
      $_SESSION['token']['video'] = "";
    //On vérifie si le token du contenu n'est pas en session pour éviter les problème avec les refresh
    if ($_SESSION['token']['video'] != $_POST['video_hash'])
    {
      //On place le token qui à été utilisé et validé en session afin d'éviter la soumission de plusieurs formulaire
      $_SESSION['token']['video'] = $_POST['video_hash'];
      //On récupère le contenu et on le traite
      $req_video_add_comment = $mysql->prepare("INSERT INTO `media_list`(`id`, `press_name`, `press_date`, `thumbnails`, `video_mp4`, `video_webm`, `video_ogg`) VALUES (NULL,:pressname,:pressdate,:thumbnail,:mp4,:webm,:ogg)");
      $req_video_add_comment->bindValue(':pressname', $_POST['press_name'], PDO::PARAM_STR);
      $req_video_add_comment->bindValue(':pressdate', $_POST['press_date'], PDO::PARAM_STR);
      $req_video_add_comment->bindValue(':thumbnail', $_POST['thumbnail'], PDO::PARAM_STR);
      $req_video_add_comment->bindValue(':mp4', $_POST['video_mp4'], PDO::PARAM_STR);
      $req_video_add_comment->bindValue(':webm', $_POST['video_webm'], PDO::PARAM_STR);
      $req_video_add_comment->bindValue(':ogg', $_POST['video_ogg'], PDO::PARAM_STR);
      $req_video_add_comment->execute();
      $msg = Generate_Popup_result(0, 'La Vidéo à été enregistré avec succès !');

    }
    //Sinon on affiche l'erreur qui correspond
    else
      $msg = Generate_Popup_result(1, 'Ouups, Vous avez déjà envoyé cet video !');
  }
  //Sinon on affiche l'erreur qui correspond
  else
    $msg = Generate_Popup_result(2, 'Le token CSRF est périmé. Veuillez réessayer');
}
//Sinon on affiche l'erreur qui correspond
else if (isset($_POST['press_name']) OR isset($_POST['press_date']) OR isset($_POST['thumbnail']) OR isset($_POST['video_mp4']) OR isset($_POST['video_webm']) OR isset($_POST['video_ogg']))
{
  $msg = Generate_Popup_result(2, "Une erreur indéterminée est survenue lors de la soumission de votre video, il n'a pas été enregistré :(");
}
include($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');

//Afficher l'infobulle
if(isset($msg) && $msg != "")
{
  echo $msg."<br />";
}
?>
      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Gérer les comptes</h3>
      </div>
      <div class="panel-body">
      <table class="table table-striped" style="table-layout: fixed; word-wrap: break-word;">
        <thead>
          <tr>
            <th>#</th>
            <th>Pseudo</th>
            <th>Mail</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php

          //On va récupérer les infos sur les videos en BDD
          $req_data_users = $mysql->prepare("SELECT * FROM media_users ORDER BY id DESC");
          $req_data_users->execute();

          //On traite le tout pour l'affichage
          while ($data_users = $req_data_users->fetch())
          {
            ?>

            <tr>
              <td><?php echo $data_users['id']; ?></td>
              <td><?php echo $data_users['pseudo']; ?></td>
              <td><?php echo $data_users['email'] ?></td>
              <td>Dernier login <?php echo PrettyDate($data_users['lastlogin'], 1, 1, 1, 1, 1); ?></td>
              <td><?php echo $data_users['lastip'] ?> ( <?php echo $data_users['lastiphost'] ?> )</td>
              <td><a href="/admin/edit_account.php?id=<?php echo $data_users['id']; ?>" class="btn btn-success btn-sm" role="button">Modifier</a> <a href="/admin/remove_account.php?id=<?php echo $data_users['id']; ?>" class="btn btn-danger btn-sm" role="button">Supprimer</a></td>
              <?php
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
