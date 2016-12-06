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
//On initialise certaine variable qui ne seront pas forcément utilisé mais qui sont présente dans le code pour eviter les erreurs de type "NOTICE Undefined"
$msg = "";

//On récupère les infos de l'user en bdd
$req_profil_data = $mysql->prepare("SELECT * FROM media_users WHERE id=:id");
$req_profil_data->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
$req_profil_data->execute();
$profil_data = $req_profil_data->fetch();

if((isset($_POST['actual_password'])) && (sha1($_POST['actual_password']) == $profil_data['password']))
{

  if (verify_token( $_POST['adduser_hash'], $_POST['adduser_CSRF']) == 1)
  {
    if(isset($_POST['actual_password']) && !empty($_POST['actual_password']) && isset($_POST['new_password']) && !empty($_POST['new_password']) && isset($_POST['confirm_new_password']) && !empty($_POST['confirm_new_password']) && isset($_POST['adduser_hash']) && !empty($_POST['adduser_hash']) && isset($_POST['adduser_CSRF']) && !empty($_POST['adduser_CSRF']) && isset($_POST['pseudo']) && !empty($_POST['pseudo']) && isset($_POST['mail']) && !empty($_POST['mail']))
    {
      if($_POST['confirm_new_password'] == $_POST['new_password'])
      {
        $req_changelog_user_add = $mysql->prepare("INSERT INTO `media_users`(`id`, `pseudo`, `photo_link`, `email`, `password`, `lastlogin`, `lastip`, `lastiphost`, `enabled`, `is_OTP_activated`, `secret`) VALUES (NULL,:pseudo,:link,:mail,:pass,0,'1.3.3.7','1.3.3.7',1,0,'')");
        $req_changelog_user_add->bindValue(':pseudo', htmlspecialchars($_POST['pseudo'], ENT_NOQUOTES ), PDO::PARAM_STR);
        $req_changelog_user_add->bindValue(':link', '/admin/users/'.htmlspecialchars($_POST['pseudo'], ENT_NOQUOTES ).'.jpg', PDO::PARAM_STR);
        $req_changelog_user_add->bindValue(':mail', htmlspecialchars($_POST['mail'], ENT_NOQUOTES ), PDO::PARAM_STR);
        $req_changelog_user_add->bindValue(':pass', sha1($_POST['new_password']), PDO::PARAM_STR);
        $req_changelog_user_add->execute();
        //log_update("Changement de mot de passe effectué avec succès sur le compte numéro ".$_SESSION['userid']."");
        $msg = Generate_Popup_result(0, 'Ajout du compte effectué avec succès');
      }
      else
      {
        //log_update("Tentative de changement de mot de passe, le nouveau mdp ne correspond pas à sa confirmation, compte numéro ".$_SESSION['userid']."");
        $msg = Generate_Popup_result(1, 'Les mots de passe ne correspondent pas');
      }
    }
    else if (isset($_POST['myprofil_hash']) OR isset($_POST['myprofil_CSRF']) OR isset($_POST['pseudo']))
    {
      //log_update("Tentative de mise à jour du profil, formulaire minimal incomplet, compte numéro ".$_SESSION['userid']."");
      $msg = Generate_Popup_result(2, 'Une erreur indéterminée est survenue lors de la soumission de création du compte, il n\'a pas été enregistré :(');
    }
  }
  else
  {
    //("Mise à jour des informations générale de profil, token CSRF non conforme, compte numéro ".$_SESSION['userid']."");
    $msg = Generate_Popup_result(1, 'Le token CSRF est périmé. Veuillez réessayer');
  }

  /***********************************************************************/
  /*  Fin de la fonction d'enregistrement des nouvelles infos de profil  */
  /***********************************************************************/

}
include($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');

//Afficher l'infobulle
if(isset($msg) && $msg != "")
{
  echo $msg."<br />";
}
if(isset($pass) && $pass != "")
{
  echo $pass."<br />";
}
if(isset($otp) && $otp != "")
{
  echo $otp."<br />";
}
?>
      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Modifier un compte</h3>
      </div>
      <div class="panel-body">

                <form method="POST" action="" accept-charset="UTF-8">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Pseudo : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Pseudo : </div>
                        <input type="text" class="form-control" name="pseudo" id="pseudo" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Email : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Email : </div>
                        <input type="text" class="form-control" name="mail" id="mail" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Mot de passe : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Mot de passe : </div>
                        <input type="password" class="form-control" name="new_password" id="new_password" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Mot de passe (confirmation) : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Mot de passe (confirmation) : </div>
                        <input type="password" class="form-control" name="confirm_new_password" id="confirm_new_password" required />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="well well-sm login-panel-heading-color">
                      <div class="form-group">
                        <strong style="font-size: 15px">Pour des raisons évidente de sécuritée, vous devez entrer votre mot de passe personnel afin de valider toute modification de ce profil : </strong><br />
                        <input type="password" value="" id="actual_password" name="actual_password" class="form-control" placeholder="Tapez votre mot de passse actuel">
                        <br />
                        <?php
                          $uniqid = uniqid();
                        ?>
                        <input type="hidden" name="adduser_CSRF" id="adduser_CSRF" value="<?php echo $uniqid; ?>">
                        <input type="hidden" name="adduser_hash" id="adduser_hash" value="<?php echo generate_token($uniqid); ?>">
                        <button type="submit" class="btn btn-success btn-sm">
                          <span class="glyphicon glyphicon-floppy-disk"></span>Enregistrer</button>
                        </div>
                      </div>
                    </div>
                  </div>
        </form>
              </div>
    </div>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
