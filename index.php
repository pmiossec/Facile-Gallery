<?php 
require("conf.php");

//error_reporting(E_ALL); // afficher les erreurs
error_reporting(0); // ne pas afficher les erreurs

///////////////////////////////////////////////////////////////////////
//fonction qui renomme les dossiers comprenant des caractères interdits
///////////////////////////////////////////////////////////////////////
function scan_invalid_char($dir2scan) {
	if ($handle = opendir($dir2scan)) {
	   while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && eregi("[]\[\àáâãäåÀÁÂÃÄÅÈÉÊËèéêëÌÍÎÏìíîïÒÓÔÕÖòóôõöÙÚÛÜùúûü.!@#$%^&*+{}()'=$]", $file) && is_dir($dir2scan.'/'.$file)) {
				$newfilename = $file;
				$newfilename = eregi_replace("[]\[\.!@#$%^&*+{}()'=$]", "_", $newfilename);
				$newfilename = eregi_replace("[àáâãäåÀÁÂÃÄÅ]", "a", $newfilename);
				$newfilename = eregi_replace("[ÈÉÊËèéêë]", "e", $newfilename);
				$newfilename = eregi_replace("[Çç]", "c", $newfilename);
				$newfilename = eregi_replace("[ÌÍÎÏìíîï]", "i", $newfilename);
				$newfilename = eregi_replace("[ÒÓÔÕÖòóôõö]", "o", $newfilename);
				$newfilename = eregi_replace("[ÙÚÛÜùúûü]", "u", $newfilename);
			   rename($dir2scan.'/'.$file, $dir2scan.'/'.$newfilename);
		   }
	   }
	   closedir($handle);
	}
}

//////////////////////////////////////////////////////////////////////////
//fonction pour créer une miniature de la 1ère image du sous dossier photo
//////////////////////////////////////////////////////////////////////////
function create_icon($dir2iconize) {
	$dir = PHOTOS_DIR."/".$dir2iconize; //chemin vers le répertoire à dont on doit créer l'icone
	if ($handle = opendir($dir)) {
		$cFile = 0;
		while (false !== ($file = readdir($handle))) {
			if($file != "." && $file != ".."){
				if(is_file($dir . "/" . $file)){
					$listFile[$cFile] = $file;
					$cFile++;
				}
			}
		}
	closedir($handle);
	}
	//$extract = scandir($dir);//scan des "array" du répertoire
	$first_dir_item = $listFile[0]; // on extrait la valeur du premier fichier du répertoire (après"." et "..")
	list($width, $height, $type, $attr) = getimagesize($dir."/".$first_dir_item);//on liste les valeur de l'image
    $miniature = imagecreatetruecolor(ICO_WIDTH, ICO_HEIGHT);
	if ($type == 1) {
		$image = imagecreatefromgif($dir."/".$first_dir_item);
	}
	if ($type == 2) {
		$image = imagecreatefromjpeg($dir."/".$first_dir_item);
	}
	if ($type == 3) {
		$image = imagecreatefrompng($dir."/".$first_dir_item);
	}
	//imagecopyresampled(image de destination, image source, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h);
	imagecopyresampled($miniature, $image, 0, 0,((($width - ICO_WIDTH)/2) <= ICO_WIDTH ? ICO_WIDTH-(($width - ICO_WIDTH)/2) : ($width - ICO_WIDTH)/2), ((($height - ICO_HEIGHT)/2) <= 0 ? ICO_HEIGHT-(($height - ICO_HEIGHT)/2) : ($height - ICO_HEIGHT)/2), ICO_WIDTH, ICO_HEIGHT, ICO_WIDTH*2, ICO_HEIGHT*2);
	imagejpeg($miniature, $dir."/".ICO_FILENAME, GLOBAL_JPG_QUALITY);
}

//////////////////////////////////////////////
//fonction pour créer le répertoire miniatures
//////////////////////////////////////////////
function create_folder($dirwhere2folderize, $dir_name) {
	mkdir(PHOTOS_DIR."/".$dirwhere2folderize."/".$dir_name);
}

/////////////////////////////////////////////////////////////////////
//fonction pour créer toutes les miniatures du répertoire en question
/////////////////////////////////////////////////////////////////////
function create_newimage($dirname, $file2miniaturize, $dimensionmax, $dir_where2save, $file_prefixe) {
	$dir = PHOTOS_DIR."/".$dirname; //chemin vers le répertoire à dont on doit créer l'icone
	$dir_where2save = ($dir_where2save ? "/".$dir_where2save : "");
	$file_prefixe = ($file_prefixe ? $file_prefixe : "");
	list($width, $height, $type, $attr) = getimagesize($dir."/".$file2miniaturize);//on liste les valeur de l'image
	if ($width >= $height) {
	$newwidth = $dimensionmax;
	$newheight = ($dimensionmax*$height)/$width;
	} else {
	$newwidth = ($dimensionmax*$width)/$height;
	$newheight = $dimensionmax;
	}
    $miniature = imagecreatetruecolor($newwidth, $newheight);
	if ($type == 1) {
		$image = imagecreatefromgif($dir."/".$file2miniaturize);
	}
	if ($type == 2) {
		$image = imagecreatefromjpeg($dir."/".$file2miniaturize);
	}
	if ($type == 3) {
		$image = imagecreatefrompng($dir."/".$file2miniaturize);
	}
	imagecopyresampled($miniature, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagejpeg($miniature, $dir.$dir_where2save."/".$file_prefixe.$file2miniaturize, GLOBAL_JPG_QUALITY); 
}

/////////////////////////////////////////
//fonction pour tronquer un nom trop long
/////////////////////////////////////////
function wordTruncate($str) {
  $str_to_count = html_entity_decode($str);
  echo strlen($str_to_count);
  if (strlen($str_to_count) <= PHOTONAME_MAXCHAR) {
   return $str;
  } else { 
  $str2 = substr($str_to_count, 0, PHOTONAME_MAXCHAR - 3)."...";
  return htmlentities($str2);
  }
}
?>
<html>
<head>
  <title>PHP Photo module 0.2.3</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="global_style.css" rel="stylesheet" type="text/css">
<SCRIPT LANGUAGE=Javascript>
<!--
function inCell(cell, newcolor) {
	if (!cell.contains(event.fromElement)) {
		cell.bgColor = newcolor;
	}
}

function outCell(cell, newcolor) {
	if (!cell.contains(event.toElement)) {
		cell.bgColor = newcolor;
	}
}
//-->
</SCRIPT>
</head>
<body>
<?php 
$show_heading = (isset($_GET['show_heading']) ? $_GET['show_heading'] : "");
ini_set('max_execution_time', 120); //2 mn max
switch ($show_heading) {
///////////////////////////////////////////////////////////////
//listing des répertoires photos sur la page d'index par défaut
///////////////////////////////////////////////////////////////
default:
scan_invalid_char(PHOTOS_DIR); //scan des répertoires qui contiennent des caractères interdits
// listage des répertoires et fichiers
if ($handle = opendir(PHOTOS_DIR)) {
   $cDir = 0;
   $cFile = 0;
   while (false !== ($file = readdir($handle))) {
		if($file != "." && $file != ".." &&  $file != THUMBS_DIR && $file != IMAGE_STDDIM){
			if(is_dir(PHOTOS_DIR . "/" . $file)){
				$listDir[$cDir] = $file;
				$cDir++;
			}
			else{
				$listFile[$cFile] = $file;
				$cFile++;
			} 
		}
   }
   if (ALPHABETIC_ORDER == true) {
		usort($listDir,"strnatcmp");
	}
   closedir($handle);
}
//
$total_icons = count($listDir);
$totalPages = ceil($total_icons/ICO_PER_PAGE);
$page_num = (isset($_GET['page_num']) && $_GET['page_num'] !== "" && $_GET['page_num'] <= $totalPages ? $_GET['page_num'] : "1");
?>
<div class="fdgris"><span class="Style1">////// <?php echo HOME_NAME ?></span></div>
<div class="fdcolor1" align="center">
<span class="Style2"><?php if ($page_num > 1) { ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo ($page_num-1); ?>" class="Style2">&laquo;</a> &nbsp;|&nbsp; <?php } 
  
for ($l =1; $l < $totalPages; $l++) {
	if ($page_num != $l) {
		?> <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a> &nbsp;|&nbsp; <?php 
	} else {
	?> <b><?php echo $l; ?></b> &nbsp;|&nbsp; <?php 
	}
}
if ($page_num != $l) {
	?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a><?php
} else {
	?><b><?php echo $l; ?></b><?php
}
if ($page_num < $totalPages) { ?> &nbsp;|&nbsp; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo ($page_num+1) ?>" class="Style2">&raquo;</a><?php } ?>
</span></div>
<br>
<table border="0" align="center" cellpadding="8" cellspacing="0">
	<tr>
<?php 
$separateurs = array('_', '-', '.');
$k=0; 
   for ($i = (ICO_PER_PAGE*$page_num) - ICO_PER_PAGE; $i < ($total_icons > (ICO_PER_PAGE*($page_num)) ? ICO_PER_PAGE*$page_num : $total_icons); $i++) {
	if ($listDir[$i] != "." && $listDir[$i] != ".." && $listDir[$i] != THUMBS_DIR && $listDir[$i] != IMAGE_STDDIM && $listDir[$i] != IMAGE_400 && $listDir[$i] != IMAGE_800 && is_dir(PHOTOS_DIR . "/" . $listDir[$i]) == true) {
	//création du répertoire miniatures et images
	if (!file_exists(PHOTOS_DIR . "/" . $listDir[$i] . "/" . THUMBS_DIR)) { 
		create_folder($listDir[$i], THUMBS_DIR);
	}
	if (!file_exists(PHOTOS_DIR . "/" . $listDir[$i] . "/" . IMAGE_STDDIM)) { 
		create_folder($listDir[$i], IMAGE_STDDIM);
	}
		//création de la miniature
		if (!file_exists(PHOTOS_DIR . "/" . $listDir[$i] . "/" . ICO_FILENAME)) { //si la miniature existe
			create_icon($listDir[$i]);
		}
		list($width, $height, $type, $attr) = getimagesize(PHOTOS_DIR . "/" . $listDir[$i]  . "/" . ICO_FILENAME);//on liste les valeurs de la miniature
		if ($width != ICO_WIDTH || $height != ICO_HEIGHT) { //on affiche
			create_icon($listDir[$i]);
		}
		?>
        <?php (is_int($k/ICO_PER_LINE) ? print "<tr>": print "");  ?>  
	<td>
		<table border="0" cellpadding="1" cellspacing="1" bgcolor="#666666">
		  <tr class="tddeco">
		    <td width="<?php echo ICO_WIDTH + 18; ?>" height="<?php echo ICO_HEIGHT + 18; ?>" align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">
				<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $listDir[$i]; ?>"><img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($listDir[$i]) . "/" . ICO_FILENAME ?>" alt="<?php echo str_replace($separateurs, ' ', $listDir[$i]); ?>" width="<?php echo ICO_WIDTH ?>" height="<?php echo ICO_HEIGHT ?>" border="0" class="imageborder"></a></td>
		  </tr>
		  <tr>
		    <td align="center"><span class="Style2"><?php 
			$titre_album = str_replace($separateurs, ' ', $listDir[$i]);
			$nbmots = explode(" ", $titre_album);
			$maxword2show = ((count($nbmots) < 6) ? count($nbmots) : 6);
			$wordnb = 0;
			while ($wordnb <$maxword2show) {
			echo  $nbmots[$wordnb] . " "; 
			$wordnb++;
			}
			echo ((count($nbmots) > 6) ? " ..." : "");
 			?></span></td>
		  </tr>
	  </table>
	  </td>
<?php
		//
	}
	$k++;
   }
?>
</table><br>
<div class="fdcolor1" align="center">
  <span class="Style2"><?php if ($page_num > 1) { ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo ($page_num-1); ?>" class="Style2">&laquo;</a> &nbsp;|&nbsp; <?php } 
  
  for ($l =1; $l < ceil($total_icons/ICO_PER_PAGE); $l++) {
	  if ($page_num != $l) {
	   ?> <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a> &nbsp;|&nbsp; <?php 
	  } else {
	   ?> <b><?php echo $l; ?></b> &nbsp;|&nbsp; <?php 
	  }
  }
  if ($page_num != $l) {
  ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a><?php
  } else {
  ?><b><?php echo $l; ?></b><?php
  }
   if ($page_num < ( ceil(($total_icons)/ICO_PER_PAGE)) ) { ?> &nbsp;|&nbsp; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo ($page_num+1) ?>" class="Style2">&raquo;</a><?php } ?>
  </span></div>
<?php 
break;

//////////////////////////////////////////////////////////
//listing des miniatures dans un répertoire photo spécifié
//////////////////////////////////////////////////////////
case ('list'):
$photodir = (isset($_GET['dir']) ? $_GET['dir'] : "");
	if (!isset($_GET['dir']) || $_GET['dir'] == "") {//on vérifie que le répertoire photo existe bien ?>
		<table border="0" align="center" cellpadding="28" cellspacing="0">
		  <tr>
		    <td align="center"><span class="txtrouge">Vous devez spécifier un répertoire photo !</span>
		      <p>
			<form method="post"><INPUT TYPE="button" VALUE="Retour" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>
	<?php	
	break;
	}
//on supprime les slash, antislash et points possibles pour éviter les failles de sécurité
$photodir = preg_replace("/\\\\/", "", $photodir);
$str2clean = array("." => "", "/" => "");
$photodir = strtr($photodir, $str2clean);
$page_num = (isset($_GET['page_num']) ? $_GET['page_num'] : "1");//vérification que le numéro de page existe bien
$dir = PHOTOS_DIR . "/" . $photodir; //chemin vers le répertoire qui contient les miniatures
	if (!file_exists($dir)) {//on vérifie que le répertoire photo existe bien ?>
		<table border="0" align="center" cellpadding="28" cellspacing="0">
		  <tr>
		    <td align="center"><span class="txtrouge">Ce r&eacute;pertoire photo n'existe pas !</span>
		      <p>
			<form method="post"><INPUT TYPE="button" VALUE="Retour" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>
	<?php	
	break;
	}
	//création du répertoire miniatures et images
	if (!file_exists($dir . "/" . THUMBS_DIR)) { 
		create_folder($photodir, THUMBS_DIR);
	}
	if (!file_exists($dir . "/" . IMAGE_STDDIM)) { 
		create_folder($photodir, IMAGE_STDDIM);
	}
// listage des répertoires et fichiers
if ($handle = opendir($dir)) {
   $cDir = 0;
   $cFile = 0;
   while (false !== ($file = readdir($handle))) {
	if($file != "." && $file != ".."){
		if(is_dir($dir . "/" . $file)){
			$listDir[$cDir] = $file;
			$cDir++;
		}
		else{
			$listFile[$cFile] = $file;
			$cFile++;
		}
	}
   }
   closedir($handle);
}
if (ALPHABETIC_ORDER == true) {
	usort($listFile,"strnatcmp");
}

//selon l'ordonnancement, on détermine la bonne pagination de retour à l'index principal.
if (ALPHABETIC_ORDER == true) {
	if ($handle = opendir(PHOTOS_DIR)) {
		$cDir = 0;
		while (false !== ($subdir = readdir($handle))) {
			if($subdir != "." && $subdir != ".." && $subdir != THUMBS_DIR && $subdir != IMAGE_STDDIM){
				if(is_dir(PHOTOS_DIR . "/" . $subdir)){
					$listDir[$cDir] = $subdir;
					$cDir++;
				}
			}
   		}
   closedir($handle);
	} 
usort($listDir,"strnatcmp");
$photoDirNba = 1;
	for ($b=0; $b <	count($listDir); $b++) {
		$ordertest[$photoDirNba] = $listDir[$b];
				if($ordertest[$photoDirNba] == $photodir){
				$dir_index = $photoDirNba;
				} else {
				$photoDirNba++;
				}
	}
} else {
// récupération du numéro du dossier photo
	if ($handle = opendir(PHOTOS_DIR)) {
	   $photoDirNbb = 1;
	   while (false !== ($photoDirectory = readdir($handle))) {
		if($photoDirectory != "." && $photoDirectory != ".." && $photoDirectory != THUMBS_DIR && $photoDirectory != IMAGE_STDDIM){
			if(is_dir(PHOTOS_DIR . "/" . $photoDirectory)){
				if($photoDirectory == $photodir){
				$dir_index = $photoDirNbb;
				} else {
				$photoDirNbb++;
				}
			}
		}
   }
   closedir($handle);
}
}
$page_index = ceil($dir_index/ICO_PER_PAGE);
//
//on liste les miniatures
if ($handle = opendir($dir."/".THUMBS_DIR)) {
   $thumb = 0;
   while (false !== ($file = readdir($handle))) {
	if($file != "." && $file != ".."){
		if(is_file($dir."/".THUMBS_DIR . "/" . $file)){
			$extractthumbs[$thumb] = $file;
			$thumb++;
		}
	}
   }
   closedir($handle);
}
$valid = 0;
for ($i=0; $i <	count($listFile); $i++) {
	if ($listFile[$i] !== ICO_FILENAME) {
		$listvalidimg[$valid] = $listFile[$i];
		$valid++;
	}
}

$total_files = count($listvalidimg);// on compte le nombre d'éléments dans le dossier sans compter "." et ".."
$separateurs = array('_', '-', '.');
?>
<div class="fdgris"><span class="Style1">////// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $page_index; ?>" class="Style1"><?php echo HOME_NAME; ?></a> &raquo; <?php echo str_replace($separateurs, ' ', $photodir); ?>  / photos <?php echo (($page_num-1)*MINIATURES_PER_PAGE)+1; ?> à <?php if ($page_num < ( ceil(($total_files)/MINIATURES_PER_PAGE)) ) { echo (($page_num)*MINIATURES_PER_PAGE); } else { echo $total_files; } ?>  sur <?php echo $total_files; ?> </span></div>
<div class="fdcolor1" align="center">
  <span class="Style2"><?php if ($page_num > 1) { ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo ($page_num-1) ?>" class="Style2">&laquo;</a> &nbsp;|&nbsp; <?php } 
  $l =1;
  while ($l < (ceil(($total_files)/MINIATURES_PER_PAGE)) ) {
  if ($page_num != $l) {
   ?> <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a> &nbsp;|&nbsp; <?php 
  } else {
   ?> <b><?php echo $l; ?></b> &nbsp;|&nbsp; <?php 
  }
   $l++;
  }
  if ($page_num != $l) {
  ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a><?php
  } else {
  ?><b><?php echo $l; ?></b><?php
  }
   if ($page_num < ( ceil(($total_files)/MINIATURES_PER_PAGE)) ) { ?> &nbsp;|&nbsp; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo ($page_num+1) ?>" class="Style2">&raquo;</a><?php } ?>
  </span></div>
<br>
<table border="0" align="center" cellpadding="8" cellspacing="0">
	<tr>
<?php 
//si les références correspondent :
$total_thumbFloor = MINIATURES_PER_PAGE*$page_num;
$k=0; 
for ($i = $total_thumbFloor - MINIATURES_PER_PAGE; $i < ( ($total_files > $total_thumbFloor) ? $total_thumbFloor : $total_files); $i++) {//oncompte le nb d'éléments à afficher selon le numéro de page
		$fileexist = "";
		$j = 0;
			while ($j < ($total_files)) {
				if ("__".$listvalidimg[$i] == (isset($extractthumbs[$j]) ? $extractthumbs[$j] : "")) {
					$fileexist = $extractthumbs[$j];
				}
			$j++;
		   }
		$pos = strrpos($listvalidimg[$i], '.'); //calcule la position du point dans la chaine $document, ex. : 8
		$ext = strtolower(substr($listvalidimg[$i], $pos + 1));
			if (($ext == "jpeg" || $ext == "jpg" || $ext == "gif" || $ext == "png") && $listvalidimg[$i] !== ICO_FILENAME && ("__".$listvalidimg[$i] !== $fileexist)) { //si $document contient les extensions d'image et qu'il n'est pas icone/image du répertoire
			   create_newimage($photodir, $listvalidimg[$i], MINIATURE_MAXDIM, THUMBS_DIR, "__");
		   }
		?>
        <?php (is_int($k/MINIATURES_PER_LINE) ? print "<tr>": print "");  ?>  
	<td>
		<table border="0" cellpadding="1" cellspacing="1" bgcolor="#666666">
		  <tr class="tddeco">
		    <td width="<?php echo MINIATURE_MAXDIM + 18; ?>" height="<?php echo MINIATURE_MAXDIM + 18; ?>" align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=detail&dir=<?php echo rawurlencode($photodir); ?>&photo=<?php echo $i+1; ?>"><img src="<?php echo PHOTOS_DIR."/" . rawurlencode($photodir) . "/" . THUMBS_DIR . "/__".$listvalidimg[$i] ?>" border="0" alt="<?php echo $listvalidimg[$i]; ?>" class="imageborder"></a></td>
		  </tr>
		  <tr>
		    <td align="center"><span class="Style2"><?php echo $i+1 ."| " . wordTruncate($listvalidimg[$i]); ?></span></td>
		  </tr>
	  </table>
	  </td>
		<?php
		$k++;
}
?>
</table><br>
<div class="fdcolor1" align="center">
  <span class="Style2"><?php if ($page_num > 1) { ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo ($page_num-1) ?>" class="Style2">&laquo;</a> &nbsp;|&nbsp; <?php } 
  $l =1;
  while ($l < (ceil(($total_files)/MINIATURES_PER_PAGE)) ) {
  if ($page_num != $l) {
   ?> <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a> &nbsp;|&nbsp; <?php 
  } else {
   ?> <b><?php echo $l; ?></b> &nbsp;|&nbsp; <?php 
  }
   $l++;
  }
  if ($page_num != $l) {
  ?><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo $l; ?>" class="Style2"><?php echo $l; ?></a><?php
  } else {
  ?><b><?php echo $l; ?></b><?php
  }
   if ($page_num < ( ceil(($total_files)/MINIATURES_PER_PAGE)) ) { ?> &nbsp;|&nbsp; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir; ?>&page_num=<?php echo ($page_num+1) ?>" class="Style2">&raquo;</a><?php } ?>
  </span></div>
<?php 
 break;

////////////////////
//détail de la photo
////////////////////
case ('detail'):
$photodir = (isset($_GET['dir']) ? $_GET['dir'] : "");
	if (!isset($_GET['dir']) || $_GET['dir'] == "") {//on vérifie que le répertoire photo existe bien ?>
		<table border="0" align="center" cellpadding="28" cellspacing="0">
		  <tr>
		    <td align="center"><span class="txtrouge">Vous devez spécifier un répertoire photo !</span>
		      <p>
			<form method="post"><INPUT TYPE="button" VALUE="Retour" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>
	<?php	
	break;
	}
//on supprime les slash, antislash et points possibles pour éviter les failles de sécurité
$photodir = preg_replace("/\\\\/", "", $photodir);
$str2clean = array("." => "", "/" => "");
$photodir = strtr($photodir, $str2clean);
$dir = PHOTOS_DIR . "/" . $photodir; //chemin vers le répertoire qui contient les miniatures
	if (!file_exists($dir)) {//on vérifie que le répertoire photo existe bien ?>
		<table border="0" align="center" cellpadding="28" cellspacing="0">
		  <tr>
		    <td align="center"><span class="txtrouge">Ce r&eacute;pertoire photo n'existe pas !</span>
		      <p>
			<form method="post"><INPUT TYPE="button" VALUE="Retour" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>
	<?php	
	break;
	}
$photo = (isset($_GET['photo']) ? $_GET['photo'] : "");
$dim = (isset($_GET['dim']) ? $_GET['dim'] : IMAGE_STDDIM);
$dir = PHOTOS_DIR . "/" . $photodir;
if ($handle = opendir($dir)) {
   $cFile = 1;
   while (false !== ($file = readdir($handle))) {
	if($file != "." && $file != ".."){
		if(is_file($dir . "/" . $file) && $file != ICO_FILENAME){
			$listFile[$cFile] = $file;
			$cFile++;
		}
	}
   }
   closedir($handle);
}
// Florent. Je retrie par ordre alphabétique mais le tableau trié $listFile2 commence à l'index 0.
// Je décale l'index pour que le tableau $listFile commence à 1, comme la variable $photo.
if (ALPHABETIC_ORDER == true)
{
	$listFile2 = $listFile;
	usort($listFile2,"strnatcmp");
	for ($i=0;$i < count($listFile2); $i++) {
		$listFile[$i+1] = $listFile2[$i];
	}
}
//
	if (!isset($_GET['photo']) || $_GET['photo'] == "" || !isset($listFile[$photo])) {//on vérifie que la photo existe bien ?>
		<table border="0" align="center" cellpadding="28" cellspacing="0">
		  <tr>
		    <td align="center"><span class="txtrouge">Il n'y a aucune photo à afficher !</span>
		      <p>
			<form method="post"><INPUT TYPE="button" VALUE="Retour" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>
	<?php	
	break;
	}
	//
if (!file_exists($dir . "/" . $dim . "/" . $listFile[$photo])) {
	create_newimage($photodir, $listFile[$photo], $dim, $dim, "");
} 
$total_images = count($listFile);// on compte le nombre d'éléments dans le dossier sans compter "." et ".."
list($width, $height, $type, $attr) = getimagesize($dir . "/" . $dim . "/" . $listFile[$photo]);
//on créé les miniatures si elles sont absentes
if ($photo > 1 && !file_exists(PHOTOS_DIR . "/" . $photodir . "/" . THUMBS_DIR . "/__" . $listFile[$photo-1])) {
	create_newimage($photodir, $listFile[$photo-1], MINIATURE_MAXDIM, THUMBS_DIR, "__");
}
if ($photo < $total_images && !file_exists(PHOTOS_DIR . "/" . $photodir . "/" . THUMBS_DIR . "/__" . $listFile[$photo+1])) {
	create_newimage($photodir, $listFile[$photo+1], MINIATURE_MAXDIM, THUMBS_DIR, "__");
}
$separateurs = array('_', '-', '.');
?>
<div class="fdgris"><span class="Style1">////// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir ?>&page_num=<?php echo ceil($photo/MINIATURES_PER_PAGE); ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $photodir); ?></a> &raquo; photo : <?php echo $listFile[$photo]; ?> / n&deg;<?php echo $photo; ?> sur <?php echo $total_images; ?></span></div>
<br>
<table border="0" align="center" cellpadding="8" cellspacing="0">
  <tr>
    <td width="<?php echo MINIATURE_MAXDIM + 26; ?>" height="<?php echo MINIATURE_MAXDIM + 26; ?>">
	<?php if ($photo > 1) {?>
		<table border="0" cellpadding="1" cellspacing="1" bgcolor="#666666">
			<tr class="tddeco">
		  		<td width="<?php echo MINIATURE_MAXDIM + 18; ?>" height="<?php echo MINIATURE_MAXDIM + 18; ?>" align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">
				<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=detail&dir=<?php echo $photodir; ?>&photo=<?php echo $photo-1; echo ($dim == IMAGE_STDDIM ? "" : "&dim=". $dim);?>"><img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . THUMBS_DIR . "/__" . $listFile[$photo-1]; ?>" alt="<?php echo $listFile[$photo-1]; ?>" border="0" class="imageborder"></a>
				</td>
			</tr>
	  	</table>
	<?php }?>
	</td><td>
		<table border="0" cellpadding="1" cellspacing="1" bgcolor="#666666">
		  <tr class="tddeco">
		    <td width="<?php echo $dim + 18; ?>" height="<?php echo $dim + 18; ?>" align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">
			<?php if ($photo != "") { ?>
        <a href="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $listFile[$photo]; ?>">
				<img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $dim . "/" . $listFile[$photo]; ?>" alt="<?php echo $listFile[$photo]; ?>" <?php echo $attr; ?> border="0" class="imageborder">
			<?php if ($photo < $total_images) { ?></a><?php } 
			} else { echo "<span class=\"txtrouge\">Il n'y a aucune photo à afficher</span>"; } ?>
			</td>
		  </tr>
		  <tr>
		    <td align="center"><span class="Style2"> 
			<?php
		if (exif_imagetype($dir.'/'.$listFile[$photo]) != IMAGETYPE_PNG && exif_imagetype($dir.'/'.$listFile[$photo]) != IMAGETYPE_GIF) {
				 ?><hr size="1" noshade><?php
	 			$exif = read_exif_data($dir.'/'.$listFile[$photo], 0, true);
				echo $exif["FILE"]["FileName"] . " || " . round(($exif["FILE"]["FileSize"]/1024), 0) . " Ko || Résolution originale : ".$exif["COMPUTED"]["Width"]." x ".$exif["COMPUTED"]["Height"]."<br>\n";
		   if (isset($exif["EXIF"]["DateTimeOriginal"]))  echo "Date et Heure : ".$exif["EXIF"]["DateTimeOriginal"]."<br>";
		   if (isset($exif["EXIF"]["ExposureTime"])) echo "Temps d'exposition : ".$exif["EXIF"]["ExposureTime"]." || ";
		   if (isset($exif["EXIF"]["ISOSpeedRatings"])) echo "ISO : ".$exif["EXIF"]["ISOSpeedRatings"]."<br>";
		   if (isset($exif["COMPUTED"]["ApertureFNumber"])) echo "Ouverture de la focale : ".$exif["COMPUTED"]["ApertureFNumber"]." || ";
		   if (isset($exif["EXIF"]["FocalLength"])) echo "Longueur de la focale : ".$exif["EXIF"]["FocalLength"]."\n";
		}
			 ?>
			</span>
			</td>
		  </tr>
	  </table>
	</td>
    <td width="<?php echo MINIATURE_MAXDIM + 26; ?>" height="<?php echo MINIATURE_MAXDIM + 26; ?>">
	<?php if ($photo < $total_images) {?>
		<table border="0" cellpadding="1" cellspacing="1" bgcolor="#666666">
			<tr class="tddeco">
		  		<td width="<?php echo MINIATURE_MAXDIM + 18; ?>" height="<?php echo MINIATURE_MAXDIM + 18; ?>" align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">

				<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=detail&dir=<?php echo $photodir; ?>&photo=<?php echo $photo+1; echo ($dim == IMAGE_STDDIM ? "" : "&dim=". $dim);?>"><img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . THUMBS_DIR . "/__" . $listFile[$photo+1]; ?>" alt="<?php echo $listFile[$photo+1]; ?>" border="0" class="imageborder"></a>
				</td>
			</tr>
	  	</table>
	<?php }?>
	</td>
  </tr>
</table>

<?php
break;
//fin du switch
}
?>
<div class="fdgris" align="right">
  <span class="Style2">Php Photo Module 0.2.3 | auteur : <a href="http://www.jensen-siu.net" target="_blank" class="Style2" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" class="Style2" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a></span>
</div>
<noscript>
<!-- Si vous retirez la référence ci dessus pour des raisons esthétiques, je vous remercie de laisser celle-ci que personne ne verra. Merci. -->
Php Photo Module 0.2.3 | auteur : <a href="http://www.jensen-siu.net" target="_blank" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a>
</noscript>
</body>
</html>
