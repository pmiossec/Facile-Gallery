<?php 
require("conf.php");

//error_reporting(E_ALL); // afficher les erreurs
error_reporting(0); // ne pas afficher les erreurs

$separateurs = array('_', '-', '.');
$directory = $_SERVER["SCRIPT_NAME"];
$directory = substr($directory, 0, strrpos($directory,"/")+1);
$url_path_script = "http://" . $_SERVER["SERVER_NAME"]. $directory . basename(__FILE__);
$url_path_datas = "http://" . $_SERVER["SERVER_NAME"]. $directory . PHOTOS_DIR ."/";

$here = (isset($_GET['here']) ? $_GET['here'] : "");
$gallery_page_num = (isset($_GET['gallery_page_num']) ? $_GET['gallery_page_num'] : "1");//vérification que le numéro de page existe bien
$thumb_page_num = (isset($_GET['thumb_page_num']) ? $_GET['thumb_page_num'] : "1");//vérification que le numéro de page existe bien

function construct_header($level, $photodir, $total_images, $photo_name, $index_photo_min, $index_photo_max)
{
	//HOME
	$header = '<span class="Style1"><a class="Style1 tooltip" href="#">©<em style="width:230px"><span></span>No copyright, just copyleft :)<br/>
Based on (modified) :<br>- Php Photo Module (CeCILL) / Jensen SIU
<br>- PrettyPhoto (CC-Attribution) / Stephane Caron </em></a>&nbsp;// ';
	$gallery_page_num = (isset($_GET['gallery_page_num']) ? $_GET['gallery_page_num'] : "1");//vérification que le numéro de page existe bien
	;
	if($level!=0)
	{
		$header .= '<a class="Style1" href="'. $_SERVER["PHP_SELF"] .'?here=default&amp;gallery_page_num=' . $gallery_page_num . '">';
	}
	$header .= HOME_NAME;
	//Directory
	if($level!=0)
	{
		$header .= '</a>';
		$thumb_page_num = (isset($_GET['thumb_page_num']) ? $_GET['thumb_page_num'] : "1");//vérification que le numéro de page existe bien
		$header .= '&raquo; ';
		if($level == 2)
		{
			$header .= '<a class="Style1" href="' . $_SERVER["PHP_SELF"] . '?here=list&amp;dir='.$photodir.'&amp;gallery_page_num=' . $gallery_page_num . '&amp;thumb_page_num='.$thumb_page_num .'">';
		}
		$header .= str_replace($separateurs, ' ', $photodir);
		if($level == 2)
		{
			$image_num = (isset($_GET['image_num']) ? $_GET['image_num'] : "1");//vérification que le numéro de page existe bien
			$header .= '</a> &raquo; ' . $photo_name . ' n&deg;'. $image_num .' / ' . $total_images;
		}
		if($level == 1)
		{
			$header .= ' ('. $index_photo_min .' -> ' . $index_photo_max . ' / ' . $total_images . ')';
		}
	}
	$header .= '</span>';
	return $header;
}

function list_directory($dir2scan, $order_alphabetically, $exclude_file, $supported_extensions)
{
	// listage des répertoires et fichiers
	if ($handle = opendir($dir2scan)) {
		$cDir = 0;
		$cFile = 0;
		while (false !== ($file = readdir($handle))) {
		if(!in_array($file,$exclude_file)){
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
		usort($listDir,"strnatcmp");
		usort($listFile,"strnatcmp");
	}
	return array($listDir,$listFile);
}


function insert_thumbnail_cell($photodir, $thumb_dir, $image_file_name, $index_image, $legend, $gallery_page_num , $thumb_page_num)
{
	$cell_content = '<table>
		<tr>
			<td width="' . (MINIATURE_MAXDIM + SPACE_AROUND_MINIATURE) .'" height="' . (MINIATURE_MAXDIM + SPACE_AROUND_MINIATURE)
			. '" align="center" valign="middle">
				<a class="tooltip" href="' . $_SERVER["PHP_SELF"] .'?here=detail&amp;gallery_page_num='.$gallery_page_num.'&amp;thumb_page_num='.$thumb_page_num.'&amp;dir=' . rawurlencode($photodir) .'&amp;image_num=' . ($index_image+1) .'">
					<img src="' . $thumb_dir."__".$image_file_name  .'" alt="' . $image_file_name .'" class="imageborder" />';

					if(strlen($legend) != 0) $cell_content .= my_nl2br("<em style=\"width:300px\"><span></span>$legend</em>");
	$cell_content .= '</a>
			</td>
		</tr>
		<tr>
			<td class="fdgris"><span class="Style2">' . wordTruncate(($index_image+1) ."|" . $image_file_name) .'</span></td>
		</tr>
	</table>';
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

function create_miniature($photodir, $filename)
{
	if(!file_exists(PHOTOS_DIR . "/" . $photodir . "/" . THUMBS_DIR . "/__" . $filename)) {
		create_newimage($photodir, $filename, MINIATURE_MAXDIM, THUMBS_DIR, "__");
	}
	else
		{
			list($width, $height, $type, $attr) = getimagesize("__" . $filename);
			if($width != MINIATURE_MAXDIM && $height != MINIATURE_MAXDIM)
			{
				create_newimage($photodir, $filename, MINIATURE_MAXDIM, THUMBS_DIR, "__");
			}
		}
}

function display_pages_indexes($page_uri,$page_num, $totalPages)
{
	$pages_indexes = '<div class="fdcolor1" align="center"><span class="Style2">';
	if($totalPages == 1)
		return $pages_indexes . "</span></div>";

	if ($page_num > 1) {
		$pages_indexes .= "<a href=\"$page_uri" . ($page_num-1) .'" class="Style2">&laquo;</a> &nbsp;|&nbsp;';
	}

	for ($l =1; $l <= $totalPages; $l++) {
		if($l > 1) $pages_indexes .= " &nbsp;|&nbsp;";
		if ($page_num != $l) {
			$pages_indexes .= "<a href=\"$page_uri" . $l .'" class="Style2">' .$l .'</a>';
		} else {
			$pages_indexes .= "<b>$l</b>";
		}
	}
	if ($page_num < $totalPages) {
		$pages_indexes .= " &nbsp;|&nbsp;<a href=\"$page_uri" . ($page_num+1) .'" class="Style2">&raquo;</a>';
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
	echo '<div id="map_canvas" style="width:95%; height:95%"></div><br/>
	<script type="text/javascript">
	//DOC : http://www.touraineverte.com/aide-documentation-exemple-tutoriel-didacticiel/api-google-maps/kml-kmz/creer-creation-carte-map-mes-cartes/utiliser-fichier-kml-generer-creer-google-earth/importer-carte-via-api-google-maps-new-GGeoXml.htm
	function initialize() {
		var myLatlng = new google.maps.LatLng(41.875696,-87.624207);
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
	$photodir = (isset($_GET['dir']) ? $_GET['dir'] : "");
	if (!isset($_GET['dir']) || $_GET['dir'] == "") {//on vérifie que le répertoire photo existe bien
		echo_message_with_history_back(PHOTO_DIR_NEEDED);
		return array (false, '', '');
	}
	//on supprime les slash, antislash et points possibles pour éviter les failles de sécurité
	$photodir = preg_replace("/\\\\/", "", $photodir);
	$str2clean = array("." => "", "/" => "");
	$photodir = strtr($photodir, $str2clean);
	$dir = PHOTOS_DIR . "/" . $photodir; //chemin vers le répertoire qui contient les miniatures
	if (!file_exists($dir)) {//on vérifie que le répertoire photo existe bien
		echo_message_with_history_back(PHOTO_DIR_NOT_EXISTING);
		return array (false, '', '');
	}
	return array (true, $photodir, $dir);
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

///fonction pour créer une miniature de la 1ère image du sous dossier photo
function create_icon($dir2iconize) {
	$dir = PHOTOS_DIR."/".$dir2iconize; //chemin vers le répertoire dont on doit créer l'icone
	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", ".."),
			array("jpeg", "jpg", "gif", "png"));

	//$extract = scandir($dir);//scan des "array" du répertoire
	$first_dir_item = $listFile[0]; // on extrait la valeur du premier fichier du répertoire (après "." et "..")

	list($srcWidth, $srcHeight, $type, $attr) = getimagesize($dir."/".$first_dir_item);//on liste les valeur de l'image
	//$miniature = imagecreatetruecolor(ICO_WIDTH, ICO_HEIGHT);
	if ($type == 1) {
		$handle = imagecreatefromgif($dir."/".$first_dir_item);
	}
	if ($type == 2) {
		$handle = imagecreatefromjpeg($dir."/".$first_dir_item);
	}
	if ($type == 3) {
		$handle = imagecreatefrompng($dir."/".$first_dir_item);
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

		imagejpeg($newHandle, $dir."/".ICO_FILENAME, GLOBAL_JPG_QUALITY);
		imagedestroy($newHandle);
	} else {
		imagejpeg($handle, $dir."/".ICO_FILENAME, GLOBAL_JPG_QUALITY);
		imagedestroy($handle);
	}
}

///fonction pour trouver une image ayant des données GPS
function find_file_with_gps_data($dir2findgps,$url_path_script, $url_path_datas) {
	$dir = PHOTOS_DIR."/".$dir2findgps; //chemin vers le répertoire dont on doit créer l'icone
	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", ".."), array("jpeg", "jpg", "gif", "png"));

	for($i=0;$i<$cFile;$i++){
		$decimal_lat = 0;
		$decimal_long = 0;
		list($succes, $decimal_lat, $decimal_long) = get_file_metadata_only_gps($dir.'/'.$listFile[$i]);
		if($succes){
			$html_code = "<a href=\"$url_path_script?here=list&amp;dir=$dir2findgps\"><img src=\"$url_path_datas$dir2findgps/". ICO_FILENAME ."\"></a><br/>
				<Placemark><name>$dir2findgps</name><description><![CDATA[$html_code]]></description><Point><coordinates>" . $decimal_long ."," . $decimal_lat . "</coordinates></Point></Placemark>";
			return array(true, $kml_file);
		}
	}
	return array(false, "");
}

///fonction pour créer toutes les miniatures du répertoire en question
function create_newimage($dirname, $file2miniaturize, $dimensionmax, $dir_where2save, $file_prefixe) {
	$dir = PHOTOS_DIR."/".$dirname; //chemin vers le répertoire à dont on doit créer l'icone
	$dir_where2save = ($dir_where2save ? "/".$dir_where2save : "");
	$file_prefixe = ($file_prefixe ? $file_prefixe : "");
	$pathFile2miniaturize = $dir."/".$file2miniaturize;
	list($width, $height, $type, $attr) = getimagesize($pathFile2miniaturize);//on liste les valeur de l'image
	if ($width >= $height) {
		$newwidth = $dimensionmax;
		$newheight = ($dimensionmax*$height)/$width;
	} else {
		$newwidth = ($dimensionmax*$width)/$height;
		$newheight = $dimensionmax;
	}
	$miniature = imagecreatetruecolor($newwidth, $newheight);
	if ($type == 1) {
		$image = imagecreatefromgif($pathFile2miniaturize);
	}
	if ($type == 2) {
		$image = imagecreatefromjpeg($pathFile2miniaturize);
	}
	if ($type == 3) {
		$image = imagecreatefrompng($pathFile2miniaturize);
	}
	imagecopyresampled($miniature, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagedestroy($image);
	imagejpeg($miniature, $dir.$dir_where2save."/".$file_prefixe.$file2miniaturize, GLOBAL_JPG_QUALITY);
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
	<meta http-equiv="Content-Type" content="text/html;charset=windows-1252">
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<link href="global_style.css" rel="stylesheet" type="text/css">
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
	scan_invalid_char(PHOTOS_DIR); //scan des répertoires qui contiennent des caractères interdits
	list($listDir, $listFile) = list_directory("./".PHOTOS_DIR, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME, IMAGE_400, IMAGE_800),
			array("jpeg", "jpg", "gif", "png"));

	$total_icons = count($listDir);
	$totalPages = ceil($total_icons/ICO_PER_PAGE);
	$page_num = (isset($_GET['gallery_page_num']) && $_GET['gallery_page_num'] !== "" && $_GET['gallery_page_num'] <= $totalPages ? $_GET['gallery_page_num'] : "1");
	$pages_html_indexes = display_pages_indexes($_SERVER["PHP_SELF"] . "?here=default&amp;gallery_page_num=", $page_num, $totalPages);
	echo '<div class="fdgris">' . construct_header(O,PHOTOS_DIR, $total_icons, null, null, null);
?>
	<?php if(GOOGLEMAP_ACTIVATE) { ?><span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=gallery_map" class="Style2"><?php echo DISPLAY_MAP ?></a></span><?php } ?></div>
   <?php echo $pages_html_indexes; ?>
	<br>
	<table>
	<?php
	$k=0;
	for ($i = (ICO_PER_PAGE*$page_num) - ICO_PER_PAGE; $i < ($total_icons > (ICO_PER_PAGE*($page_num)) ? ICO_PER_PAGE*$page_num : $total_icons); $i++) {
		//création du répertoire miniatures et images
		$thumb_dir = PHOTOS_DIR . "/" . $listDir[$i] . "/" . THUMBS_DIR . "/";
		$image_dir = PHOTOS_DIR . "/" . $listDir[$i] . "/" . IMAGE_STDDIM . "/";
		if (!file_exists($thumb_dir)) { mkdir($thumb_dir); }
		if (!file_exists($image_dir)) { mkdir($image_dir); }
		$tooltip_filepath = PHOTOS_DIR . "/" . $listDir[$i] . "/" . FOLDER_INFO_FILENAME;
		$legend = null;
		if (file_exists($tooltip_filepath)) {
			$fh = fopen($tooltip_filepath, 'r');
			$legend = fread($fh, filesize($tooltip_filepath));
			fclose($fh);
		}
		//création de la miniature
		if (!file_exists(PHOTOS_DIR . "/" . $listDir[$i] . "/" . ICO_FILENAME)) {
			create_icon($listDir[$i]);
		}
		else
		{
			list($width, $height, $type, $attr) = getimagesize(PHOTOS_DIR . "/" . $listDir[$i]  . "/" . ICO_FILENAME);//on liste les valeurs de la miniature
			if ($width != ICO_WIDTH || $height != ICO_HEIGHT) {
				create_icon($listDir[$i]);
			}
		}
		?>
	<?php print is_int($k/ICO_PER_LINE) ? "<tr>": "";  ?>
		<td>
			<table>
				<tr>
					<td>
						<a class="tooltip" href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=list&amp;gallery_page_num=<?php echo $page_num; ?>&amp;dir=<?php echo $listDir[$i]; ?>"><img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($listDir[$i]) . "/" . ICO_FILENAME ?>" alt="<?php echo str_replace($separateurs, ' ', $listDir[$i]); ?>" class="imageborder"><?php if(strlen($legend) != 0) echo my_nl2br("<em style=\"width:250px\"><span></span>$legend</em>");?></a>
					</td>
				</tr>
				<tr>
					<td class="fdgris"><span class="Style2"><?php
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
		print is_int(($k+1)/ICO_PER_LINE) ? "</tr>": "";
		$k++;
	}
	?>
	</tr>
	</table><br>
	<?php
	echo $pages_html_indexes;
	break;//default

//listing des miniatures dans un répertoire photo spécifié
case ('list'):
	list($continue, $photodir, $dir) = verify_directories();
	$image_dir = $dir. "/" . IMAGE_STDDIM ."/";
	$thumb_dir = $dir. "/" . THUMBS_DIR ."/";
	if(!$continue) {break;}
	//création du répertoire miniatures et images
	if (!file_exists($thumb_dir)) { mkdir($thumb_dir); }
	if (!file_exists($image_dir)) { mkdir($image_dir); }

	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));
	list($listDirThumb, $listFileThumb) = list_directory($thumb_dir, ALPHABETIC_ORDER, null, null);

	$total_files = count($listFile);
	for($i=0;$i<$total_files;$i++)
	{
		$file_datas[$i] = array($listFile[$i], get_file_metadata("./$dir/$listFile[$i]"));
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
			$images .= "'./$dir/$image_file_name'";
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
	$totalPages =ceil(($total_files)/MINIATURES_PER_PAGE);
	$pages_html_indexes = display_pages_indexes($_SERVER["PHP_SELF"] . "?here=list&amp;dir=$photodir&amp;gallery_page_num=$gallery_page_num&amp;thumb_page_num=", $thumb_page_num, $totalPages);
	$index_photo_min = (($thumb_page_num-1)*MINIATURES_PER_PAGE)+1;
	if ($thumb_page_num < ( ceil(($total_files)/MINIATURES_PER_PAGE)) )
	{ $index_photo_max = (($thumb_page_num)*MINIATURES_PER_PAGE); } else { $index_photo_max = $total_files; }

	echo '<div class="fdgris">'. construct_header(1, $photodir, $total_files, null , $index_photo_min, $index_photo_max);
	?>
	<?php if(GOOGLEMAP_ACTIVATE) { ?><span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=map&amp;dir=<?php echo $photodir; ?>" class="Style2"><?php echo DISPLAY_MAP ?></a></span><?php }
			if( GOOGLEMAP_ACTIVATE && $activate_slideshow){?><span class="Style2" style="float:right;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><?php }
			if($activate_slideshow){?><span class="Style2" style="float:right;"><a href="#" onClick="slideshow();return false;" class="Style2"><?php echo SLIDESHOW ?></a></span><?php } ?></div>

	<?php echo $pages_html_indexes; ?>
	<table>
		<tr>
	<?php
	//si les références correspondent :
	$total_thumbFloor = MINIATURES_PER_PAGE*$thumb_page_num;
	$k=0;
	for ($i = $total_thumbFloor - MINIATURES_PER_PAGE; $i < ( ($total_files > $total_thumbFloor) ? $total_thumbFloor : $total_files); $i++) {//oncompte le nb d'éléments à afficher selon le numéro de page
		list($image_file_name, $datas) = $file_datas[$i];
		$legend = get_file_metadata_to_display('./' .$dir.'/'.$image_file_name, $exif_to_display, $iptc_to_display, false);
		if(!in_array("__".$image_file_name, $listFileThumb))
		{
			create_newimage($photodir, $image_file_name, MINIATURE_MAXDIM, THUMBS_DIR, "__");
		}
		else
		{
			list($width, $height, $type, $attr) = getimagesize(PHOTOS_DIR . "/" . $photodir . "/" . THUMBS_DIR . "/__" .$image_file_name);
			if($width != MINIATURE_MAXDIM && $height != MINIATURE_MAXDIM)
			{
				create_newimage($photodir, $image_file_name, MINIATURE_MAXDIM, THUMBS_DIR, "__");
			}
		}
		?>
		<?php (is_int($k/MINIATURES_PER_LINE) ? print "<tr>": print "");
			echo "<td>" . insert_thumbnail_cell($photodir, $thumb_dir, $image_file_name, $i, $legend, $gallery_page_num , $thumb_page_num) . "</td>";
		$k++;
	}
	?>
	</table><br>
<?php
	echo $pages_html_indexes;
	break;//list

//détail de la photo
case ('detail'):
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
	$thumb_dir = $dir. "/" . THUMBS_DIR ."/";
	$photo = (isset($_GET['image_num']) ? $_GET['image_num'] : "");
	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));

	$dim = IMAGE_STDDIM;

	if ($photo == "" || !isset($listFile[$photo-1])) {//on vérifie que la photo existe bien
		echo_message_with_history_back(NO_PHOTO_TO_DISPLAY);
		break;
	}
	$photo = $photo -1;
	//
	if (!file_exists($dir . "/" . $dim . "/" . $listFile[$photo])) {
		create_newimage($photodir, $listFile[$photo], $dim, $dim, "");
	}
	$total_images = count($listFile);// on compte le nombre d'éléments dans le dossier sans compter "." et ".."
	list($width, $height, $type, $attr) = getimagesize($dir . "/" . $dim . "/" . $listFile[$photo]);
	//on créé les miniatures si elles sont absentes
	if ($photo > 0)
	{
		create_miniature($photodir, $listFile[$photo-1]);
	}
	if ($photo < $total_images-1)
	{
		create_miniature($photodir, $listFile[$photo+1]);
	}
	echo '<div class="fdgris">'.construct_header(2, $photodir, $total_images, $listFile[$photo], null, null) . '</div>';
?>
<table>
	<tr>
		<td>
		<?php if ($photo > 0) { echo insert_thumbnail_cell($photodir, $thumb_dir, $listFile[$photo-1], $photo-1, "", $gallery_page_num , $thumb_page_num); }?>
	</td>
	<td>
		<table>
			<tr>
				<td align="center" valign="middle">
			<?php if ($photo >= 0 && $photo < $total_images) { ?>
						<a href="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $listFile[$photo]; ?>">
							<img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $dim . "/" . $listFile[$photo]; ?>" alt="<?php echo $listFile[$photo]; ?>" <?php echo $attr; ?> class="imageborder">
						</a><?php
					} else { echo_message_with_history_back( NO_PHOTO_TO_DISPLAY ); } ?>
				</td>
			</tr>
			<tr>
				<td class="fdgris">
					<span class="Style2">
					<?php
					echo my_nl2br(get_file_metadata_to_display($dir.'/'.$listFile[$photo],$exif_to_display, $iptc_to_display, true));
					?>
					</span>
				</td>
			</tr>
		</table>
	</td>
	<td>
	<?php if ($photo < $total_images -1) {echo insert_thumbnail_cell($photodir, $thumb_dir, $listFile[$photo+1], $photo+1, "", $gallery_page_num , $thumb_page_num); }?>
		</td>
	</tr>
</table>
<?php
break;//detail

case ('map'):
	if(!GOOGLEMAP_ACTIVATE) {break;}
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=default" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=list&amp;dir=<?php echo $photodir ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $photodir); ?></a></span>
<?php
	$photo = (isset($_GET['photo']) ? $_GET['photo'] : "");
	$create_kml_file = (isset($_GET['create']) ? $_GET['create'] : "");
	$dim = IMAGE_STDDIM;

	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));

	$kml_path =  "./" . PHOTOS_DIR . "/" . $photodir. ".kml";
	if(!file_exists($kml_path) || $create_kml_file="true") {
	$at_least_one = false;
	for ($i=0;$i < count($listFile); $i++) {
	
		$file_to_add = $listFile[$i];
		list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_only_if_gps_exists($dir.'/'.$listFile[$i]);
		if($succes)
		{
			$name= $file_to_add;
			if($iptcs != null)
			{
				$html_code = "<a href=\"$url_path_datas$photodir/$file_to_add\"><img src=\"$url_path_datas$photodir/". THUMBS_DIR . "/__$file_to_add\"></a><br/>
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
	$kml_url = $url_path_datas. $photodir.".kml";
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
	scan_invalid_char(PHOTOS_DIR); //scan des répertoires qui contiennent des caractères interdits
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?here=default" class="Style1"><?php echo HOME_NAME ?></a></span>
<?php
	// listage des répertoires et fichiers
	$create_kml_file = (isset($_GET['create']) ? $_GET['create'] : "");

	list($listDir, $listFile) = list_directory(PHOTOS_DIR, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));

	$kml_gallery_filename = "gallery.kml";
	$kml_path =  "./" . PHOTOS_DIR . "/" .$kml_gallery_filename ;
	$placemarks = "";
	if(!file_exists($kml_path) || $create_kml_file="true") {
		$at_least_one = false;
		for($iDir=0;$iDir< count($listDir); $iDir++){
			list($find_one, $placemark) = find_file_with_gps_data($listDir[$iDir], $url_path_script, $url_path_datas);
			if($find_one)
			{
				$placemarks = $placemarks .  $placemark ;
				$at_least_one = true;
			}
		}
		if($at_least_one){
			write_kml_file($placemarks,$kml_path);
		}
	}
	if(file_exists($kml_path)) {
		$kml_url = $url_path_datas . $kml_gallery_filename;
		echo '<span class="Style2" style="float:right;"><a href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $kml_url . '" target="_blank" class="Style2">' . OPEN_IN_GOOGLE_MAP . '</a></span></div>';
		add_map($kml_url);
	}
	else
	{
		echo '</div><div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
	}
break;
}
if(DISPLAY_FOOTER)
	echo '<div class="fdgris"><span class="Style2">Php Photo Module 0.3.0 | auteur : <a href="http://www.jensen-siu.net" target="_blank" class="Style2" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" class="Style2" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a></span>
<span class="Style2" style="float:right;">Slideshow by "<a class="Style2" href="http://www.no-margin-for-errors.com" target="_blank">PrettyPhoto</a>" by Stephane Caron</span></div>';
?><noscript>
<!-- Si vous retirez la reference ci dessus pour des raisons esthetiques, je vous remercie de laisser celle-ci que personne ne verra. Merci. -->
Based on :
- Php script by "Php Photo Module" 0.2.3 by <a href="http://www.jensen-siu.net" target="_blank" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a>
- Slideshow by "<a href="http://www.no-margin-for-errors.com" target="_blank">PrettyPhoto</a>" by Stephane Caron
</noscript>
</body>
</html>
