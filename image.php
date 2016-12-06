<?php

function calc_taille_img($max_width, $max_height, $img_link) {
   // On récupère la taille de l'image
   $sizeimg = GetImageSize($img_link);  
   $source_width = $sizeimg[0]; // largeur
   $source_height = $sizeimg[1]; // hauteur

   // On teste les dimensions tenant dans la zone
   $test_height = round(($max_width / $source_width) * $source_height);
   $test_width = round(($max_height / $source_height) * $source_width);

   // Si max_height non précisé (0)
   if(!$max_height) $max_height = $test_height;
   // Sinon si max_width final non précisé (0)
   elseif(!$max_width) $max_width = $test_width;
   // Sinon teste quel redimensionnement tient dans la zone
   elseif($test_height>$max_height) $max_width = $test_width;
   else $max_height = $test_height;
   //Retourne les valeurs
   $result = array();
   $result['width'] = $max_width;
   $result['height'] = $max_height;
   return $result;
}

$img = str_replace('../', '', $_GET['i']);
$img = str_replace('./', '', $img);
$img = $_SERVER["DOCUMENT_ROOT"].$img;

// On defini le header
if(exif_imagetype($img) == IMAGETYPE_PNG)
{
   header('Content-type: ' .image_type_to_mime_type(IMAGETYPE_PNG));
   $source = imagecreatefrompng($img); // La photo est la source

   //calcul de la taille de l'image de destination en gardant les proportions
   $largeur_source = imagesx($source);
   $hauteur_source = imagesy($source);
   $taille_destination = calc_taille_img(337, 0, $img);

   //on crée l'image de destination
   $destination = imagecreatetruecolor($taille_destination['width'], $taille_destination['height']); // On crée la miniature vide
 
   // On crée la miniature
   imagecopyresampled($destination, $source, 0, 0, 0, 0, $taille_destination['width'], $taille_destination['height'], $largeur_source, $hauteur_source);
 
   // Le chemin vers le fichier de sauvegarde n'est pas défini, le flux brut de l'image sera affiché directement.
   imagepng($destination);
}
else if(exif_imagetype($img) == IMAGETYPE_JPEG)
{
   header('Content-type: ' .image_type_to_mime_type(IMAGETYPE_PNG));
   $source = imagecreatefromjpeg($img); // La photo est la source

   //calcul de la taille de l'image de destination en gardant les proportions
   $largeur_source = imagesx($source);
   $hauteur_source = imagesy($source);
   $taille_destination = calc_taille_img(337, 0, $img);

   //on crée l'image de destination
   $destination = imagecreatetruecolor($taille_destination['width'], $taille_destination['height']); // On crée la miniature vide
 
   // On crée la miniature
   imagecopyresampled($destination, $source, 0, 0, 0, 0, $taille_destination['width'], $taille_destination['height'], $largeur_source, $hauteur_source);
 
   // Le chemin vers le fichier de sauvegarde n'est pas défini, le flux brut de l'image sera affiché directement.
   imagejpeg($destination);
}
else{
   die();
}