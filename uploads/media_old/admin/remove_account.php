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

if(isset($_POST['deluser_confirm']) && $_POST['deluser_confirm'] == "U rly want to deluser lel?")
{
  if (verify_token( $_POST['deluser_hash'], $_POST['deluser_CSRF']) == 1)
  {
      $req_del_user = $mysql->prepare("DELETE FROM `media_users` WHERE `id` = :id");
      $req_del_user->bindValue(':id', intval($_GET['id']), PDO::PARAM_STR);
      $req_del_user->execute();
      
      $msg = Generate_Popup_result(0, 'Compte supprimé avec succès !');

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
      $req_duser_data = $mysql->prepare("SELECT pseudo FROM media_users WHERE id=:id ");
      $req_duser_data->bindValue(':id', intval($_GET['id']), PDO::PARAM_STR);
      $req_duser_data->execute();
      $duser_data = $req_duser_data->fetch();
?>

      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Supprimer le compte "<?php echo $duser_data['pseudo']; ?>" ?</h3>
      </div>
      <div class="panel-body">
                              <form action="/admin/remove_account.php?id=<?php echo intval($_GET['id']); ?>" method="post">
                <input type="hidden" name="deluser_confirm" id="deluser_confirm" value="U rly want to deluser lel?" >
                        <?php
                          $uniqid = uniqid();
                        ?>
                        <input type="hidden" name="deluser_CSRF" id="deluser_CSRF" value="<?php echo $uniqid; ?>">
                        <input type="hidden" name="deluser_hash" id="deluser_hash" value="<?php echo generate_token($uniqid); ?>">
                <button name="submit" id="submit" type="submit" class="btn btn-info btn-sm">Oui, je veut supprimer ce compte</button>
              </form>
              </div>
    </div>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
