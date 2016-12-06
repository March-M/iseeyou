<?php
//On crée le define qui indique a l'upload handler qu'il est bien inclu en PHP et pas directement appelé, et on l'inclu
define('IS_UPLOAD_INCLUDED', TRUE);
include($_SERVER['DOCUMENT_ROOT']."/ajax/upload/index.php");

include($_SERVER['DOCUMENT_ROOT'].'/db.php');

//On récupère le nom de l'user qui à publié d'article de l'article
$req_iseeyou_data = $mysql->prepare("SELECT * FROM iseeyou_list");
$req_iseeyou_data->execute();

$count['number_img'] = 0;

while ($iseeyou_data = $req_iseeyou_data->fetch())
{
    $iseeyou[$count['number_img']]['id'] = $iseeyou_data['id'];
    $iseeyou[$count['number_img']]['date'] = $iseeyou_data['date'];
    $iseeyou[$count['number_img']]['by'] = $iseeyou_data['by'];
    $iseeyou[$count['number_img']]['stk'] = $iseeyou_data['sticker'];
    $iseeyou[$count['number_img']]['titre'] = $iseeyou_data['titre'];
    $iseeyou[$count['number_img']]['desc'] = $iseeyou_data['description'];
    $iseeyou[$count['number_img']]['img'] = $iseeyou_data['image'];
    $count['number_img']++;
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>I See You Project, Quand ca veut pas, ca veut pas.</title>

    <!-- Bootstrap core CSS -->
    <link href="/dist/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="/dist/css/font-awesome.css">
    
    <!-- Montserrat: http://www.google.com/fonts/#QuickUsePlace:quickUse/Family:Montserrat -->
    <!-- <link href='http://fonts.googleapis.com/css?family=Montserrat:700,100' rel='stylesheet' type='text/css'> -->
        
    <!-- Stylesheet -->
    <link href="/dist/css/style.css" rel="stylesheet">
    <!--[if lt IE 9]>
      <script src="js/respond.min.js"></script>
    <![endif]-->
    <style>
</style>
  </head>

  <body>
    <div id="navbar" class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/"><br><img src="/dist/img/logo.png" alt="logo" height="50" width="50">I SEE YOU<br><span class="subtext middleview">quand ca bug, ca bug</span></a>
      </div>
      <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          <!-- This hidden one just makes scrollspy reset when going above the team section -->
          <li><a class="scroll" href="#work"><i class="icon-briefcase"></i>Les images<span class="arrow-left"></span></a></li>
          <li><a class="scroll" href="#contact"><i class="icon-comments"></i>Contact<span class="arrow-left"></span></a></li>
        </ul>
      </div><!--/.nav-collapse -->
    </div>
    <div class="search affix-top">
      <i class="icon-search"></i>
      
      <form id="search" class="form-inline" role="form">
        <div class="form-group">
          <input class="form-control" id="exampleSearch" type="text">
        </div>
      </form>
    </div>
    <!-- Work -->
<?php if(isset($_GET['iseeyou_id']) && !empty($_GET['iseeyou_id']) && is_numeric($_GET['iseeyou_id']))
{


  $req_iseeyou_single_data = $mysql->prepare("SELECT * FROM iseeyou_list WHERE id = :id");
  $req_iseeyou_single_data->bindValue(':id', intval($_GET['iseeyou_id']), PDO::PARAM_INT);
  $req_iseeyou_single_data->execute();

  $iseeyou_single = $req_iseeyou_single_data->fetch();
?>
<div id="singleimg" class="section">
      <div class="fixed-wrapper"><h1 class="affix-top highview">Système public planté, signalement numéro <?php echo $iseeyou_single['id']; ?></h1><h1 class="affix-top lowview">Signalement n°: <?php echo $iseeyou_single['id']; ?></h1></div>
      <div class="container">
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">

          <!-- Wrapper for slides -->
          <div class="carousel-inner">
            <div class="item active">
                <img src="<?php echo $iseeyou_single['image']; ?>" class="img-responsive" alt="placeholder">
              <div class="carousel-caption">
              <div style="float: left; margin-right: 15px; margin-left: 15px; margin-top: 15px;" class="btn btn-danger" type="button"><?php echo $iseeyou_single['sticker']; ?></div>
              <div style="float: left; margin-top: 15px;" class="btn btn-warning" type="button"><?php echo "Le ".date("d/m/Y \à H\hi", $iseeyou_single['date']); ?></div>
              <div style="float: left; margin-right: 15px; margin-left: 15px; margin-top: 15px;" class="btn btn-info" type="button">Signalé par <?php echo $iseeyou_single['by']; ?></div><h2><?php echo $iseeyou_single['titre']; ?></h2>
                <p><?php echo $iseeyou_single['description']; ?></p>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
<?php 
}
?>
      <!-- Modal For Gallery -->      
      <div class="modals">

    <?php
    $i = 0;
    while ($i < $count['number_img'])
    {
    ?>
    <div class="modal fade" id="modal<?php echo $i+1; ?>" tabindex="-1" role="dialog">
      <div class="wrapper" >
        <img data-dismiss="modal" class="img-responsive" src="<?php echo $iseeyou[$i]['img']; ?>" alt="">
        <div style="background-color: #fff;">
          <div style="float: left; margin-right: 15px; margin-left: 15px; margin-top: 15px;" class="btn btn-danger" type="button"><?php echo $iseeyou[$i]['stk']; ?></div>
          <div style="float: left; margin-top: 15px;" class="btn btn-warning" type="button"><?php echo "Le ".date("d/m/Y \à H\hi", $iseeyou[$i]['date']); ?></div>
          <div style="float: left; margin-right: 15px; margin-left: 15px; margin-top: 15px;" class="btn btn-info" type="button">Par <?php echo $iseeyou[$i]['by']; ?></div>
          <h2 style="color: #000;" class="modal-title"><?php echo $iseeyou[$i]['titre']; ?></h2>
          <div class="description block" style="padding: 20px;">
            <p><?php echo $iseeyou[$i]['desc']; ?></p>
          </div>
        </div>  
      </div><!-- /.content -->      
    </div><!-- /.modal -->
    <?php
    $i++;
    }
    ?>


      </div>

    <div id="work" class="section">
      <div class="fixed-wrapper"><h1 class="affix-top highview">Les images de la collection</h1><h1 class="affix-top lowview">Les images</h1></div>
      <div class="container">      
        <div style="position: relative; height: 804.834px;" class="row masonry-grid">
        <!-- START BLOCK REFERENCE -->

    <?php
    $i = 0;
    while ($i < $count['number_img'])
    {
    ?>
          <div style="position: absolute; left: 0px; top: 0px; opacity: 1;" class="col-md-4 col-sm-4 col-xs-12 item">
            <div class="item-wrapper block">
              <a data-toggle="modal" href="#modal<?php echo $i+1; ?>" class="image">
                <span class="category_top"><?php echo "Le ".date("d/m/Y \à H\hi", $iseeyou[$i]['date']); ?></span>
                <img src="http://www.michelgaschet.science/image.php?i=<?php echo urlencode($iseeyou[$i]['img']); ?>" alt="image"> 
                <span class="category_bottom"><?php echo $iseeyou[$i]['stk']; ?></span>
                <span class="overlay"><span class="valign"></span></span>
              </a>
              <div class="description block">
                <h2 style="font-size: 25px;"><?php echo $iseeyou[$i]['titre']; ?></h2>
              </div>                        
            </div>
          </div>
    <?php
    $i++;
    }
    ?>
        <!-- STOP BLOCK REFERENCE -->
        </div>  
      </div>
      
    </div><!-- /.container -->    
    
    <!-- Contact Section -->
    <div id="contact" class="section">
      <div class="fixed-wrapper"><h1 class="affix-top highview">Proposer un écran planté au site</h1><h1 class="affix-top lowview">Contact</h1></div>
      <div class="container">
        
        <div class="content">
         
          
          <form role="form">
            <div class="form-group">
              <input class="form-control" id="exampleInputName" placeholder="Full Name" required="" type="text">
              <label for="exampleInputName"><i class="icon-tag"></i></label>
              <div class="clearfix"></div>
            </div>
            <div class="form-group">
              <input class="form-control" id="exampleInputEmail1" placeholder="Enter email" required="" type="email">
              <label for="exampleInputEmail1"><i class="icon-inbox"></i></label>
              <div class="clearfix"></div>
            </div>
            <div class="form-group textarea">
              <textarea rows="6" class="form-control" id="exampleInputMessage" placeholder="Write Message" required=""></textarea>
              <label for="exampleInputMessage"><i class="icon-pencil"></i></label>
              <div class="clearfix"></div>
            </div>
            <?php /*<div class="form-group textarea">
                <?php 
                simple_upload_form('content');
                ?>
              <label for="exampleInputPicture1"><i class="icon-picture"></i></label>
              <div class="clearfix"></div>
            </div>*/ ?>
            
            <button type="submit" class="btn btn-large">Envoyer le message</button>
          </form>
          
        </div>
        <br>
        
      </div>
    </div><!-- /.container -->    
    

    
    <div id="footer" class="section">
      <p>Made with ♥ by <a href="http://www.michelgaschet.gp/">Michel Gaschet</a></p>     
    </div>
    <!-- templace by WebDesignCrowd : http://webdesigncrowd.com/ -->   
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/dist/js/jquery-1.js"></script>
    <script src="/dist/js/jquery.js"></script>
    <script src="/dist/js/masonry.js"></script>
    <script src="/dist/js/imagesloaded.js"></script>
		<script src="/dist/js/bootstrap.js"></script>
    <script src="/dist/js/init.js"></script>
  

</body></html>