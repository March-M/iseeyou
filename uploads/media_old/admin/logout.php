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
include($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');
?>
      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Se déconnecter ?</h3>
      </div>
      <div class="panel-body">

                              <form action="/admin/index.php" method="post">
                <input type="hidden" name="Logout_confirm" id="Logout_confirm" value="U rly want to deconnect lel?" >
                <button name="submit" id="submit" type="submit" class="btn btn-info btn-sm">Oui, je veut me déconnecter</button>
              </form>
              </div>
    </div>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
