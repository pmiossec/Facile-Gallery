<?php 
require("conf.php");

//error_reporting(E_ALL); // afficher les erreurs
error_reporting(0); // ne pas afficher les erreurs

$separateurs = array('_', '-', '.');

function write_kml_file($kml_placemarks, $kml_path)
{
	//echo $kml_path;
	$kml_content = '<?xml version= "1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>'
	               . $kml_placemarks . '</Document></kml>';
	//echo $kml_content;
	//Ecrire le fichier
	$fh = fopen($kml_path, 'w') or die("can't open file");
	fwrite($fh, $kml_content);
	fclose($fh);
}

function add_map($url_kml_file)
{
	echo '<div id="map_canvas" style="width:800px; height:600px"></div>
	<script type="text/javascript">
	//DOC : http://code.google.com/intl/fr/apis/maps/documentation/javascript/v2/services.html#XML_Overlays
	//http://www.touraineverte.com/aide-documentation-exemple-tutoriel-didacticiel/api-google-maps/kml-kmz/creer-creation-carte-map-mes-cartes/utiliser-fichier-kml-generer-creer-google-earth/importer-carte-via-api-google-maps-new-GGeoXml.htm
	function initialize() {
		var myLatlng = new google.maps.LatLng(41.875696,-87.624207);
		var myOptions = { zoom: 11, center: myLatlng, mapTypeId: google.maps.MapTypeId.HYBRID }
		var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		var ctaLayer = new google.maps.KmlLayer("' . $url_kml_file . '");
		ctaLayer.setMap(map);
	}
	</script>';
}
function verify_directories()
{
	$photodir = (isset($_GET['dir']) ? $_GET['dir'] : "");
	if (!isset($_GET['dir']) || $_GET['dir'] == "") {//on v�rifie que le r�pertoire photo existe bien
		echo '<table border="0" align="center" cellpadding="28" cellspacing="0">
			<tr>
				<td align="center"><span class="txtrouge">' . PHOTO_DIR_NEEDED . '</span>
				<p>
			<form method="post"><INPUT TYPE="button" VALUE="' . BACK . '" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>';
	return array (false, '', '');
	}
	//on supprime les slash, antislash et points possibles pour �viter les failles de s�curit�
	$photodir = preg_replace("/\\\\/", "", $photodir);
	$str2clean = array("." => "", "/" => "");
	$photodir = strtr($photodir, $str2clean);
	$dir = PHOTOS_DIR . "/" . $photodir; //chemin vers le r�pertoire qui contient les miniatures
	if (!file_exists($dir)) {//on v�rifie que le r�pertoire photo existe bien
		echo '<table border="0" align="center" cellpadding="28" cellspacing="0">
			<tr>
				<td align="center"><span class="txtrouge">' . PHOTO_DIR_NOT_EXISTING . '</span>
				<p>
			<form method="post"><INPUT TYPE="button" VALUE="' . BACK . '" onClick="history.go(-1)"></form>
			</td>
		</tr>
	</table>';
	return array (false, '', '');
	}
	return array (true, $photodir, $dir);
}


///////////////////////////////////////////////////////////////////////
//fonction qui convertit les donn�es GPS de degr�s, minutes, secondes en decimal
///////////////////////////////////////////////////////////////////////
function extract_gps_datas($exif_deg, $exif_min, $exif_sec, $exif_hem)
{
	$deg=divide_gps_coordinates($exif_deg);
	$min=divide_gps_coordinates($exif_min);
	$sec=divide_gps_coordinates($exif_sec);
	//H�misph�re (N, S, W ou E):
	$hem=$exif_hem;

	///Altitude:
	//$alt=$exif["GPS"]["GPSAltitude"][0];

	if ($hem === "N" || $hem === "E")
	{ $gps_ref2 = 1; }
	else
	{ $gps_ref2 = -1; }
	return $gps_ref2 *($deg + $min / 60 + $sec/3600) ;
}
///////////////////////////////////////////////////////////////////////
//fonction qui extrait et met en forme une donn�e exif
///////////////////////////////////////////////////////////////////////
function extract_exif_data($exifs, $field1, $field2, $label){
	if (isset($exifs[$field1][$field2]))
		return $label . $exifs[$field1][$field2];
	else return "";
}
///////////////////////////////////////////////////////////////////////
//fonction qui extrait et met en forme une donn�e IPTC
///////////////////////////////////////////////////////////////////////
function extract_iptc_data($iptcs, $iptc_entry_code, $label){
	if(!array_key_exists($iptc_entry_code, $iptcs) || count($iptcs[$iptc_entry_code])==0)
		return "";

	$display_string = "";

	for ($i=0;$i < count($iptcs[$iptc_entry_code]); $i++) {
		if($i != 0)
		{
			$display_string = $display_string . ", ";
		}
		$display_string = $display_string . $iptcs[$iptc_entry_code][$i];
	}
	return $label . $display_string;
}

///////////////////////////////////////////////////////////////////////
//fonction qui retourne un nombre correspondant � une donn�e GPS
///////////////////////////////////////////////////////////////////////
function divide_gps_coordinates($a)
  {
  // evaluate the string fraction and return a float //	
    $e = explode('/', $a);
  // prevent division by zero //
    if (!$e[0] || !$e[1]) {
      return 0;
    }	else{
    return $e[0] / $e[1];
    }
  }

///////////////////////////////////////////////////////////////////////
//fonction qui renomme les dossiers comprenant des caract�res interdits
///////////////////////////////////////////////////////////////////////
function scan_invalid_char($dir2scan) {
	if ($handle = opendir($dir2scan)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && eregi("[]\[\����������������������������������������������.!@#$%^&*+{}()'=$]", $file) && is_dir($dir2scan.'/'.$file)) {
					$newfilename = $file;
					$newfilename = eregi_replace("[]\[\.!@#$%^&*+{}()'=$]", "_", $newfilename);
					$newfilename = eregi_replace("[������������]", "a", $newfilename);
					$newfilename = eregi_replace("[��������]", "e", $newfilename);
					$newfilename = eregi_replace("[��]", "c", $newfilename);
					$newfilename = eregi_replace("[��������]", "i", $newfilename);
					$newfilename = eregi_replace("[����������]", "o", $newfilename);
					$newfilename = eregi_replace("[��������]", "u", $newfilename);
					rename($dir2scan.'/'.$file, $dir2scan.'/'.$newfilename);
			}
		}
		closedir($handle);
	}
}

//////////////////////////////////////////////////////////////////////////
//fonction pour cr�er une miniature de la 1�re image du sous dossier photo
//////////////////////////////////////////////////////////////////////////
function create_icon($dir2iconize) {
	$dir = PHOTOS_DIR."/".$dir2iconize; //chemin vers le r�pertoire dont on doit cr�er l'icone
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

	if (ALPHABETIC_ORDER == true) {
		usort($listFile,"strnatcmp");
	}

	//$extract = scandir($dir);//scan des "array" du r�pertoire
	$first_dir_item = $listFile[0]; // on extrait la valeur du premier fichier du r�pertoire (apr�s "." et "..")
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
//////////////////////////////////////////////////////////////////////////
//fonction pour trouver une image ayant des donn�es GPS
//////////////////////////////////////////////////////////////////////////
function find_file_with_gps_data($dir2findgps) {
	$dir = PHOTOS_DIR."/".$dir2findgps; //chemin vers le r�pertoire dont on doit cr�er l'icone
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
	for($i=0;$i<$cFile;$i++)
	{
		$exif = read_exif_data($dir.'/'.$listFile[$i], 0, true);
		if(isset($exif["GPS"]["GPSLatitude"][0])
			&& isset($exif["GPS"]["GPSLongitude"][0]))
		{
			$size = getimagesize($dir.'/'.$listFile[$i], $info);
			//if (isset($info["APP13"])) {
			//	$html_code = "<a href=\"./" . PHOTOS_DIR . "/". $photodir. "/" . $file_to_add ."\"><img src=\"http://". $_SERVER['SERVER_NAME'] . "/photos/". PHOTOS_DIR ."/". $photodir. "/". THUMBS_DIR . "/__" . $file_to_add ."\"></a><br/>";
			//}
			$decimal_lat =  extract_gps_datas($exif["GPS"]["GPSLatitude"][0] , $exif["GPS"]["GPSLatitude"][1] , $exif["GPS"]["GPSLatitude"][2], $exif["GPS"]["GPSLatitudeRef"]);
			$decimal_long =  extract_gps_datas($exif["GPS"]["GPSLongitude"][0] , $exif["GPS"]["GPSLongitude"][1] , $exif["GPS"]["GPSLongitude"][2], $exif["GPS"]["GPSLongitudeRef"]);
			$kml_file = $kml_file . "<Placemark><name>" . $dir2findgps . "</name><description><![CDATA[";
			$kml_file = $kml_file . $html_code;
			$kml_file = $kml_file . "]]></description><Point><coordinates>" . $decimal_long ."," . $decimal_lat . "</coordinates></Point></Placemark>";
			return array(true, $kml_file);
		}
	}
	return array(false, "");
}

//////////////////////////////////////////////
//fonction pour cr�er le r�pertoire miniatures
//////////////////////////////////////////////
function create_folder($dirwhere2folderize, $dir_name) {
	mkdir(PHOTOS_DIR."/".$dirwhere2folderize."/".$dir_name);
}

/////////////////////////////////////////////////////////////////////
//fonction pour cr�er toutes les miniatures du r�pertoire en question
/////////////////////////////////////////////////////////////////////
function create_newimage($dirname, $file2miniaturize, $dimensionmax, $dir_where2save, $file_prefixe) {
	$dir = PHOTOS_DIR."/".$dirname; //chemin vers le r�pertoire � dont on doit cr�er l'icone
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
	//echo strlen($str_to_count);
	if (strlen($str_to_count) <= PHOTONAME_MAXCHAR+6) {
		return $str;
	} else {
		$str2 = substr($str_to_count, 0, PHOTONAME_MAXCHAR +3)."...";
		return htmlentities($str2);
	}
}
?>
<?php
$show_heading = (isset($_GET['show_heading']) ? $_GET['show_heading'] : "");
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo (isset($_GET['dir']) ? $_GET['dir'] : HOME_NAME);?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<link href="global_style.css" rel="stylesheet" type="text/css">
    <script src="http://maps.google.com/maps/api/js?sensor=false"
            type="text/javascript"></script>
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
<style type="text/css">
  html { height: 100% }
  body { height: 100%; margin: 0px; padding: 0px }
  #map_canvas { height: 100% ; margin-left: auto; margin-right: auto; }
</style>
<?php
if($show_heading =="list"){?>
	<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
	<script src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<?php
}?>
</head>
<?php
if($show_heading =="map" || $show_heading =="gallery_map"){
	echo '<body onload="initialize()">';
}
else
{
	echo "<body>";
}
ini_set('max_execution_time', 120); //2 mn max
$directory = $_SERVER["SCRIPT_NAME"];
$directory = substr($directory, 0, strrpos($directory,"/")+1);
$url_path_datas = "http://" . $_SERVER["SERVER_NAME"]. $directory . PHOTOS_DIR ."/";
switch ($show_heading) {
///////////////////////////////////////////////////////////////
//listing des r�pertoires photos sur la page d'index par d�faut
///////////////////////////////////////////////////////////////
default:
	scan_invalid_char(PHOTOS_DIR); //scan des r�pertoires qui contiennent des caract�res interdits
	// listage des r�pertoires et fichiers
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
	<div class="fdgris"><span class="Style1"><?php echo HOME_NAME ?></span>
	<span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=gallery_map" class="Style2"><?php echo DISPLAY_MAP ?></a></span></div>
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
	$k=0;
	for ($i = (ICO_PER_PAGE*$page_num) - ICO_PER_PAGE; $i < ($total_icons > (ICO_PER_PAGE*($page_num)) ? ICO_PER_PAGE*$page_num : $total_icons); $i++) {
		if ($listDir[$i] != "." && $listDir[$i] != ".." && $listDir[$i] != THUMBS_DIR && $listDir[$i] != IMAGE_STDDIM && $listDir[$i] != IMAGE_400 && $listDir[$i] != IMAGE_800 && is_dir(PHOTOS_DIR . "/" . $listDir[$i]) == true) {
			//cr�ation du r�pertoire miniatures et images
			if (!file_exists(PHOTOS_DIR . "/" . $listDir[$i] . "/" . THUMBS_DIR)) {
				create_folder($listDir[$i], THUMBS_DIR);
			}
			if (!file_exists(PHOTOS_DIR . "/" . $listDir[$i] . "/" . IMAGE_STDDIM)) {
				create_folder($listDir[$i], IMAGE_STDDIM);
			}
			//cr�ation de la miniature
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
	break;//Fin : listing des r�pertoires photos sur la page d'index par d�faut


//////////////////////////////////////////////////////////
//listing des miniatures dans un r�pertoire photo sp�cifi�
//////////////////////////////////////////////////////////
case ('list'):
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
	$page_num = (isset($_GET['page_num']) ? $_GET['page_num'] : "1");//v�rification que le num�ro de page existe bien
		//cr�ation du r�pertoire miniatures et images
		if (!file_exists($dir . "/" . THUMBS_DIR)) {
			create_folder($photodir, THUMBS_DIR);
		}
		if (!file_exists($dir . "/" . IMAGE_STDDIM)) {
			create_folder($photodir, IMAGE_STDDIM);
		}
	// listage des r�pertoires et fichiers
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

	//selon l'ordonnancement, on d�termine la bonne pagination de retour � l'index principal.
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
		// r�cup�ration du num�ro du dossier photo
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
	$total_files = count($listvalidimg);// on compte le nombre d'�l�ments dans le dossier sans compter "." et ".."

	//donn�es du slideshow
	$images='images = [';
	$titles='titles = [' ;
	$descriptions='descriptions = [';
	for($i=0;$i<count($listvalidimg);$i++)
	{
		if($i!=0){
			$images.=',';
			$titles.=',';
			$descriptions.=',';
		}
			$images .= "'./$dir/$listvalidimg[$i]'";
			$titles .="'titre'";
			$descriptions.="'desc'";
	}
	$images.='];';
	$titles.='];';
	$descriptions.='];';

	echo '<script type="text/javascript" charset="utf-8">' , $images , $titles , $descriptions, 'function slideshow(){$.prettyPhoto.open(images,titles,descriptions);}</script>';
	?>
<script type="text/javascript" charset="utf-8">

	$(document).ready(function(){
		$("a[rel^='prettyPhoto']").prettyPhoto({
			animation_speed: 'fast', /* fast/slow/normal */
			slideshow: 10000, /* false OR interval time in ms */
			autoplay_slideshow: false, /* true/false */
			opacity: 0.80, /* Value between 0 and 1 */
			show_title: false, /* true/false */
			allow_resize: true, /* Resize the photos bigger than viewport. true/false */
			default_width: 500,
			default_height: 344,
			counter_separator_label: '/', /* The separator for the gallery counter 1 "of" 2 */
			theme: 'facebook', /* light_rounded / dark_rounded / light_square / dark_square / facebook */
			hideflash: false, /* Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto */
			wmode: 'opaque', /* Set the flash wmode attribute */
			autoplay: true, /* Automatically start videos: True/False */
			modal: false, /* If set to true, only the close button will close the window */
			overlay_gallery: true, /* If set to true, a gallery will overlay the fullscreen image on mouse over */
			keyboard_shortcuts: true, /* Set to false if you open forms inside prettyPhoto */
			changepicturecallback: function(){}, /* Called everytime an item is shown/changed */
			callback: function(){}, /* Called when prettyPhoto is closed */
			markup: '<div class="pp_pic_holder"> \
						<div class="ppt">&nbsp;</div> \
						<div class="pp_top"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
						<div class="pp_content_container"> \
							<div class="pp_left"> \
							<div class="pp_right"> \
								<div class="pp_content"> \
									<div class="pp_loaderIcon"></div> \
									<div class="pp_fade"> \
										<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
										<div class="pp_hoverContainer"> \
											<a class="pp_next" href="#">next</a> \
											<a class="pp_previous" href="#">previous</a> \
										</div> \
										<div id="pp_full_res"></div> \
										<div class="pp_details clearfix"> \
											<p class="pp_description"></p> \
											<a class="pp_close" href="#">Close</a> \
											<div class="pp_nav"> \
												<a href="#" class="pp_arrow_previous">Previous</a> \
												<p class="currentTextHolder">0/0</p> \
												<a href="#" class="pp_arrow_next">Next</a> \
											</div> \
										</div> \
									</div> \
								</div> \
							</div> \
							</div> \
						</div> \
						<div class="pp_bottom"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
					</div> \
					<div class="pp_overlay"></div>',
			gallery_markup: '<div class="pp_gallery"> \
								<a href="#" class="pp_arrow_previous">Previous</a> \
								<ul> \
									{gallery} \
								</ul> \
								<a href="#" class="pp_arrow_next">Next</a> \
							</div>',
			image_markup: '<img id="fullResImage" src="" />',
			flash_markup: '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>',
			quicktime_markup: '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>',
			iframe_markup: '<iframe src ="{path}" width="{width}" height="{height}" frameborder="no"></iframe>',
			inline_markup: '<div class="pp_inline clearfix">{content}</div>',
			custom_markup: ''
		});
	});
	//$.prettyPhoto.initialize();
</script>
	<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $page_index; ?>" class="Style1"><?php echo HOME_NAME; ?></a> &raquo; <?php echo str_replace($separateurs, ' ', $photodir); ?> ( <?php echo (($page_num-1)*MINIATURES_PER_PAGE)+1; ?> -> <?php if ($page_num < ( ceil(($total_files)/MINIATURES_PER_PAGE)) ) { echo (($page_num)*MINIATURES_PER_PAGE); } else { echo $total_files; } ?> / <?php echo $total_files; ?>)</span>
	<span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=map&dir=<?php echo $photodir; ?>" class="Style2"><?php echo DISPLAY_MAP ?></a></span><span class="Style2" style="float:right;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><span class="Style2" style="float:right;"><a href="#" onClick="slideshow();return false;" class="Style2"><?php echo SLIDESHOW ?></a></span></div>

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
	//si les r�f�rences correspondent :
	$total_thumbFloor = MINIATURES_PER_PAGE*$page_num;
	$k=0;
	for ($i = $total_thumbFloor - MINIATURES_PER_PAGE; $i < ( ($total_files > $total_thumbFloor) ? $total_thumbFloor : $total_files); $i++) {//oncompte le nb d'�l�ments � afficher selon le num�ro de page
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
		if (($ext == "jpeg" || $ext == "jpg" || $ext == "gif" || $ext == "png") && $listvalidimg[$i] !== ICO_FILENAME && ("__".$listvalidimg[$i] !== $fileexist)) { //si $document contient les extensions d'image et qu'il n'est pas icone/image du r�pertoire
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
					<td align="center"><span class="Style2"><?php echo wordTruncate(($i+1) ."|" . $listvalidimg[$i]); ?></span></td>
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
	break;//Fin : listing des miniatures dans un r�pertoire photo sp�cifi�


////////////////////
//d�tail de la photo
////////////////////
case ('detail'):
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
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
	// Florent. Je retrie par ordre alphab�tique mais le tableau tri� $listFile2 commence � l'index 0.
	// Je d�cale l'index pour que le tableau $listFile commence � 1, comme la variable $photo.
	if (ALPHABETIC_ORDER == true)
	{
		$listFile2 = $listFile;
		usort($listFile2,"strnatcmp");
		for ($i=0;$i < count($listFile2); $i++) {
			$listFile[$i+1] = $listFile2[$i];
		}
	}
	//
	if (!isset($_GET['photo']) || $_GET['photo'] == "" || !isset($listFile[$photo])) {//on v�rifie que la photo existe bien ?>
		<table border="0" align="center" cellpadding="28" cellspacing="0">
			<tr>
				<td align="center"><span class="txtrouge"><?php echo NO_PHOTO_TO_DISPLAY; ?></span>
					<p>
					<form method="post"><INPUT TYPE="button" VALUE="<?php echo BACK; ?>" onClick="history.go(-1)"></form>
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
	$total_images = count($listFile);// on compte le nombre d'�l�ments dans le dossier sans compter "." et ".."
	list($width, $height, $type, $attr) = getimagesize($dir . "/" . $dim . "/" . $listFile[$photo]);
	//on cr�� les miniatures si elles sont absentes
	if ($photo > 1 && !file_exists(PHOTOS_DIR . "/" . $photodir . "/" . THUMBS_DIR . "/__" . $listFile[$photo-1])) {
		create_newimage($photodir, $listFile[$photo-1], MINIATURE_MAXDIM, THUMBS_DIR, "__");
	}
	if ($photo < $total_images && !file_exists(PHOTOS_DIR . "/" . $photodir . "/" . THUMBS_DIR . "/__" . $listFile[$photo+1])) {
		create_newimage($photodir, $listFile[$photo+1], MINIATURE_MAXDIM, THUMBS_DIR, "__");
	}
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir ?>&page_num=<?php echo ceil($photo/MINIATURES_PER_PAGE); ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $photodir); ?></a> &raquo; <?php echo $listFile[$photo]; ?> n&deg;<?php echo $photo; ?> / <?php echo $total_images; ?></span></div>
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
	</td>
	<td>
		<table border="0" cellpadding="1" cellspacing="1" bgcolor="#666666">
			<tr class="tddeco">
				<td align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">
			<?php if ($photo != "") { ?>
				<a href="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $listFile[$photo]; ?>">
				<img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $dim . "/" . $listFile[$photo]; ?>" alt="<?php echo $listFile[$photo]; ?>" <?php echo $attr; ?> border="0" class="imageborder">
			<?php if ($photo < $total_images) { ?></a><?php } 
			} else { echo '<span class="txtrouge">'. NO_PHOTO_TO_DISPLAY .'</span>'; } ?>
				</td>
			</tr>
			<tr>
				<td align="center">
					<span class="Style2">
					<?php
					$size = getimagesize($dir.'/'.$listFile[$photo], $info);
					if (isset($info["APP13"])) {
						$iptc = iptcparse($info["APP13"]);
						echo '<span class="legend">';
						echo str_replace("\n","<br/>",extract_iptc_data($iptc, '2#120',""));
						echo '</span><br/>';
						for($i_iptc=0;$i_iptc<count($iptc_to_display);$i_iptc++)
						{
						   list($code,$label)= $iptc_to_display[$i_iptc];
							echo extract_iptc_data($iptc, $code, $label . ' : ')."<br/>\n";
						}
					}
					if (exif_imagetype($dir.'/'.$listFile[$photo]) != IMAGETYPE_PNG && exif_imagetype($dir.'/'.$listFile[$photo]) != IMAGETYPE_GIF) {
						?><hr size="1" noshade><?php
						$exif = read_exif_data($dir.'/'.$listFile[$photo], 0, true);
						echo $exif["FILE"]["FileName"] . " || " . round(($exif["FILE"]["FileSize"]/1024), 0) . " Ko || ".$exif["COMPUTED"]["Width"]." x ".$exif["COMPUTED"]["Height"]."px<br>\n";
						for($i_exif=0;$i_exif<count($exif_to_display);$i_exif++)
						{
							list($field1, $field2, $label)= $exif_to_display[$i_exif];
							$text = extract_exif_data($exif, $field1, $field2 , $label . ' : ');
							if(strlen($text) != 0)
								echo $text."<br/>\n";
						}
						if(isset($exif["GPS"]["GPSLatitude"][0])
							&& isset($exif["GPS"]["GPSLongitude"][0]))
						{
							$decimal_lat =  extract_gps_datas($exif["GPS"]["GPSLatitude"][0] , $exif["GPS"]["GPSLatitude"][1] , $exif["GPS"]["GPSLatitude"][2], $exif["GPS"]["GPSLatitudeRef"]);
							$decimal_long =  extract_gps_datas($exif["GPS"]["GPSLongitude"][0] , $exif["GPS"]["GPSLongitude"][1] , $exif["GPS"]["GPSLongitude"][2], $exif["GPS"]["GPSLongitudeRef"]);
							echo "<a target=\"_blank\" href=\"http://maps.google.com/maps?ll=". $decimal_lat."," . $decimal_long."&spn=0.01,0.01&q=". $decimal_lat."," . $decimal_long."&hl=fr\">GPS: ". $decimal_lat."," . $decimal_long."</a><br/>";
						}
						/*$keys = array_keys($exif["EXIF"]);
						for ($i=0;$i < count($keys); $i++) {
						  echo $keys[$i] . " :" . $exif["EXIF"][$keys[$i]] . "<br/>";
						}*/
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
case ('map'):
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $photodir); ?></a></span></div>
<?php
	$photo = (isset($_GET['photo']) ? $_GET['photo'] : "");
	$dim = (isset($_GET['dim']) ? $_GET['dim'] : IMAGE_STDDIM);
	$dir = PHOTOS_DIR . "/" . $photodir;
	if ($handle = opendir($dir)) {
		$cFile = 0;
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
	$kml_path =  "./" . PHOTOS_DIR . "/" . $photodir. ".kml";
	//echo $kml_path ;
//if(!file_exists($kml_path)) {   //TODO
	if(true){
	//Creer le fichier .kml
	$at_least_one = false;
	for ($i=0;$i < count($listFile); $i++) {
	
		$file_to_add = $listFile[$i];
		$exif = read_exif_data($dir.'/'.$listFile[$i], 0, true);
		if(isset($exif["GPS"]["GPSLatitude"][0])
			&& isset($exif["GPS"]["GPSLongitude"][0]))
		{
			$name= $file_to_add;
			$size = getimagesize($dir.'/'.$listFile[$i], $info);
			if (isset($info["APP13"])) {
				$iptc = iptcparse($info["APP13"]);
				$html_code = "<a href=\"./" . PHOTOS_DIR . "/". $photodir. "/" . $file_to_add ."\"><img src=\"http://". $_SERVER['SERVER_NAME'] . "/photos/". PHOTOS_DIR ."/". $photodir. "/". THUMBS_DIR . "/__" . $file_to_add ."\"></a><br/>";
				$html_code = $html_code . "<span class=\"legend\">";
				$html_code = $html_code . str_replace("\n","<br/>",extract_iptc_data($iptc, '2#120',""));
				$html_code = $html_code . "</span><br/>\n";
				$html_code = $html_code . extract_iptc_data($iptc, '2#025',"Tags : ")."<br/>\n";
			}
			$decimal_lat =  extract_gps_datas($exif["GPS"]["GPSLatitude"][0] , $exif["GPS"]["GPSLatitude"][1] , $exif["GPS"]["GPSLatitude"][2], $exif["GPS"]["GPSLatitudeRef"]);
			$decimal_long =  extract_gps_datas($exif["GPS"]["GPSLongitude"][0] , $exif["GPS"]["GPSLongitude"][1] , $exif["GPS"]["GPSLongitude"][2], $exif["GPS"]["GPSLongitudeRef"]);
			$kml_file = $kml_file . "<Placemark><name>" . $name . "</name><description><![CDATA[";
			$kml_file = $kml_file . $html_code;
			$kml_file = $kml_file . "]]></description><Point><coordinates>" . $decimal_long ."," . $decimal_lat . "</coordinates></Point></Placemark>";
			$at_least_one = true;
		}
	}
	//Ecrire le fichier
	if($at_least_one){
		write_kml_file($kml_file,$kml_path);
	}
}
//Afficher une carte google map
if(file_exists($kml_path)) {
	$kml_url = $url_path_datas. $photodir.".kml";
//	echo $kml_url ;
	add_map($kml_url);
}
else
{
	echo '<div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
}
break;
case ('gallery_map'):
	scan_invalid_char(PHOTOS_DIR); //scan des r�pertoires qui contiennent des caract�res interdits
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a></span></div>
<?php
	// listage des r�pertoires et fichiers
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
	$kml_gallery_filename = "gallery.kml";
	$kml_path =  "./" . PHOTOS_DIR . "/" .$kml_gallery_filename ;
	$placemarks = "";
	$at_least_one = false;
	for($iDir=0;$iDir< count($listDir); $iDir++){
		list($find_one, $placemark) = find_file_with_gps_data($listDir[$iDir]);
		if($find_one)
		{
			$placemarks = $placemarks .  $placemark ;
			$at_least_one = true;
		}
	}
	if($at_least_one){
		write_kml_file($placemarks,$kml_path);
	}
	if(file_exists($kml_path)) {
		$kml_url = $url_path_datas . $kml_gallery_filename;
	//	echo $kml_url ;
		add_map($kml_url);
	}
	else
	{
		echo '<div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
	}
break;
case ('slideshow'):
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
?>
	<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $page_index; ?>" class="Style1"><?php echo HOME_NAME; ?></a> &raquo; <?php echo str_replace($separateurs, ' ', $photodir); ?></span>
	<span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=map&dir=<?php echo $photodir; ?>" class="Style2"><?php echo DISPLAY_MAP ?></a></span></div>
<?php
	// listage des r�pertoires et fichiers
	if ($handle = opendir($dir)) {
		$cDir = 0;
		$cFile = 0;
		while (false !== ($file = readdir($handle))) {
		if($file != "." && $file != ".." && $file != ICO_FILENAME){
			if(is_dir("$dir/$file")){
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
	echo '<ul class="gallery slideshow clearfix">';
	for($i=0;$i<$cFile;$i++)
	{
		echo '<li><a href="'. $dir . "/" .$listFile[$i] .'" rel="prettyPhoto[pp_gal]" title="'. str_replace("\n",'<br/>','legend sur plusieurs lignes').'"><img src="'. $dir . "/" . THUMBS_DIR . "/__" .$listFile[$i] .'" alt="" /></a></li>';
	}
	echo '</ul>'
?>
<script type="text/javascript" charset="utf-8">

	$(document).ready(function(){
		$("a[rel^='prettyPhoto']").prettyPhoto({
			animation_speed: 'fast', /* fast/slow/normal */
			slideshow: 5000, /* false OR interval time in ms */
			autoplay_slideshow: true, /* true/false */
			opacity: 0.80, /* Value between 0 and 1 */
			show_title: true, /* true/false */
			allow_resize: true, /* Resize the photos bigger than viewport. true/false */
			default_width: 500,
			default_height: 344,
			counter_separator_label: '/', /* The separator for the gallery counter 1 "of" 2 */
			theme: 'facebook', /* light_rounded / dark_rounded / light_square / dark_square / facebook */
			hideflash: false, /* Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto */
			wmode: 'opaque', /* Set the flash wmode attribute */
			autoplay: true, /* Automatically start videos: True/False */
			modal: false, /* If set to true, only the close button will close the window */
			overlay_gallery: true, /* If set to true, a gallery will overlay the fullscreen image on mouse over */
			keyboard_shortcuts: true, /* Set to false if you open forms inside prettyPhoto */
			changepicturecallback: function(){}, /* Called everytime an item is shown/changed */
			callback: function(){}, /* Called when prettyPhoto is closed */
			markup: '<div class="pp_pic_holder"> \
						<div class="ppt">&nbsp;</div> \
						<div class="pp_top"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
						<div class="pp_content_container"> \
							<div class="pp_left"> \
							<div class="pp_right"> \
								<div class="pp_content"> \
									<div class="pp_loaderIcon"></div> \
									<div class="pp_fade"> \
										<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
										<div class="pp_hoverContainer"> \
											<a class="pp_next" href="#">next</a> \
											<a class="pp_previous" href="#">previous</a> \
										</div> \
										<div id="pp_full_res"></div> \
										<div class="pp_details clearfix"> \
											<p class="pp_description"></p> \
											<a class="pp_close" href="#">Close</a> \
											<div class="pp_nav"> \
												<a href="#" class="pp_arrow_previous">Previous</a> \
												<p class="currentTextHolder">0/0</p> \
												<a href="#" class="pp_arrow_next">Next</a> \
											</div> \
										</div> \
									</div> \
								</div> \
							</div> \
							</div> \
						</div> \
						<div class="pp_bottom"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
					</div> \
					<div class="pp_overlay"></div>',
			gallery_markup: '<div class="pp_gallery"> \
								<a href="#" class="pp_arrow_previous">Previous</a> \
								<ul> \
									{gallery} \
								</ul> \
								<a href="#" class="pp_arrow_next">Next</a> \
							</div>',
			image_markup: '<img id="fullResImage" src="" />',
			flash_markup: '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>',
			quicktime_markup: '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>',
			iframe_markup: '<iframe src ="{path}" width="{width}" height="{height}" frameborder="no"></iframe>',
			inline_markup: '<div class="pp_inline clearfix">{content}</div>',
			custom_markup: ''
		});
	});
</script>
	<?php
	break;
//fin du switch
}
if(DISPLAY_FOOTER)
	echo '<div class="fdgris" align="right"><span class="Style2">Php Photo Module 0.2.3 | auteur : <a href="http://www.jensen-siu.net" target="_blank" class="Style2" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" class="Style2" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a></span></div>';
?><noscript>
<!-- Si vous retirez la r�f�rence ci dessus pour des raisons esth�tiques, je vous remercie de laisser celle-ci que personne ne verra. Merci. -->
Php Photo Module 0.2.3 | auteur : <a href="http://www.jensen-siu.net" target="_blank" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a>
</noscript>
</body>
</html>
