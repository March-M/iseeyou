<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/functions.php');

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

if(isset($_POST['delvid_confirm']) && $_POST['delvid_confirm'] == "U rly want to delvid lel?")
{
  if (verify_token( $_POST['delvid_hash'], $_POST['delvid_CSRF']) == 1)
  {
      $req_del_user = $mysql->prepare("DELETE FROM `media_list` WHERE `id` = :id");
      $req_del_user->bindValue(':id', intval($_GET['id']), PDO::PARAM_STR);
      $req_del_user->execute();
      
      $msg = Generate_Popup_result(0, 'Vidéo supprimée avec succès !');

    }
    //Sinon on affiche l'erreur qui correspond
    else
      $msg = Generate_Popup_result(1, 'Token CSRF périmé !');
}
include($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');

//Afficher l'infobulle
if(isset($msg) && $msg != "")
{
  echo $msg."<br />";
}
$req_dvid_data = $mysql->prepare("SELECT press_name FROM media_list WHERE id=:id ");
$req_dvid_data->bindValue(':id', intval($_GET['id']), PDO::PARAM_STR);
$req_dvid_data->execute();
$vid_data = $req_dvid_data->fetch();
?>

      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Supprimer la vidéo "<?php echo $vid_data['press_name']; ?>" ?</h3>
      </div>
      <div class="panel-body">
                              <form action="/admin/remove_video.php?id=<?php echo intval($_GET['id']); ?>" method="post">
                <input type="hidden" name="delvid_confirm" id="delvid_confirm" value="U rly want to delvid lel?" >
                        <?php
                          $uniqid = uniqid();
                        ?>
                        <input type="hidden" name="delvid_CSRF" id="delvid_CSRF" value="<?php echo $uniqid; ?>">
                        <input type="hidden" name="delvid_hash" id="delvid_hash" value="<?php echo generate_token($uniqid); ?>">
                <button name="submit" id="submit" type="submit" class="btn btn-info btn-sm">Oui, je veut supprimer cette vidéo</button>
              </form>
              </div>
    </div>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
