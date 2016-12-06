<?php
/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   upload.php                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: mgaschet <mgaschet@student.42.fr>          +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/01/11 04:42:26 by mgaschet          #+#    #+#             */
/*   Updated: 2016/01/13 00:23:33 by mgaschet         ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

/*
 * Upload.php
 * Ajax upload file
 * Upload.php by @michel_gaschet
 *
 * This script use the "jQuery File Upload Plugin" UploadHandler.php file
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * "jQuery File Upload Plugin" Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * "jQuery File Upload Plugin" Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);

//J'ai réellement besoin d'expliquer ça :v ?
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/functions.php');

//On vérifie qu'il est bien connecté
$check['session'] = Check_session();
$check['cookie'] = Check_cookie();

//on vérifie les résultats des tests précédents
if($check['cookie'] == FALSE OR $check['session'] == FALSE)
	die("You can't access to this page if you're not logged.\n\nVous ne pouvez pas accéder à cette page si vous n'êtes pas connecté.");

//On crée le define qui indique a l'upload handler qu'il est bien inclu en PHP et pas directement appelé, et on l'inclu
define('IS_UPLOADHANDLER_INCLUDED', TRUE);
require('UploadHandler.php');

//On récupère d'abord le lien complet du dossier web
$directory = $_SERVER['DOCUMENT_ROOT']."/uploads/";
$url = "/uploads/".date("Y")."/".date("m")."/".date("d")."/";

//On vérifie que le dossier de l'année en cour existe, s'il n'existe pas on le crée
if(!is_dir($directory .= date("Y")."/"))
	mkdir($directory);

//On vérifie que le dossier du mois en cour existe, s'il n'existe pas on le crée
if(!is_dir($directory .= date("m")."/"))
	mkdir($directory);

//On vérifie que le dossier du jour en cour existe, s'il n'existe pas on le crée
if(!is_dir($directory .= date("d")."/"))
	mkdir($directory);

$options = array('upload_dir'=>$directory, 'upload_url'=>$url);

$upload_handler = new UploadHandler($options);
