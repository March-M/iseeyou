<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/functions.php');

if(isset($_POST['Logout_confirm']) && $_POST['Logout_confirm'] == "U rly want to deconnect lel?")
{
  Destroy_Session();
}

if(isset($_SESSION['OTPcheck']) && $_SESSION['OTPcheck'] == 'to_check' && isset($_POST['otp_code']) && !empty($_POST['otp_code']))
{
  $authenticator = new GoogleAuthenticator();
  if(Check_session() == TRUE)
    {
      $tolerance = 0;

      $req_user_data1 = $mysql->prepare("SELECT * FROM media_users WHERE id=:id ");
      $req_user_data1->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
      $req_user_data1->execute();
      $user_data_nb1 = $req_user_data1->rowCount();
      $user_data1 = $req_user_data1->fetch();

      if(isset($_COOKIE['userid']))
      {
        $req_user_data2 = $mysql->prepare("SELECT * FROM media_users WHERE id=:id ");
        $req_user_data2->bindValue(':id', $_COOKIE['userid'], PDO::PARAM_STR);
        $req_user_data2->execute();
        $user_data_nb2 = $req_user_data2->rowCount();
        $user_data2 = $req_user_data2->fetch();
      }

      $checkResult = $authenticator->verifyCode($user_data1['secret'], intval($_POST['otp_code']), $tolerance);

      if ($checkResult && ($user_data_nb1 != "0" OR (isset($user_data_nb2) && $user_data_nb2 != "0")))
      {
        $_SESSION['OTPcheck'] = 'passed';
        header("Location: /admin/dashboard.php?otp_success");
        die();
      }
      else
      {
        header("Location: /admin/index.php?otp_err");
        die();
      }
    }
}

if (isset($_SESSION['OTPcheck']) && $_SESSION['OTPcheck'] == 'passed')
{
  echo 'OTP PASSED';
  if(isset($_SESSION['userid']) OR isset($_SESSION['userpass']))
  {
    if(Check_session() == TRUE)
    {
        $req_user_data1 = $mysql->prepare("SELECT * FROM media_users WHERE id=:id ");
        $req_user_data1->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
        $req_user_data1->execute();
        $user_data_nb1 = $req_user_data1->rowCount();
        $user_data1 = $req_user_data1->fetch();

        if(isset($_COOKIE['userid']))
        {
          $req_user_data2 = $mysql->prepare("SELECT * FROM media_users WHERE id=:id ");
          $req_user_data2->bindValue(':id', $_COOKIE['userid'], PDO::PARAM_STR);
          $req_user_data2->execute();
          $user_data_nb2 = $req_user_data2->rowCount();
          $user_data2 = $req_user_data2->fetch();
        }

        if ($user_data_nb1 != "0" OR (isset($user_data_nb2) && $user_data_nb2 != "0"))
        {
          header("Location: /admin/dashboard.php?sess_or_cookie_auth");
          die();
        }
      }
  }
}

if ((isset($_POST['pseudo']) && !empty($_POST['pseudo'])) && (isset($_POST['password']) && !empty($_POST['password'])))
{
  //On initialise les deux variable : On les nettois et on les prépare pour les future requêtes SQL
  $pseudo = htmlspecialchars($_POST['pseudo'], ENT_QUOTES );
  $mdp = sha1($_POST['password']);

  //Sécuritée 1 : on vérifie si un compte correspondant à l'username existe bien. Pourquoi ne pas directement tester avec le mdp? En cas de login doublon, en effet ici on vérifie qu'il n'y ai pas ni plus ni moins de 1 entrée.
  $req_user_exist = $mysql->prepare("SELECT COUNT(*) FROM media_users WHERE pseudo=:pseudo");
  $req_user_exist->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
  $req_user_exist->execute();
  $verif_user_exist = $req_user_exist->fetch();

  // si le résultat est bien égal à 1 (car il ne peut pas y avoir deux utilisateurs avec le même username), alors le compte existe. Sinon il y a un problème alors on met fin à la tentative de connexion par sécuritée.
  if ($verif_user_exist[0] == 1)
  {
    //On sélectionne les informations lié à l'utilisateur en bdd. Par sécurité on ajoute le mot de passse crypté a la vérification, même si la correspondance est revérifiée un peu plus loin
    $req_user_data = $mysql->prepare("SELECT * FROM media_users WHERE pseudo=:pseudo");
    $req_user_data->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
    $req_user_data->execute();
    $user_data_nb = $req_user_data->rowCount();
    $user_data = $req_user_data->fetch();

    //La on va vérifier si l'user est bien actif et n'a pas été désactivé.
    if ($user_data['enabled'] == "1")
    {

      //On est jamais trop parano : on re-vérifie la validitée du mot de passe
      if ($mdp == $user_data['password'])
      {
        $_SESSION['userpass'] = make_session_signature($mdp, $user_data['pseudo']); //On met en session ce qui va servir a vérifier si c'est bien l'user qui est connecté : couple sha1(mdp.sha1(username))
        $_SESSION['userid'] = $user_data['id']; //On met en session l'ID de l'utilisateur, pour permettre la modification de son compte

        //On initialise les variables des différents token pour éviter les erreur de type "NOTICE Undefined"
        $_SESSION['token'] = "";


        // génère le cookie
        if (isset($_POST['remember']) && $_POST['remember'] == "remember-me" )
        {
          setcookie("userpass", make_session_signature($mdp, $user_data['pseudo']), time()+60*60*24*100, "/");
          setcookie("userid", $user_data['id'], time()+60*60*24*100, "/");
        }

        //Mise à jour de la dernière IP/HOST de connexion dans la table user
        $req_user_ip_update = $mysql->prepare("UPDATE `media_users` SET lastlogin=UNIX_TIMESTAMP(), lastip=:ip, lastiphost=:host WHERE id=:id");
        $req_user_ip_update->bindValue(':id', $user_data['id'], PDO::PARAM_STR);
        $req_user_ip_update->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $req_user_ip_update->bindValue(':host', gethostbyaddr($_SERVER['REMOTE_ADDR']), PDO::PARAM_STR);
        $req_user_ip_update->execute();


        if($user_data['is_OTP_activated'] == 1)
        {
         define('OTP_CHECK', TRUE);
         $_SESSION['OTPcheck'] = 'to_check';
        }
        else
        {
          //Mise à jour du log de connexion
          //log_update("Connexion réussie pour l'utilisateur &quot;".$user_data['id']."&quot;, via l'ip &quot;".$_SERVER['REMOTE_ADDR']."&quot;.");
          header("Location: /admin/dashboard.php");
          die();
         }
      }
      else //MDP incorrect
      {
        header("Location: /admin/index.php?MDPno");
        die();
      }
    }
    else //compte désactivé
    {
      header("Location: /admin/index.php?disabled");
      die();
    }
  }
  else //username incorect
  {
    header("Location: /admin/index.php?USERNAMEno");
    die();
  }
}
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
  <div class="row">
  <?php
  if (defined('OTP_CHECK'))
  {
    ?>
      <div class="login-panel">
      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Vérification Autentification à deux facteurs</h3>
      </div>
      <div class="panel-body">
        <form method="POST" action="" accept-charset="UTF-8">
          <input type="text" class="form-control" placeholder="OTP code" id="otp_code" name="otp_code">
          <span class="glyphicon glyphicon-time form-control-feedback"></span>
          <button type="submit" name="submit" class="btn btn-info btn-block">Connexion</button>
        </form> 
      </div>
    </div>
    <?php
  }
  else
  {
  ?>
  <div class="login-panel">
      <div class="login-panel-heading">
        <h3 class="login-panel-title center">Se connecter</h3>
      </div>
      <div class="panel-body">

                <form method="POST" action="" accept-charset="UTF-8">
          <input type="text" id="pseudo" class="form-control login-panel-input" name="pseudo" placeholder="Pseudo" required="" autofocus="">
          <input type="password" id="password" class="form-control login-panel-input" name="password" placeholder="Mot de passe" required="">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="remember" id="remember" value="remember-me"> Se souvenir de moi
            </label>
          </div>
          <button type="submit" name="submit" class="btn btn-info btn-block">Connexion</button>
        </form> 
              </div>
    </div>
    <?php } ?>
  </div>
</div>
</section>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
?>
