<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/functions.php');

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
$pass = "";
$otp = "";

//On récupère les infos de l'user en bdd
$req_profil_data = $mysql->prepare("SELECT * FROM media_users WHERE id=:id");
$req_profil_data->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
$req_profil_data->execute();
$profil_data = $req_profil_data->fetch();

if((isset($_POST['actual_password'])) && (sha1($_POST['actual_password']) == $profil_data['password']))
{


  if (verify_token( $_POST['myprofil_hash'], $_POST['myprofil_CSRF']) == 1)
  {
    /*****************************************************************/
    /*     Début de la fonction d'activation de la connection OTP    */
    /*****************************************************************/
    if(isset($_POST['use_otp']) && $_POST['use_otp'] == 1 && $_POST['use_otp'] != $profil_data['is_OTP_activated'])
    {
      $authenticator = new GoogleAuthenticator();
      $secret = $authenticator->createSecret();
      $req_changelog_user_update = $mysql->prepare("UPDATE `media_users` SET secret=:secret, is_OTP_activated=1 WHERE id=:id");
      $req_changelog_user_update->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
      $req_changelog_user_update->bindValue(':secret', $secret, PDO::PARAM_STR);
      $req_changelog_user_update->execute();
      //log_update("Activation de la connexion OTP via google authenticator pour le compte numéro ".$_SESSION['userid']."");
      $otp = Generate_Popup_result(0, 'Activation de la connexion OTP effectuée !');
    }
    elseif(isset($_POST['use_otp']) && $_POST['use_otp'] == 0 && $_POST['use_otp'] != $profil_data['is_OTP_activated'])
    {
      $req_changelog_user_update = $mysql->prepare("UPDATE `media_users` SET secret=NULL, is_OTP_activated=0 WHERE id=:id");
      $req_changelog_user_update->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
      $req_changelog_user_update->execute();
      //log_update("Désactivation de la connexion OTP via google authenticator pour le compte numéro ".$_SESSION['userid']."");
      $otp = Generate_Popup_result(0, 'Désactivation de la connexion OTP effectuée !');
    }

    /*****************************************************************/
    /*      Fin de la fonction d'activation de la connection OTP     */
    /*****************************************************************/

    /*****************************************************************/
    /* Début de la fonction d'enregistrement du nouveau mot de passe */
    /*****************************************************************/
    if((isset($_POST['change_password']) && $_POST['change_password'] == 1) && (isset($_POST['actual_password']) && !empty($_POST['actual_password']) && isset($_POST['new_password']) && !empty($_POST['new_password']) && isset($_POST['confirm_new_password']) && !empty($_POST['confirm_new_password'])))
    {
      if($_POST['confirm_new_password'] == $_POST['new_password'])
      {
        $req_changelog_user_update = $mysql->prepare("UPDATE `media_users` SET password=:pass WHERE id=:id");
        $req_changelog_user_update->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
        $req_changelog_user_update->bindValue(':pass', sha1($_POST['new_password']), PDO::PARAM_STR);
        $req_changelog_user_update->execute();
        //log_update("Changement de mot de passe effectué avec succès sur le compte numéro ".$_SESSION['userid']."");
        $pass = Generate_Popup_result(0, 'Changement de mot de passe effectué, vous allez être déconnecté.<script>self.setTimeout("self.location.href = /index.php;", 5000);</script>');
      }
      else
      {
        //log_update("Tentative de changement de mot de passe, le nouveau mdp ne correspond pas à sa confirmation, compte numéro ".$_SESSION['userid']."");
        $pass = Generate_Popup_result(1, 'Les mots de passe ne correspondent pas');
      }
    }

    /*****************************************************************/
    /*  Fin de la fonction d'enregistrement du nouveau mot de passe  */
    /*****************************************************************/


    /***********************************************************************/
    /* Début de la fonction d'enregistrement des nouvelles infos de profil */
    /***********************************************************************/

    if (isset($_POST['myprofil_hash']) && !empty($_POST['myprofil_hash']) && isset($_POST['myprofil_CSRF']) && !empty($_POST['myprofil_CSRF']) && isset($_POST['pseudo']) && !empty($_POST['pseudo']))
    {
      //On récupère les données et on les traitent
      $pseudo = $_POST['pseudo'];
      $pseudo = htmlspecialchars($pseudo, ENT_NOQUOTES ) ;
      $req_changelog_update_user = $mysql->prepare("UPDATE `media_users` SET pseudo=:pseudo WHERE id=:id");
      $req_changelog_update_user->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
      $req_changelog_update_user->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
      $req_changelog_update_user->execute();
      //log_update("Mise à jour des informations générale de profil effectué avec succès, compte numéro ".$_SESSION['userid']."");
      $msg = Generate_Popup_result(0, 'Votre profil a été mis à jour avec succès !');
    }
    else if (isset($_POST['myprofil_hash']) OR isset($_POST['myprofil_CSRF']) OR isset($_POST['pseudo']))
    {
      //log_update("Tentative de mise à jour du profil, formulaire minimal incomplet, compte numéro ".$_SESSION['userid']."");
      $msg = Generate_Popup_result(2, 'Une erreur indéterminée est survenue lors de la soumission de la mise a jour de votre profil, il n\'a pas été enregistré :(');
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

$req_profil_view_data = $mysql->prepare("SELECT * FROM media_users WHERE id=:id");
$req_profil_view_data->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
$req_profil_view_data->execute();
$profil_view_data = $req_profil_view_data->fetch();

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
        <h3 class="login-panel-title center">Modifier son profil</h3>
      </div>
      <div class="panel-body">

                <form method="POST" action="" accept-charset="UTF-8">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Pseudo : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Pseudo : </div>
                        <input type="text" class="form-control" name="pseudo" id="pseudo" value="<?php echo $profil_view_data['pseudo']; ?>" required />
                      </div>
                    </div>
                    <?php
                    if($profil_view_data['is_OTP_activated'])
                    {
                      $authenticator = new GoogleAuthenticator();
                      $domain = 'media.42.fr/admin/'; //Your Website
                      $qrCodeUrl = $authenticator->getQRCodeGoogleUrl($profil_view_data['pseudo'], $domain, $profil_view_data['secret']);
                      echo "<i>scanner ce QR code dans google Authenticator pour ajouter le compte</i><br /><img src='".$qrCodeUrl."'><br /><br />";
                      $otp_0 = '';
                      $otp_1 = 'selected';
                    }
                    else
                    {
                      $otp_0 = 'selected';
                      $otp_1 = '';
                    }
                    ?>
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Activer la double autentification : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Activer la double autentification : </div>
                        <select class="form-control form-control-select" id="use_otp" name="use_otp" data-toggle="tooltip" data-html="true" data-original-title="Activer l'autentification à deux facteurs via Google Authenticator!">
                          <option value="0" <?php echo $otp_0; ?>>Non</option>
                          <option value="1" <?php echo $otp_1; ?>>Oui</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="sr-only" for="exampleInputAmount">Changer son mot de passe : </label>
                      <div class="input-group">
                        <div class="input-group-addon">Changer son mot de passe : </div>
                        <select class="form-control form-control-select" id="change_password" name="change_password" data-toggle="tooltip" data-html="true" data-original-title="<b>/!\ ATTENTION :</b> Changer de mot de passe entraine une déconnexion automatique dès la mise a jour de celui si !">
                          <option value="0" selected>Non</option>
                          <option value="1">Oui</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group hidden" id="changepass">
                      <label class="sr-only" for="exampleInputAmount">Pseudo : </label>
                      <div class="input-group form-group">
                      <div class="input-group-addon">Entrez le nouveau mot de passe : &nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <input type="password" value="" id="new_password" name="new_password" class="form-control" <br />
                      </div>
                      <div class="input-group form-group">
                        <div class="input-group-addon">Confirmez le nouveau mot de passe : </div>
                        <input type="password" value="" id="confirm_new_password" name="confirm_new_password" class="form-control" <br />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="well well-sm login-panel-heading-color">
                      <div class="form-group">
                        <strong style="font-size: 15px">Pour des raisons évidente de sécuritée, vous devez entrer votre mot de passe actuel afin de valider toute modification de votre profil : </strong><br />
                        <input type="password" value="" id="actual_password" name="actual_password" class="form-control" placeholder="Tapez votre mot de passse actuel">
                        <br />
                        <?php
                          $uniqid = uniqid();
                        ?>
                        <input type="hidden" name="myprofil_CSRF" id="myprofil_CSRF" value="<?php echo $uniqid; ?>">
                        <input type="hidden" name="myprofil_hash" id="myprofil_hash" value="<?php echo generate_token($uniqid); ?>">
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
