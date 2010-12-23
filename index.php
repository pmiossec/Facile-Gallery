<?php 
///File under Licence CECILL

require("conf_en.php");
//require("conf_fr.php");

define('CACHE_DIR', '____cache'); //name of the folder where all the datas generated are placed
define('ICO_FILENAME', '_icon.jpg'); // name of the thumbnail image displayed in the main page

error_reporting(E_ALL); // afficher les erreurs
//error_reporting(0); // ne pas afficher les erreurs

if(isset($_GET['encode']))
{
	die(sha1($_GET['encode']));
}

session_start();

$private = (isset($_GET['private']) ? $_GET['private'] : "") == "1";
if($private)
	define('PRIVATE_PARAM', '&private=1');
else
	define('PRIVATE_PARAM', '');

if(PRIVATE_GALLERY_ACTIVATE && $private)
{
	$private_dir = PHOTOS_DIR;
	// check user & pwd:
	if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || !isset($_SESSION["login"])){
		header('WWW-Authenticate: Basic realm="'. AUTH_REQUIRED .'"');
		header('HTTP/1.0 401 Unauthorized');
		$_SESSION["login"] = true;
		die('<a href="?">'. PUBLIC_GALLERY .'</a>');
	}
	else
	{

		$usr = $_SERVER['PHP_AUTH_USER'];
		$pwd = $_SERVER['PHP_AUTH_PW'];
		if(ENCRYPTED_PASSWORD)
		{
			$pwd = sha1($pwd);
		}
		$login_successful = false;
		for($i=0;$i<count($auth_right_and_path);$i++)
		{
			if($usr ==$auth_right_and_path[$i][0] && $pwd ==$auth_right_and_path[$i][1])
			{
				$login_successful = true;
				$private_dir = $auth_right_and_path[$i][2];
				break;
			}
		}
		if($login_successful)
		{
			define('PHOTOS_DIR_ROOT', $private_dir);
		}else
		{
			unset($_SESSION["login"]);
			die('<a href="?">'. PUBLIC_GALLERY .'</a>');
		}
	}
}
else
{
	unset($_SESSION["login"]);
	define('PHOTOS_DIR_ROOT', PHOTOS_DIR);
}

$separateurs = array('_', '-', '.');
$file_format_managed = array("jpeg", "jpg", "gif", "png");
$directory = $_SERVER["SCRIPT_NAME"];
$directory = substr($directory, 0, strrpos($directory,"/")+1);
$url_path_script = "http://" . $_SERVER["SERVER_NAME"]. $directory . basename(__FILE__);
$url_path_datas = "http://" . $_SERVER["SERVER_NAME"]. $directory . PHOTOS_DIR_ROOT ."/";
$url_path_cache = "http://" . $_SERVER["SERVER_NAME"]. $directory . CACHE_DIR ."/";

$here = (isset($_GET['here']) ? $_GET['here'] : "");
$p = extract_page_parameters();
$gallery_page_num = get_page_parameter(get_deepth());
$thumb_page_num = get_thumb_page_num();
function get_deepth(){
	global $here;
	//return count($p)-1;
	if($here=="list" || $here=="detail")
	{
		return 1 + substr_count ( $_GET['dir'] , "/");
	}
	return 0;
}
function my_mkdir($path)
{
	mkdir($path, 0777, true);
}
function extract_page_parameters()
{
	if(!isset($_GET['p'])) return null;
	return explode ( ',', $_GET['p']);
}
function get_thumb_page_num()
{
	global $p;
	if($p == null || count($p) <= 1 ) return 1;
	return $p[count($p)-1];
}
function get_page_level($level)
{
	global $p;

	if($p == null ) return 1;
	$nb_p = count($p);
	//echo "count:$nb_p";
	if($level >= $nb_p) return 1;
	return $p[$level];
}

function get_page_parameter($level)
{
	global $p;

	if($p == null ) return "1";
	$nb_p = count($p);
	//echo "count:$nb_p";
	if($nb_p == 1) return $p[0];
	if(($level+1)>=$nb_p) return $_GET['p'];
	$new_p = "";
	for($i=0;$i<=$level;$i++)
	{
		if($i>0) $new_p.= ",";
		$new_p.= $p[$i];
	}
	return $new_p;
}
function construct_header($level, $photodir, $total_images, $photo_name, $index_photo_min, $index_photo_max, $separateurs)
{
	$header = '<span class="Style1">';
	if(DISPLAY_COPYLEFT)
		$header .= '&nbsp;<a class="Style1" href="javascript:myClick();">(?)</a>&nbsp;';
	$header .= '// ';

	//HOME
	if($level!=0)
	{
		$header .= '<a class="Style1" href="'. $_SERVER["PHP_SELF"] .'?here=default&amp;p=' . get_page_parameter(0) . PRIVATE_PARAM . '">';
	}
	$header .= HOME_NAME;
	//Directory
	if($level!=0)
	{
		$header .= '</a>';
		//$header .= '&raquo; ';
		$subdirs = explode(DIRECTORY_SEPARATOR, $photodir);
		$dir = "";
		switch($level)
		{
			//case 0 : $last = count($subdirs); break;
			case 1 : $last = count($subdirs)-1; break;
			case 2 : $last = count($subdirs); break;
		}
		//$last = count($subdirs) - $level == 2 ? 0 :1 ;
		for($iDir=0;$iDir<$last;$iDir++)
		{
			if($iDir!=0) $dir .= "/";
			$dir .= $subdirs[$iDir];
			$header .= '&raquo; <a class="Style1" href="' . $_SERVER["PHP_SELF"] . '?here=list&amp;dir='.$dir.'&amp;p=' . get_page_parameter($iDir+1) .PRIVATE_PARAM .'">';
			$header .= str_replace($separateurs, ' ', $subdirs[$iDir]);
			$image_num = (isset($_GET['image_num']) ? $_GET['image_num'] : "1");//vérification que le numéro de page existe bien
			$header .= '</a>';
		}
		if($level == 2)
		{
			$header .= ' &raquo; ' . $photo_name . ' n&deg;'. $image_num .' / ' . $total_images;
		}
		if($level == 1)
		{
			$header .= '&raquo; ' . str_replace($separateurs, ' ', $subdirs[$last]) . ' ('. $index_photo_min .' -> ' . $index_photo_max . ' / ' . $total_images . ')';
		}
	}
	$header .= '</span>';
	return $header;
}

function list_directory($dir2scan, $order_alphabetically, $exclude_files, $supported_extensions)
{
	$listDir = null;
	$listFile = null;
	// listage des répertoires et fichiers
	if ($handle = opendir($dir2scan)) {
		$cDir = 0;
		$cFile = 0;
		while (false !== ($file = readdir($handle))) {
		if($exclude_files==null || !in_array($file, $exclude_files)){
			if(is_dir($dir2scan . "/" . $file)){
				$listDir[$cDir] = $file;
				$cDir++;
			}
			else{
				$pathinfos = pathinfo($file);
				$file_ext = strtolower($pathinfos['extension']);
				if($supported_extensions == null || (strlen($file_ext)!= 0 && in_array($file_ext, $supported_extensions)))
				{
					$listFile[$cFile] = $file;
					$cFile++;
				}
			}
		}
	   }
	   closedir($handle);
	}
	if ($order_alphabetically == true) {
		if($listDir != null) usort($listDir,"strnatcmp");
		if($listFile != null) usort($listFile,"strnatcmp");
	}
	return array($listDir,$listFile);
}


function insert_thumbnail_cell($photodir, $thumb_dir, $image_file_name, $index_image, $legend, $gallery_page_num , $thumb_page_num)
{
	$cell_content = '<div class="cell">
		<div class="cell_image" style="width:' . (MINIATURE_MAXDIM + 6) .'px;height:' . (MINIATURE_MAXDIM + 6).'px">
				<a class="tooltip" href="' . $_SERVER["PHP_SELF"] .'?here=detail&amp;p='.$gallery_page_num.'&amp;dir=' . rawurlencode($photodir) .'&amp;image_num=' . ($index_image+1) .PRIVATE_PARAM.'">
					<img src="' . $thumb_dir."/".$image_file_name  .'" alt="' . $image_file_name .'" class="imageborder" />';

					if(strlen($legend) != 0) $cell_content .= my_nl2br("<em style=\"width:300px\"><span></span>" .utf8_encode($legend). "</em>");
	$cell_content .= '</a>
		</div>
		<div class="cell_text">
			<span class="Style2">' . wordTruncate(($index_image+1) ."|" . $image_file_name) .'</span>
		</div>
	</div>';
	return $cell_content;
}

function insert_subdir_cell($album_dir, $sub_album_dir, $thumb_dir, $image_file_name, $index_image, $legend, $gallery_page_num , $thumb_page_num)
{
	$cell_content = '<div class="cell">
		<div class="cell_image" style="width:' . (MINIATURE_MAXDIM + 6) .'px;height:' . (MINIATURE_MAXDIM + 6).'px">
				<a class="tooltip" href="' . $_SERVER["PHP_SELF"] .'?here=list&amp;p='.$gallery_page_num.','.$thumb_page_num.'&amp;dir=' . rawurlencode($album_dir . "/" .$sub_album_dir) .'&amp;image_num=' . ($index_image+1) .PRIVATE_PARAM .'">
				<img src="' . $thumb_dir."/". $sub_album_dir . "/" .$image_file_name .'" alt="' . $image_file_name .'" class="imageborder" /></a>
		</div>
		<div class="cell_text">
			<span class="Style2">' . $sub_album_dir .'</span>
		</div>
	</div>';
	return $cell_content;
}


function get_file_metadata_to_display($filepath,$exif_to_display, $iptc_to_display, $display_gps_data)
{
	$metadata_to_display = "";
	list($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_and_gps($filepath);
	if ($succes) {
		$isIptcDisplayed = false;
		if(strlen($legend) != 0)
		{
			$metadata_to_display .= '<p class="legend">' . my_nl2br($legend) . '</p>';
			$isIptcDisplayed = true;
		}
		for($i_iptc=0;$i_iptc<count($iptc_to_display);$i_iptc++)
		{
			list($code,$label)= $iptc_to_display[$i_iptc];
			if(strlen($label)!=0) $label = $label . ' : ';
			$iptc_with_label = extract_iptc_data($iptcs, $code, $label);
			if(strlen($iptc_with_label)!=0)
			{
				$metadata_to_display .=  $iptc_with_label."\n";
				$isIptcDisplayed = true;
			}
		}
		if($isIptcDisplayed) { $metadata_to_display .= '<hr size="1">'; }
	}
	if ($exifs!=null) {
		$metadata_to_display .=  $exifs["FILE"]["FileName"] . " || " . round(($exifs["FILE"]["FileSize"]/1024), 0) . " Ko || ".$exifs["COMPUTED"]["Width"]." x ".$exifs["COMPUTED"]["Height"]."px\n";
		for($i_exif=0;$i_exif<count($exif_to_display);$i_exif++)
		{
			list($field1, $field2, $label)= $exif_to_display[$i_exif];
			$text = extract_exif_data($exifs, $field1, $field2 , $label . ' : ');
			if(strlen($text) != 0)
				$metadata_to_display .=  $text."\n";
		}
		if($display_gps_data && $decimal_lat != 0 && $decimal_long != 0)
		{
			$metadata_to_display .=  "<a target=\"_blank\" href=\"http://maps.google.com/maps?ll=". $decimal_lat."," . $decimal_long."&amp;spn=0.01,0.01&amp;q=". $decimal_lat."," . $decimal_long."&amp;hl=fr\">GPS: ". $decimal_lat."," . $decimal_long."</a>";
		}
		/*$keys = array_keys($exif["EXIF"]);
		for ($i=0;$i < count($keys); $i++) {
			echo $keys[$i] . " :" . $exif["EXIF"][$keys[$i]] . "<br/>";
		}*/
	}
	return $metadata_to_display;
}
function my_nl2br($string)
{
	return str_replace(array("\r\n", "\n", "\r"), "", nl2br($string));
}

function display_pages_indexes($page_uri,$page_num, $totalPages)
{
	$pages_indexes = '<div class="little_space_around main_color" align="center"><span class="Style2">';
	if($totalPages == 1)
		return $pages_indexes . "</span></div>";

	if ($page_num > 1) {
		$pages_indexes .= "<a href=\"$page_uri" . ($page_num-1) .PRIVATE_PARAM.'" class="Style2">&laquo;</a> &nbsp;|&nbsp;';
	}

	for ($l =1; $l <= $totalPages; $l++) {
		if($l > 1) $pages_indexes .= " &nbsp;|&nbsp;";
		if ($page_num != $l) {
			$pages_indexes .= "<a href=\"$page_uri" . $l .PRIVATE_PARAM.'" class="Style2">' .$l .'</a>';
		} else {
			$pages_indexes .= "<b>$l</b>";
		}
	}
	if ($page_num < $totalPages) {
		$pages_indexes .= " &nbsp;|&nbsp;<a href=\"$page_uri" . ($page_num+1) .PRIVATE_PARAM.'" class="Style2">&raquo;</a>';
	}
	$pages_indexes .= '</span></div>';
	return $pages_indexes;
}


//list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata($filepath, $extract_gps_data);
function get_file_all_metadata($filepath, $extract_gps_data, $extrat_datas_only_if_gps_exists, $extrat_only_gps_datas)
{
	//Gestion des FATAL ERROR
	ob_start("fatal_error_handler");
	set_error_handler("handle_error");
	//causes a warning
	preg_replace();

	$decimal_lat = 0;
	$decimal_long = 0;
	$exif_exists = exif_imagetype($filepath) != IMAGETYPE_PNG && exif_imagetype($filepath) != IMAGETYPE_GIF;
	//would normally cause a fatal error, but instead our output handler will be called allowing us to handle the error.
	if($exif_exists)
		$exifs = read_exif_data($filepath, 0, true);
	//Gestion des FATAL ERROR
	ob_end_flush();
	if($extrat_datas_only_if_gps_exists || $extrat_only_gps_datas)
	{
		if(!$exif_exists)
		{
			return array(false, null, null, '', '', 0, 0);
		}
		if(!isset($exifs["GPS"]["GPSLatitude"][0])
		|| !isset($exifs["GPS"]["GPSLongitude"][0]))
		{
			return array(false, null, null, '', '', 0, 0);
		}
		$decimal_lat =  extract_gps_datas($exifs["GPS"]["GPSLatitude"][0] , $exifs["GPS"]["GPSLatitude"][1] , $exifs["GPS"]["GPSLatitude"][2], $exifs["GPS"]["GPSLatitudeRef"]);
		$decimal_long =  extract_gps_datas($exifs["GPS"]["GPSLongitude"][0] , $exifs["GPS"]["GPSLongitude"][1] , $exifs["GPS"]["GPSLongitude"][2], $exifs["GPS"]["GPSLongitudeRef"]);
		if($decimal_lat == 0 || $decimal_long == 0)
		{
			return array(false, null, null, '', '', 0, 0);
		}
		if($extrat_only_gps_datas)
		{
			return array(true, $exifs, null, '', '', $decimal_lat, $decimal_long);
		}
	}
	$size = getimagesize($filepath, $info);
	if (isset($info["APP13"])) {
		$iptcs = iptcparse($info["APP13"]);
		$legend = extract_iptc_data($iptcs, '2#120',"");
		$tags = extract_iptc_data($iptcs, '2#025',TAGS);
	}
	else
	{
		$iptcs = null;
		$legend = '';
		$tags = '';
	}
	if($extrat_datas_only_if_gps_exists)
		return array(true, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long);
	if(!$exif_exists)
		return array(true, null , $iptcs, $legend, $tags, 0, 0);
	if(!$extract_gps_data)
		return array(true, $exifs, $iptcs, $legend, $tags, 0, 0);
	if(!isset($exifs["GPS"]["GPSLatitude"][0])
	|| !isset($exifs["GPS"]["GPSLongitude"][0]))
		{ return array(true, $exifs, $iptcs, $legend, $tags, 0, 0);}
	$decimal_lat =  extract_gps_datas($exifs["GPS"]["GPSLatitude"][0] , $exifs["GPS"]["GPSLatitude"][1] , $exifs["GPS"]["GPSLatitude"][2], $exifs["GPS"]["GPSLatitudeRef"]);
	$decimal_long =  extract_gps_datas($exifs["GPS"]["GPSLongitude"][0] , $exifs["GPS"]["GPSLongitude"][1] , $exifs["GPS"]["GPSLongitude"][2], $exifs["GPS"]["GPSLongitudeRef"]);
	if($decimal_lat == 0 || $decimal_long == 0)
	{
		return array(true, $exifs, $iptcs, $legend, $tags, 0, 0);
	}
	return array(true, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long);
}
//list($succes,$exifs, $iptcs, $legend, $tags) = get_file_metadata($filepath);
function get_file_metadata($filepath)
{
	list($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_all_metadata($filepath, false, false, false);
	return array($succes, $exifs, $iptcs, $legend, $tags);
}

//list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_and_gps($filepath);
function get_file_metadata_and_gps($filepath)
{
	list($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_all_metadata($filepath, true, false, false);
	return array($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long);
}

//list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_only_gps($filepath);
function get_file_metadata_only_gps($filepath)
{
	list($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_all_metadata($filepath, false, false, true);
	return array($succes, $decimal_lat, $decimal_long);
}

//list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_only_if_gps_exists($filepath);
function get_file_metadata_only_if_gps_exists($filepath)
{
	list($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_all_metadata($filepath, false, true, false);
	return array($succes, $exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long);
}

function fatal_error_handler($buffer) {
	if (ereg("(error</b>:)(.+)(<br)", $buffer, $regs) ) {
		$err = preg_replace("/<.*?>/","",$regs[2]);
		error_log($err);
		return "ERROR CAUGHT check log file";
	}
	return $buffer;
}

function handle_error ($errno, $errstr, $errfile, $errline){
	error_log("$errstr in $errfile on line $errline");
	if($errno == E_ALL){
		ob_end_flush();
		echo "ERROR CAUGHT check log file";
		exit(0);
	}
}

function write_kml_file($kml_placemarks, $kml_path){
	$kml_content = '<?xml version= "1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>'
	               . $kml_placemarks . '</Document></kml>';
	$fh = fopen($kml_path, 'w') or die("can't open file");
	fwrite($fh, $kml_content);
	fclose($fh);
}

function add_map($url_kml_file){
	//DOC : http://www.touraineverte.com/aide-documentation-exemple-tutoriel-didacticiel/api-google-maps/kml-kmz/creer-creation-carte-map-mes-cartes/utiliser-fichier-kml-generer-creer-google-earth/importer-carte-via-api-google-maps-new-GGeoXml.htm
	echo '<div id="map_canvas" style="width:95%; height:95%"></div><br/>
	<script type="text/javascript">
	function initialize() {
		var myLatlng = new google.maps.LatLng(48.857798,2.296765);
		var myOptions = { zoom: 11, center: myLatlng, mapTypeId: google.maps.MapTypeId.HYBRID }
		var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		var ctaLayer = new google.maps.KmlLayer("' . $url_kml_file . '");
		ctaLayer.setMap(map);
	}
	</script>';
}
function echo_message_with_history_back($message)
{
	echo '<a align="center" href="javascript:history.go(-1)">' . $message . '</a>';
}
function verify_directories(){
	$album_dir = (isset($_GET['dir']) ? $_GET['dir'] : "");
	if (!isset($_GET['dir']) || $_GET['dir'] == "") {//on vérifie que le répertoire photo existe bien
		echo_message_with_history_back(PHOTO_DIR_NEEDED);
		return array (false, '', '');
	}
	//on supprime les slash, antislash et points possibles pour éviter les failles de sécurité
	$album_dir = preg_replace("/\\\\/", "", $album_dir);
	$str2clean = array("." => "");
	$album_dir = strtr($album_dir, $str2clean);
	$album_path = PHOTOS_DIR_ROOT . "/" . $album_dir;
	if (!file_exists($album_path)) {
		echo_message_with_history_back(PHOTO_DIR_NOT_EXISTING);
		return array (false, '', '', '', '');
	}
	$album_cache_path = CACHE_DIR . "/" . $album_dir;
	if (!file_exists($album_cache_path)) { my_mkdir($album_cache_path);}
	$cache_resized_image_dir = $album_cache_path . "/" . IMAGE_STDDIM;
	if (!file_exists($cache_resized_image_dir)) { my_mkdir($cache_resized_image_dir);}
	return array (true, $album_dir, $album_path, $album_cache_path, $cache_resized_image_dir);
}

///fonction qui convertit les données GPS de degrés, minutes, secondes en decimal
function extract_gps_datas($exif_deg, $exif_min, $exif_sec, $exif_hem)
{
	$deg=divide_gps_coordinates($exif_deg);
	$min=divide_gps_coordinates($exif_min);
	$sec=divide_gps_coordinates($exif_sec);
	//Hémisphère (N, S, W ou E):
	$hem=$exif_hem;

	///Altitude: $alt=$exif["GPS"]["GPSAltitude"][0];

	if ($hem === "N" || $hem === "E") { $gps_ref2 = 1; }
	else { $gps_ref2 = -1; }
	return $gps_ref2 *($deg + $min / 60 + $sec/3600) ;
}

///fonction qui extrait et met en forme une donnée exif
function extract_exif_data($exifs, $field1, $field2, $label){
	if (isset($exifs[$field1][$field2]))
		return $label . utf8_decode($exifs[$field1][$field2]);
	else return "";
}

///fonction qui extrait et met en forme une donnée IPTC
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

///fonction qui retourne un nombre correspondant à une donnée GPS
function divide_gps_coordinates($a){
	// evaluate the string fraction and return a float //
	$e = explode('/', $a);
	// prevent division by zero //
	if (!$e[0] || !$e[1]) { return 0; }
	else{ return $e[0] / $e[1]; }
}

///fonction qui renomme les dossiers comprenant des caractères interdits
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

function create_album_icon($dir2iconize,$file_format_managed) {
		
		$album_icon_path = CACHE_DIR . "/" . $dir2iconize . "/" . ICO_FILENAME;
		$need_to_create = true;
		if (file_exists($album_icon_path)) {
			list($width, $height, $type, $attr) = getimagesize($album_icon_path);
			if ($width == ICO_WIDTH || $height == ICO_HEIGHT) {
				$need_to_create = false;
			}
		}

	if(!$need_to_create) return;
	$path_dir2iconize = PHOTOS_DIR_ROOT."/".$dir2iconize; //chemin vers le répertoire dont on doit créer l'icone
	list($listDir, $listFile) = list_directory($path_dir2iconize, ALPHABETIC_ORDER,
			array(".", ".."),
			$file_format_managed);

	$first_dir_item_path = $path_dir2iconize."/".$listFile[0]; // create icon with the first image

	list($srcWidth, $srcHeight, $type, $attr) = getimagesize($first_dir_item_path);//on liste les valeur de l'image
	//$miniature = imagecreatetruecolor(ICO_WIDTH, ICO_HEIGHT);
	if ($type == 1) {
		$handle = imagecreatefromgif($first_dir_item_path);
	} else
	if ($type == 2) {
		$handle = imagecreatefromjpeg($first_dir_item_path);
	} else
	if ($type == 3) {
		$handle = imagecreatefrompng($first_dir_item_path);
	} else {
		echo "Error getting type and size of " . $first_dir_item_path;
		return;
	}
	

	if ($srcWidth >= ICO_WIDTH && $srcHeight >= ICO_HEIGHT)
	{
		$newHandle = imagecreatetruecolor(ICO_WIDTH, ICO_HEIGHT);
		if (!$newHandle)
			return false;

		if($srcHeight < $srcWidth)
		{
			$ratio = (double)($srcHeight / ICO_HEIGHT);

			$cpyWidth = round(ICO_WIDTH * $ratio);
			if ($cpyWidth > $srcWidth)
			{
				$ratio = (double)($srcWidth / ICO_WIDTH);
				$cpyWidth = $srcWidth;
				$cpyHeight = round(ICO_HEIGHT * $ratio);
				$xOffset = 0;
				$yOffset = round(($srcHeight - $cpyHeight) / 2);
			} else {
				$cpyHeight = $srcHeight;
				$xOffset = round(($srcWidth - $cpyWidth) / 2);
				$yOffset = 0;
			}
		} else {
			$ratio = (double)($srcWidth / ICO_WIDTH);

			$cpyHeight = round(ICO_HEIGHT * $ratio);
			if ($cpyHeight > $srcHeight)
			{
				$ratio = (double)($srcHeight / ICO_HEIGHT);
				$cpyHeight = $srcHeight;
				$cpyWidth = round(ICO_WIDTH * $ratio);
				$xOffset = round(($srcWidth - $cpyWidth) / 2);
				$yOffset = 0;
			} else {
				$cpyWidth = $srcWidth;
				$xOffset = 0;
				$yOffset = round(($srcHeight - $cpyHeight) / 2);
			}
		}
		if (!imagecopyresampled($newHandle, $handle, 0, 0, $xOffset, $yOffset, ICO_WIDTH, ICO_HEIGHT, $cpyWidth, $cpyHeight))
			return false;
		imagedestroy($handle);

		imagejpeg($newHandle, $album_icon_path, GLOBAL_JPG_QUALITY);
		imagedestroy($newHandle);
	} else {
		imagejpeg($handle, $album_icon_path, GLOBAL_JPG_QUALITY);
		imagedestroy($handle);
	}
}

///fonction pour trouver une image ayant des données GPS
function find_file_with_gps_data($dir_root, $dir2findgps, $url_path_script, $url_path_datas) {
	$dir = $dir_root."/".$dir2findgps;
	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", ".."), $file_format_managed);

	for($i=0;$i<count($listFile);$i++){
		$decimal_lat = 0;
		$decimal_long = 0;
		list($succes, $decimal_lat, $decimal_long) = get_file_metadata_only_gps($dir.'/'.$listFile[$i]);
		if($succes){
			$html_code = "<a href=\"$url_path_script?here=list&amp;dir=$dir2findgps\"><img src=\"$url_path_datas$dir2findgps/". ICO_FILENAME ."\"></a><br/>";
			$html_code = "<Placemark><name>$dir2findgps</name><description><![CDATA[$html_code]]></description><Point><coordinates>" . $decimal_long ."," . $decimal_lat . "</coordinates></Point></Placemark>";
			return array(true, $html_code);
		}
	}
	//try to find in subdirs
	for($i=0;$i<count($listDir);$i++){
		list($find_one, $html_code) = find_file_with_gps_data($dir, $listDir[$iDir], $url_path_script, $url_path_cache);
		if($find_one)
		{
			return array(true, $html_code);
		}
	}
	return array(false, "");
}

function create_thumbs_of_dir($album_dir_way, $file_format_managed)
{
	$album_dir_path = PHOTOS_DIR_ROOT ."/".$album_dir_way;
	$cache_dir_path = CACHE_DIR ."/".$album_dir_way;
	echo $full_dir;
	list($listDir, $listFile) = list_directory($album_dir_path, ALPHABETIC_ORDER,
			array(".", ".." , IMAGE_STDDIM, ICO_FILENAME),
			$file_format_managed);
	if (!file_exists($cache_dir_path)) { my_mkdir($cache_dir_path); }
	list($listDirThumb, $listFileThumb) = list_directory($cache_dir_path, ALPHABETIC_ORDER, null, null);
	if(count($listFileThumb) > count($listFile))
	{
		for($i=0;$i<count($listFileThumb);$i++){
			if(!file_exists($album_dir_path ."/" . $listFileThumb[$i]))
				unlink($listFileThumb[$i]);
		}
	}
	for($i=0;$i<count($listFile);$i++){
		create_thumb($album_dir_path . "/" . $listFile[$i], $cache_dir_path . "/" . $listFile[$i]);
	}
	if(count($listFile) != 0) return $listFile[0];
	return null;
}

function create_thumb($path_file2miniaturize, $path_file_miniaturized)
{
	create_newimage($path_file2miniaturize, $path_file_miniaturized, MINIATURE_MAXDIM);
}

function create_newimage($path_file2miniaturize, $path_file_miniaturized, $dimensionmax) {
		$need_to_create = true;
		if (file_exists($path_file_miniaturized)) {
			list($width, $height, $type, $attr) = getimagesize($path_file_miniaturized);
			if ($width != $dimensionmax || $height != $dimensionmax) {
				$need_to_create = false;
			}
		}

	if(!$need_to_create) return;
	list($width, $height, $type, $attr) = getimagesize($path_file2miniaturize);
	if ($width >= $height) {
		$newwidth = $dimensionmax;
		$newheight = ($dimensionmax*$height)/$width;
	} else {
		$newwidth = ($dimensionmax*$width)/$height;
		$newheight = $dimensionmax;
	}
	$miniature = imagecreatetruecolor($newwidth, $newheight);
	if ($type == 1) {
		$image = imagecreatefromgif($path_file2miniaturize);
	}
	if ($type == 2) {
		$image = imagecreatefromjpeg($path_file2miniaturize);
	}
	if ($type == 3) {
		$image = imagecreatefrompng($path_file2miniaturize);
	}
	imagecopyresampled($miniature, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagedestroy($image);
	imagejpeg($miniature, $path_file_miniaturized, GLOBAL_JPG_QUALITY);
	imagedestroy($miniature);
}

///fonction pour tronquer un nom trop long
function wordTruncate($str) {
	$str_to_count = html_entity_decode($str);
	if (strlen($str_to_count) <= PHOTONAME_MAXCHAR+6) {
		return $str;
	} else {
		$str2 = substr($str_to_count, 0, PHOTONAME_MAXCHAR +3)."...";
		return htmlentities($str2);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo (isset($_GET['dir']) ? $_GET['dir'] : HOME_NAME);?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<noscript>
	Based on :
	- Php script by "Php Photo Module" 0.2.3 ( http://www.atelier-r.net/scripts.php ) / Jensen SIU  (http://www.jensen-siu.net )
	- Slideshow by PrettyPhoto ( http://www.no-margin-for-errors.com ) / Stephane Caron
	</noscript>
	<style type="text/css">
/* For the fixed header (begin) */
body{
	margin:0;
	padding:35px 0 0 0;
}
div.header{
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:35px;
}
@media screen{
	body>div.header{
	position: fixed;
	}
}
* html body{
	overflow:hidden;
}
div.table{
	padding-top:45px;
}
/*For the fixed header (end) */

.main_color
{
	background-color: <?php echo MAIN_COLOR; ?>;
}
.color_light
{
	background-color: <?php echo LIGHT_COLOR; ?>;
}
.color_dark
{
	background-color: <?php echo DARK_COLOR; ?>;
}
div.popup
{
	position: absolute;
	top: 25%;
	left: 25%;
	width: 50%;
	height: 50%;
	padding: 5px;
	z-index: 2500;
}
div.ligne {
	clear:both;
}
div.cell {
	float: left;
	margin: 2px;
	border:  5px solid <?php echo LIGHT_COLOR; ?>;
}

div.cell_image {
	border:  0px;
	background-color: <?php echo LIGHT_COLOR; ?>;
	padding: 0px;
	margin : 0px;
	text-align:center;
	display: table-cell;
	vertical-align: middle;
}
div.cell_image a{
	border:  0px;
	padding: 0px;
	margin : 0px;
}

div.cell_text {
	background-color: <?php echo DARK_COLOR; ?>;
	padding: 2px;
	text-align:center;
}
.Style1 {
	font-size: small;
	font-weight: bold;
	color: <?php echo TEXT_COLOR; ?>;
}
a.Style1 {
	text-decoration: underline;
}
a.Style1:hover {
	text-decoration: none;
}
.Style2 {
	font-size: xx-small;
	color: <?php echo TEXT_COLOR; ?>;
}
a.Style2 {
	text-decoration: underline;
}
a.Style2:hover {
	text-decoration: none;
}
table{
	margin-left:auto;
	margin-right:auto;
	border :0;
	padding:1px;
	spacing:1px;
}

body,td,th {
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
tr.tddeco {
	border: 1px solid <?php echo DARK_COLOR; ?>;
}
td.tdover {
	border: 1px solid <?php echo DARK_COLOR; ?>;
	background-color: <?php echo PAGE_COLOR; ?>;
}
td.tdover:hover {
	border: 1px solid <?php echo DARK_COLOR; ?>;
}
td {
	background-color: <?php echo LIGHT_COLOR; ?>;
}

.fdgris {
	background-color: <?php echo DARK_COLOR; ?>;
	padding: 2px;
}
td.fdgris {
	text-align : center;
}

.little_space_around {
	padding: 2px;
}
body {
	background-color: <?php echo PAGE_COLOR; ?>;
	margin: 10px;
}
.imageborder {
	border: 1px solid <?php echo DARK_COLOR; ?>;
	padding: 0px;
	margin: 0px;
}
.legend{
	font-weight: bold;
}
/*Tooltip*/
a.tooltip {
	border:  0px;
	padding: 0px;
	margin : 0px;
}
a.tooltip em {
	display:none;
}
a.tooltip:hover {
	border: 0;
	position: relative;
	z-index: 500;
	text-decoration:none;
}
a.tooltip:hover em {
	font-style: normal;
	font-size: xx-small;
	display: block;
	position: absolute;
	top: 24px; /* At least the Height of the image*/
	left: -5px;
	padding: 5px;
	color: #000;
	border: 1px solid #bbb;
	background: <?php echo MAIN_COLOR; ?>;
	/*width:auto;*/
	text-align:left;
}
a.tooltip:hover em span {
	position: absolute;
	top: -10px; /* -1 x Height of the image*/
	left: 15px;
	height: 10px; /* Height of the image*/
	width: 19px; /* Width of the image*/
	background: transparent url(tooltip.png);
	margin:0;
	padding: 0;
	border: 0;
}
</style>
<?php if(GOOGLEMAP_ACTIVATE) { ?>
	<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
	<style type="text/css">
		html { height: 100% }
		body { height: 100%; margin: 0px; padding: 0px }
		#map_canvas { height: 100% ; margin-left: auto; margin-right: auto; }
	</style>
<?php }

$activate_slideshow = SLIDESHOW_ACTIVATE; //TODO : ajouter la vérification de la présence de la librairie
if($here =="list" && $activate_slideshow){?>
	<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
	<script src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function(){
			$("a[rel^='prettyPhoto']").prettyPhoto({
				//Parameters slideshow : you can modify to your wish!!!
				animation_speed: 'fast', /* fast/slow/normal */
				slideshow: 10000, /* false OR interval time in ms */
				autoplay_slideshow: false, /* true/false */
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
			<?php if(SLIDESHOW_FULLSCREEN){
				//Parameters slideshow in fullscreen : Don't touch!!!
				?>
				fullscreen:true, /* true/false */
				markup: '<div class="pp_pic_holder"> \
							<div class="pp_content_container_fullscreen"> \
									<div class="pp_content"> \
										<div class="pp_loaderIcon"></div> \
										<div class="pp_details_fullscreen clearfix"> \
											<div class="ppt_fullscreen">&nbsp;</div> \
											<div style="width:100%">\
												<div class="pp_nav"> \
													<a href="#" class="pp_arrow_previous">Previous</a> \
													<p class="currentTextHolder">0/0</p> \
													<a href="#" class="pp_arrow_next">Next</a> \
												</div> \
												<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
												<a href="#" class="pp_close">Close</a> \
											</div>\
											<div class="pp_description"></div> \
										</div> \
										<div class="pp_fade"> \
											<div id="pp_full_res"></div> \
											<div class="pp_hoverContainer"> \
												<a class="pp_next" href="#">next</a> \
												<a class="pp_previous" href="#">previous</a> \
											</div> \
										</div> \
									</div> \
							</div> \
						</div> \
						<div class="pp_overlay_fullscreen"></div>',
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
			<?php }
				else{ echo "fullscreen:false, /* true/false */"; }
			?>
			});
		});
	</script>
<?php
}?>
</head>
<?php
if(GOOGLEMAP_ACTIVATE && ($here =="map" || $here =="gallery_map")){
	echo '<body onload="initialize()">';
}
else
{
	echo "<body>";
}
ini_set('max_execution_time', 120); //2 mn max
switch ($here) {
//listing des répertoires photos sur la page d'index par défaut
default:
	scan_invalid_char(PHOTOS_DIR_ROOT); //scan des répertoires qui contiennent des caractères interdits
	$ico_per_page = ICO_LINES * ICO_PER_LINE;
	list($listDir, $listFile) = list_directory("./".PHOTOS_DIR_ROOT, ALPHABETIC_ORDER,
			array(".", "..", IMAGE_STDDIM, ICO_FILENAME),
			$file_format_managed);

	$total_icons = count($listDir);
	$totalPages = ceil($total_icons/$ico_per_page);
	$pn=get_page_level(0);
	$page_num = $pn <= $totalPages ? $pn : $totalPages -1 ;
	$pages_html_indexes = display_pages_indexes($_SERVER["PHP_SELF"] . "?here=default&amp;p=", $page_num, $totalPages);
	echo '<div class="header"><div class="fdgris">' . construct_header(0,PHOTOS_DIR_ROOT, $total_icons, null, null, null, $separateurs);
?>
	<?php if(GOOGLEMAP_ACTIVATE) { ?>&nbsp;<span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=gallery_map<?php echo PRIVATE_PARAM; ?>" class="Style2"><?php echo DISPLAY_MAP ?></a></span>&nbsp;<?php }
		if( GOOGLEMAP_ACTIVATE && PRIVATE_GALLERY_ACTIVATE){?><span class="Style2" style="float:right;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><?php }
		if(PRIVATE_GALLERY_ACTIVATE) { ?>&nbsp;<span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; echo !$private ? '?private=1' : '' ?>" class="Style2"><?php echo $private ? PUBLIC_GALLERY : PRIVATE_GALLERY ?></a></span>&nbsp;<?php } ?>
	</div>
   <?php echo $pages_html_indexes; ?>
	</div>
	<br>
	<div class="table" style="width:<?php echo ICO_PER_LINE * (ICO_WIDTH + THUMB_MARGIN)?>px;margin:auto;">
	<?php
	$k=0;
	for ($i = $ico_per_page*($page_num-1); $i < ($total_icons > ($ico_per_page*($page_num)) ? $ico_per_page*$page_num : $total_icons); $i++) {
		$tooltip_filepath = PHOTOS_DIR_ROOT . "/" . $listDir[$i] . "/" . FOLDER_INFO_FILENAME;
		$legend = null;
		if (file_exists($tooltip_filepath)) {
			$legend = file_get_contents($tooltip_filepath);
		}
		create_album_icon($listDir[$i], $file_format_managed);
		?>
		<div class="cell">
			<div class="cell_image" style="height:<?php echo ICO_HEIGHT ?>px">
				<a class="tooltip" href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=list&amp;p=<?php echo $page_num; ?>&amp;dir=<?php echo $listDir[$i]; echo PRIVATE_PARAM; ?>"><img src="<?php echo CACHE_DIR . "/" . $listDir[$i] . "/" . ICO_FILENAME ?>" alt="<?php echo str_replace($separateurs, ' ', $listDir[$i]); ?>" class="imageborder"><?php if(strlen($legend) != 0) echo my_nl2br("<em><span></span>$legend</em>");?></a>
			</div>
			<div class="cell_text fdgris"><span class="Style2"><?php
				$titre_album = str_replace($separateurs, ' ', $listDir[$i]);
				$nbmots = explode(" ", $titre_album);
				$maxword2show = ((count($nbmots) < 6) ? count($nbmots) : 6);
				$wordnb = 0;
				while ($wordnb <$maxword2show) {
					echo  $nbmots[$wordnb] . " ";
					$wordnb++;
				}
				echo ((count($nbmots) > 6) ? " ..." : "");
			?></span>
			</div>
		</div>
	<?php
		print is_int(($k+1)/ICO_PER_LINE) ? '<div class="line"></div>': "";
		$k++;
	}
	?>
	</div>
	<?php
	break;//default

case ('list'): //album thumb listing
	$miniatures_per_page = MINIATURES_LINES * MINIATURES_PER_LINE;
	list($continue, $album_dir, $album_dir_path, $thumb_dir, $image_dir) = verify_directories();
	if(!$continue) {break;}

	list($listDir, $listFile) = list_directory($album_dir_path, ALPHABETIC_ORDER,
			array(".", ".." , IMAGE_STDDIM, ICO_FILENAME),
			$file_format_managed);
	list($listDirThumb, $listFileThumb) = list_directory($thumb_dir, ALPHABETIC_ORDER, null, null);

	$total_files = count($listFile);
	for($i=0;$i<$total_files;$i++)
	{
		$file_datas[$i] = array($listFile[$i], get_file_metadata("./$album_dir_path/$listFile[$i]"));
	}

	if($activate_slideshow)
	{
		for($i=0;$i<count($file_datas);$i++)
		{
			if($i!=0){
				$images.=',';
				$titles.=',';
				$descriptions.=',';
			}
			list($image_file_name, $datas) = $file_datas[$i];
			list($succes, $exifs, $iptcs, $legend, $tags) = $datas;
			$images .= "'./$album_dir_path/$image_file_name'";
			$titles .= "'$image_file_name'";
			if($succes)
			{
				$descriptions.="'".my_nl2br($legend)."'";
			}
			else
			{
				$descriptions.="''";
			}
		}
		$images="images = [$images];";
		$titles="titles = [$titles];";
		$descriptions="descriptions = [$descriptions];";

		echo '<script type="text/javascript" charset="utf-8">' , $images , $titles , $descriptions, 'function slideshow(){$.prettyPhoto.open(images,titles,descriptions);}</script>';
	}
	$total_dirs = count($listDir);
	$totalPages =ceil(($total_files + $total_dirs)/$miniatures_per_page);
	$pages_html_indexes = display_pages_indexes($_SERVER["PHP_SELF"] . "?here=list&amp;dir=$album_dir&amp;p=". get_page_parameter(get_deepth()-1) .",", $thumb_page_num, $totalPages);
	$index_photo_min = (($thumb_page_num-1)*$miniatures_per_page)+1;
	if ($thumb_page_num < $totalPages )
	{ $index_photo_max = (($thumb_page_num)*$miniatures_per_page); } else { $index_photo_max = $total_files; }

	echo '<div class="header"><div class="fdgris">'. construct_header(1, $album_dir, $total_files, null , $index_photo_min, $index_photo_max, $separateurs);
	?>
	<?php if(GOOGLEMAP_ACTIVATE) { ?><span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=map&amp;dir=<?php echo $album_dir; echo PRIVATE_PARAM; ?>" class="Style2"><?php echo DISPLAY_MAP ?></a></span><?php }
			if( GOOGLEMAP_ACTIVATE && $activate_slideshow){?><span class="Style2" style="float:right;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><?php }
			if($activate_slideshow){?><span class="Style2" style="float:right;"><a href="#" onClick="slideshow();return false;" class="Style2"><?php echo SLIDESHOW ?></a></span><?php } ?></div>

	<?php echo $pages_html_indexes; ?>
	</div>
	<div class="table" style="width:<?php echo MINIATURES_PER_LINE * (MINIATURE_MAXDIM + 20 )?>px;margin:auto;">
	<?php
	$total_thumbFloor = $miniatures_per_page*$thumb_page_num;
	$borne_min = $total_thumbFloor - $miniatures_per_page;
	$k=0;
	//directories
	if($total_dirs > $borne_min)
	{
		for ($i = $borne_min; $k < $miniatures_per_page && $i < $total_dirs ; $i++,$k++) {
			$image_file_name = create_thumbs_of_dir($album_dir. "/" . $listDir[$i], $file_format_managed);
			echo insert_subdir_cell($album_dir, $listDir[$i], $thumb_dir, $image_file_name, $i, "", $gallery_page_num , $thumb_page_num);
			print is_int(($k+1)/MINIATURES_PER_LINE) ? '<div class="line"></div>': "";
		}
	}
	//photos
	$j = $borne_min + $k - $total_dirs;
	for ($i = $borne_min + $k; $k < $miniatures_per_page && $i < $total_dirs + $total_files; $i++,$k++,$j++) {
		list($image_file_name, $datas) = $file_datas[$j];
		$legend = get_file_metadata_to_display('./' .$album_dir_path.'/'.$image_file_name, $exif_to_display, $iptc_to_display, false);
		create_thumb($album_dir_path . "/" . $image_file_name , $thumb_dir . "/" . $image_file_name);
		echo insert_thumbnail_cell($album_dir, $thumb_dir, $image_file_name, $j, $legend, $gallery_page_num , $thumb_page_num);
		print is_int(($k+1)/MINIATURES_PER_LINE) ? '<div class="line"></div>': "";
	}
	?>
	</div>
<?php
	break;//list

//détail de la photo
case ('detail'):
	list($continue, $album_dir, $album_dir_path, $thumb_dir, $images_dir_path) = verify_directories();
	if(!$continue) {break;}
	$photo = (isset($_GET['image_num']) ? $_GET['image_num'] : "");
	list($listDir, $listFile) = list_directory($album_dir_path, ALPHABETIC_ORDER,
			array(".", ".." , IMAGE_STDDIM, ICO_FILENAME),
			$file_format_managed);

	list($listDirResized, $listFileResized) = list_directory($images_dir_path, false,
			array(".", ".."),
			$file_format_managed);

	if ($photo == "" || !isset($listFile[$photo-1])) {
		echo_message_with_history_back(NO_PHOTO_TO_DISPLAY);
		break;
	}
	//delete all when orphans!!
	if(count($listFile)< count($listFileResized))
	{
		for($i=0;$i<count($listFileResized);$i++){
			if(!file_exists($album_dir_path ."/" . $listFileResized[$i]))
				unlink($images_dir_path ."/" . $listFileResized[$i]);
		}
	}
	$photo = $photo -1;
	$image_path = $images_dir_path . "/" . $listFile[$photo];
	create_newimage($album_dir_path ."/" .$listFile[$photo], $image_path, IMAGE_STDDIM);
	$total_images = count($listFile);// on compte le nombre d'éléments dans le dossier sans compter "." et ".."
	list($width, $height, $type, $attr) = getimagesize($image_path);
	if ($photo > 0)
	{
		create_thumb($album_dir_path ."/" .$listFile[$photo-1], $thumb_dir  ."/" .$listFile[$photo-1]);
	}
	if ($photo < $total_images-1)
	{
		create_thumb($album_dir_path ."/" .$listFile[$photo+1], $thumb_dir  ."/" .$listFile[$photo+1]);
	}
	echo '<div class="fdgris">'.construct_header(2, $album_dir, $total_images, $listFile[$photo], null, null, $separateurs) . '</div>';
?>
<table>
	<tr>
		<td>
		<?php if ($photo > 0) { echo insert_thumbnail_cell($album_dir, $thumb_dir, $listFile[$photo-1], $photo-1, "", $gallery_page_num , $thumb_page_num); }?>
	</td>
	<td>
		<table>
			<tr>
				<td align="center" valign="middle">
			<?php if ($photo >= 0 && $photo < $total_images) { ?>
						<a href="<?php echo $album_dir_path . "/" . $listFile[$photo]; ?>">
							<img src="<?php echo $images_dir_path . "/" . $listFile[$photo]; ?>" alt="<?php echo $listFile[$photo]; ?>" <?php echo $attr; ?> class="imageborder">
						</a><?php
					} else { echo_message_with_history_back( NO_PHOTO_TO_DISPLAY ); } ?>
				</td>
			</tr>
			<tr>
				<td class="fdgris">
					<span class="Style2">
					<?php
					echo utf8_encode(my_nl2br(get_file_metadata_to_display($album_dir_path.'/'.$listFile[$photo],$exif_to_display, $iptc_to_display, true)));
					?>
					</span>
				</td>
			</tr>
		</table>
	</td>
	<td>
	<?php if ($photo < $total_images -1) {echo insert_thumbnail_cell($album_dir, $thumb_dir, $listFile[$photo+1], $photo+1, "", $gallery_page_num , $thumb_page_num); }?>
		</td>
	</tr>
</table>
<?php if(COMMENTS_ACTIVATE && strlen(DISQUS_SHORTNAME)!=0)
	{ ?>
	<div id="disqus_thread" style="width:80%;margin:auto"></div>
	<script type="text/javascript">
		var disqus_shortname = '<?php echo DISQUS_SHORTNAME ?>';
		var disqus_identifier = '<?php echo $album_dir_path .'/'. $listFile[$photo]?>'; //page id
		//var disqus_url = '...';
		(function() {
			var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
			dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
		})();
	</script>
	<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
	<?php
}
break;//detail

case ('map'):
	if(!GOOGLEMAP_ACTIVATE) {break;}
	list($continue, $album_dir, $album_dir_path, $thumb_dir, $images_dir_path) = verify_directories();
	if(!$continue) {break;}
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=default<?php echo PRIVATE_PARAM; ?>" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=list&amp;dir=<?php echo $album_dir; echo PRIVATE_PARAM ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $album_dir); ?></a></span>
<?php
	$photo = (isset($_GET['photo']) ? $_GET['photo'] : "");
	$create_kml_file = (isset($_GET['create']) ? $_GET['create'] : "");

	list($listDir, $listFile) = list_directory($album_dir_path, ALPHABETIC_ORDER,
			array(".", "..", IMAGE_STDDIM, ICO_FILENAME),
			$file_format_managed);

	$kml_filname = str_replace('/', '_', $album_dir);
	$kml_path =  "./" . CACHE_DIR . "/" . $kml_filname . ".kml";
	if(!file_exists($kml_path) || $create_kml_file="true") {
	$at_least_one = false;
	for ($i=0;$i < count($listFile); $i++) {
	
		$file_to_add = $listFile[$i];
		list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_only_if_gps_exists($album_dir_path.'/'.$listFile[$i]);
		if($succes)
		{
			$name= $file_to_add;
			if($iptcs != null)
			{
				$html_code = "<a href=\"$url_path_datas$album_dir/$file_to_add\"><img src=\"$url_path_cache$album_dir/$file_to_add\"></a><br/>
					<span class=\"legend\">" . my_nl2br($legend) . "</span><br/>\n $tags<br/>\n";
			}
			$kml_file = $kml_file . "<Placemark><name>$name</name><description><![CDATA[$html_code]]></description><Point><coordinates>$decimal_long,$decimal_lat</coordinates></Point></Placemark>";
			$at_least_one = true;
		}
	}

	if($at_least_one){
		write_kml_file($kml_file,$kml_path);
	}
}
//Afficher une carte google map
if(file_exists($kml_path)) {
	$kml_url = $url_path_cache. $album_dir.".kml";
	echo '<span class="Style2" style="float:right;"><a href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $kml_url . '" target="_blank" class="Style2">' . OPEN_IN_GOOGLE_MAP . '</a></span>';
	echo "</div>";
	add_map($kml_url);
}
else
{
	echo '</div><div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
}
break;//map

case ('gallery_map'):
	if(!GOOGLEMAP_ACTIVATE) {break;}
	scan_invalid_char(PHOTOS_DIR_ROOT); //scan des répertoires qui contiennent des caractères interdits
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=default<?php echo PRIVATE_PARAM; ?>" class="Style1"><?php echo HOME_NAME ?></a></span>
<?php
	$create_kml_file = (isset($_GET['create']) ? $_GET['create'] : "");
	$kml_gallery_filename = "__gallery.kml";
	$kml_path =  "./" . CACHE_DIR . "/" .$kml_gallery_filename ;
	$placemarks = "";
	if(!file_exists($kml_path) || $create_kml_file="true") {
		$at_least_one = false;
		list($listDir, $listFile) = list_directory(PHOTOS_DIR_ROOT, ALPHABETIC_ORDER,
			array(".", "..", IMAGE_STDDIM, ICO_FILENAME),
			$file_format_managed);
		for($iDir=0;$iDir< count($listDir); $iDir++){
			list($find_one, $placemark) = find_file_with_gps_data(PHOTOS_DIR_ROOT, $listDir[$iDir], $url_path_script, $url_path_cache);
			if($find_one)
			{
				$placemarks .= $placemark ;
				$at_least_one = true;
			}
		}
		if($at_least_one){
			write_kml_file($placemarks,$kml_path);
		}
	}
	if(file_exists($kml_path)) {
		echo '<span class="Style2" style="float:right;"><a href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $kml_url . '" target="_blank" class="Style2">' . OPEN_IN_GOOGLE_MAP . '</a></span></div>';
		add_map($url_path_cache . $kml_gallery_filename);
	}
	else
	{
		echo '</div><div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
	}
break;
}
?>
<div id="popup" class="popup color_light" style="display:none">
	<div class="color_light" style="height:10%">About Facile Gallery</div>
	<div class="color_dark" style="height:80%">
		<span class="Style2"><a class="Style2" href="https://github.com/pmiossec/Facile-Gallery" target="_blank"><b>Facile Gallery (on GitHub)</b></a> by Philippe Miossec<br/>
		Based on the work of :<br/>
		- Gallery : <a class="Style2" href="http://www.atelier-r.net/scripts.php" target="_blank" class="Style2" title="Annuaire cooperatif du graphisme et du multimedia">Php Photo Module / Atelier R</a> (CECILL license) / <a class="Style2" href="http://www.jensen-siu.net/" target="_blank" title="Graphiste - Concepteur multimedia">Jensen SIU</a><br/>
		- Slideshow : <a class="Style2" href="http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/">PrettyPhoto</a> (CC-Attribution license) / <a class="Style2" href="http://www.no-margin-for-errors.com/" target="_blank">Stephane Caron</a><br/>
		</span>
	</div>
	<div class="color_light" align="center">
		<input type="button" value="Close" onClick="javascript:closePopup();" />
	</div>
</div>
<script>
	function myClick() {
	elem = document.getElementById("popup");
	elem.style.display = 'block';
	};
	function closePopup()
	{
	elem = document.getElementById("popup");
	elem.style.display = 'none';
	}
</script></body>
</html>
