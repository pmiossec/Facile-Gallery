<?php 
require("conf.php");

//error_reporting(E_ALL); // afficher les erreurs
error_reporting(0); // ne pas afficher les erreurs

$separateurs = array('_', '-', '.');
$directory = $_SERVER["SCRIPT_NAME"];
$directory = substr($directory, 0, strrpos($directory,"/")+1);
$url_path_script = "http://" . $_SERVER["SERVER_NAME"]. $directory . basename(__FILE__);
$url_path_datas = "http://" . $_SERVER["SERVER_NAME"]. $directory . PHOTOS_DIR ."/";

function list_directory($dir2scan, $order_alphabetically, $exclude_file, $supported_extensions)
{
	// listage des r�pertoires et fichiers
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


function insert_thumbnail_cell($photodir, $thumb_dir, $image_file_name, $index_image, $legend)
{
	$cell_content = '<table>
		<tr class="tddeco">
			<td width="' . (MINIATURE_MAXDIM + SPACE_AROUND_MINIATURE) .'" height="' . (MINIATURE_MAXDIM + SPACE_AROUND_MINIATURE)
			. '" align="center" valign="middle" class="tdover">
				<a class="tooltip" href="' . $_SERVER["PHP_SELF"] .'?show_heading=detail&dir=' . rawurlencode($photodir) .'&photo=' . ($index_image+1) .'">
					<img src="' . $thumb_dir."__".$image_file_name  .'" border="0" alt="' . $image_file_name .'" class="imageborder" />';

					if(strlen($legend) != 0) $cell_content .= my_nl2br("<em><span></span>$legend</em>");
	$cell_content .= '</a>
			</td>
		</tr>
		<tr class="fdgris">
			<td align="center"><span class="Style2">' . wordTruncate(($index_image+1) ."|" . $image_file_name) .'</span></td>
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
		if($isIptcDisplayed) { $metadata_to_display .= '<hr size="1" noshade>'; }
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
			$metadata_to_display .=  "<a target=\"_blank\" href=\"http://maps.google.com/maps?ll=". $decimal_lat."," . $decimal_long."&spn=0.01,0.01&q=". $decimal_lat."," . $decimal_long."&hl=fr\">GPS: ". $decimal_lat."," . $decimal_long."</a>";
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

function display_pages($page_uri,$page_num, $totalPages)
{
	$pages_html = '<div class="fdcolor1" align="center"><span class="Style2">';
	if ($page_num > 1) {
		$pages_html .= "<a href=\"$page_uri&page_num=" . ($page_num-1) .'" class="Style2">&laquo;</a> &nbsp;|&nbsp;';
	}

	for ($l =1; $l < $totalPages; $l++) {
		if ($page_num != $l) {
			$pages_html .= "<a href=\"$page_uri&page_num=" . $l .'" class="Style2">' .$l .'</a> &nbsp;|&nbsp;';
		} else {
			$pages_html .= "<b>$l</b> &nbsp;|&nbsp;";
		}
	}
	if ($page_num < $totalPages) {
		$pages_html .= "<a href=\"$page_uri&page_num=" . ($page_num+1) .'" class="Style2">&raquo;</a>';
	}
	$pages_html .= '</span></div>';
	return $pages_html;
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
	//echo $kml_path;
	$kml_content = '<?xml version= "1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>'
	               . $kml_placemarks . '</Document></kml>';
	//echo $kml_content;
	//Ecrire le fichier
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
		echo '<table border="0" align="center" cellpadding="28" cellspacing="0">
			<tr>
				<td align="center">
				<a href="javascript:history.go(-1)">' . $message . '</a>
				</td>
		</tr>
	</table>';
}
function verify_directories(){
	$photodir = (isset($_GET['dir']) ? $_GET['dir'] : "");
	if (!isset($_GET['dir']) || $_GET['dir'] == "") {//on v�rifie que le r�pertoire photo existe bien
		echo_message_with_history_back(PHOTO_DIR_NEEDED);
		return array (false, '', '');
	}
	//on supprime les slash, antislash et points possibles pour �viter les failles de s�curit�
	$photodir = preg_replace("/\\\\/", "", $photodir);
	$str2clean = array("." => "", "/" => "");
	$photodir = strtr($photodir, $str2clean);
	$dir = PHOTOS_DIR . "/" . $photodir; //chemin vers le r�pertoire qui contient les miniatures
	if (!file_exists($dir)) {//on v�rifie que le r�pertoire photo existe bien
		echo_message_with_history_back(PHOTO_DIR_NOT_EXISTING);
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
		return $label . utf8_decode($exifs[$field1][$field2]);
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
function divide_gps_coordinates($a){
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
	//ancienne methode moins bonne
	//imagecopyresampled(image de destination, image source, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h);
	//imagecopyresampled($newHandle, $handle, 0, 0,((($width - ICO_WIDTH)/2) <= ICO_WIDTH ? ICO_WIDTH-(($width - ICO_WIDTH)/2) : ($width - ICO_WIDTH)/2), ((($height - ICO_HEIGHT)/2) <= 0 ? ICO_HEIGHT-(($height - ICO_HEIGHT)/2) : ($height - ICO_HEIGHT)/2), ICO_WIDTH, ICO_HEIGHT, ICO_WIDTH*2, ICO_HEIGHT*2);
	//imagedestroy($handle);
	//imagejpeg($NewThumb, $dir."/".ICO_FILENAME, GLOBAL_JPG_QUALITY);
	//imagedestroy($newhandle);
}
//////////////////////////////////////////////////////////////////////////
//fonction pour trouver une image ayant des donn�es GPS
//////////////////////////////////////////////////////////////////////////
function find_file_with_gps_data($dir2findgps,$url_path_script, $url_path_datas) {
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

	for($i=0;$i<$cFile;$i++){
		$decimal_lat = 0;
		$decimal_long = 0;
		list($succes, $decimal_lat, $decimal_long) = get_file_metadata_only_gps($dir.'/'.$listFile[$i]);
		if($succes){
				$html_code = "<a href=\"$url_path_script?show_heading=list&dir=$dir2findgps\"><img src=\"$url_path_datas$dir2findgps/". ICO_FILENAME ."\"></a><br/>";
				$kml_file = $kml_file . "<Placemark><name>" . $dir2findgps . "</name><description><![CDATA[";
				$kml_file = $kml_file . $html_code;
				$kml_file = $kml_file . "]]></description><Point><coordinates>" . $decimal_long ."," . $decimal_lat . "</coordinates></Point></Placemark>";
				return array(true, $kml_file);
		}
	}
	return array(false, "");
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
	imagedestroy($image);
	imagejpeg($miniature, $dir.$dir_where2save."/".$file_prefixe.$file2miniaturize, GLOBAL_JPG_QUALITY);
	imagedestroy($miniature);

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
	<meta http-equiv="Content-Type" content="text/html;">
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<link href="global_style.css" rel="stylesheet" type="text/css">
<?php if(GOOGLEMAP_ACTIVATE) { ?>
	<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
	<style type="text/css">
		html { height: 100% }
		body { height: 100%; margin: 0px; padding: 0px }
	 	#map_canvas { height: 100% ; margin-left: auto; margin-right: auto; }
	</style>
<?php } ?>
<SCRIPT>
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
<?php
$activate_slideshow = SLIDESHOW_ACTIVATE; //TODO : ajouter la v�rification de la pr�sence de la lirairie
if($show_heading =="list" && $activate_slideshow){?>
	<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
	<script src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function(){
			$("a[rel^='prettyPhoto']").prettyPhoto();
		});

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
					else{
						echo "fullscreen:false, /* true/false */";
					}
			?>
			});
		});
	</script>
<?php
}?>
</head>
<?php
if(GOOGLEMAP_ACTIVATE && ($show_heading =="map" || $show_heading =="gallery_map")){
	echo '<body onload="initialize()">';
}
else
{
	echo "<body>";
}
ini_set('max_execution_time', 120); //2 mn max
switch ($show_heading) {
///////////////////////////////////////////////////////////////
//listing des r�pertoires photos sur la page d'index par d�faut
///////////////////////////////////////////////////////////////
default:
	scan_invalid_char(PHOTOS_DIR); //scan des r�pertoires qui contiennent des caract�res interdits
	// listage des r�pertoires et fichiers
	list($listDir, $listFile) = list_directory("./".PHOTOS_DIR, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));

	$total_icons = count($listDir);
	$totalPages = ceil($total_icons/ICO_PER_PAGE);
	$page_num = (isset($_GET['page_num']) && $_GET['page_num'] !== "" && $_GET['page_num'] <= $totalPages ? $_GET['page_num'] : "1");
	$pages_html = display_pages($_SERVER["PHP_SELF"] . "?show_heading=default", $page_num, $totalPages);
	?>
	<div class="fdgris"><span class="Style1"><?php echo HOME_NAME ?></span>
	<?php if(GOOGLEMAP_ACTIVATE) { ?><span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=gallery_map" class="Style2"><?php echo DISPLAY_MAP ?></a></span><?php } ?></div>
   <?php echo $pages_html; ?>
	<br>
	<table>
		<tr>
	<?php
	$k=0;
	for ($i = (ICO_PER_PAGE*$page_num) - ICO_PER_PAGE; $i < ($total_icons > (ICO_PER_PAGE*($page_num)) ? ICO_PER_PAGE*$page_num : $total_icons); $i++) {
		if ($listDir[$i] != "." && $listDir[$i] != ".." && $listDir[$i] != THUMBS_DIR && $listDir[$i] != IMAGE_STDDIM && $listDir[$i] != IMAGE_400 && $listDir[$i] != IMAGE_800 && is_dir(PHOTOS_DIR . "/" . $listDir[$i]) == true) {
			//cr�ation du r�pertoire miniatures et images
			$thumb_dir = PHOTOS_DIR . "/" . $listDir[$i] . "/" . THUMBS_DIR . "/";
			$image_dir = PHOTOS_DIR . "/" . $listDir[$i] . "/" . IMAGE_STDDIM . "/";
			if (!file_exists($thumb_dir)) { mkdir($thumb_dir); }
			if (!file_exists($image_dir)) { mkdir($image_dir); }
			//cr�ation de la miniature
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
	<?php (is_int($k/ICO_PER_LINE) ? print "<tr>": print "");  ?>
		<td>
			<table>
				<tr class="tddeco">
					<td width="<?php echo ICO_WIDTH + SPACE_AROUND_MINIATURE; ?>" height="<?php echo ICO_HEIGHT + SPACE_AROUND_MINIATURE; ?>" align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">
						<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $listDir[$i]; ?>"><img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($listDir[$i]) . "/" . ICO_FILENAME ?>" alt="<?php echo str_replace($separateurs, ' ', $listDir[$i]); ?>" width="<?php echo ICO_WIDTH ?>" height="<?php echo ICO_HEIGHT ?>" border="0" class="imageborder"></a></td>
				</tr>
				<tr class="fdgris">
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
	<?php
	echo $pages_html;
	break;//Fin : listing des r�pertoires photos sur la page d'index par d�faut


//////////////////////////////////////////////////////////
//listing des miniatures dans un r�pertoire photo sp�cifi�
//////////////////////////////////////////////////////////
case ('list'):
	list($continue, $photodir, $dir) = verify_directories();
	$image_dir = $dir. "/" . IMAGE_STDDIM ."/";
	$thumb_dir = $dir. "/" . THUMBS_DIR ."/";
	if(!$continue) {break;}
	$page_num = (isset($_GET['page_num']) ? $_GET['page_num'] : "1");//v�rification que le num�ro de page existe bien
	//cr�ation du r�pertoire miniatures et images
	if (!file_exists($thumb_dir)) { mkdir($thumb_dir); }
	if (!file_exists($image_dir)) { mkdir($image_dir); }

	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));
	list($listDirThumb, $listFileThumb) = list_directory($thumb_dir, ALPHABETIC_ORDER, null, null);

	//TODO A reg�rer
	//$page_index = ceil($dir_index/ICO_PER_PAGE);
	
	$total_files = count($listFile);
	for($i=0;$i<$total_files;$i++)
	{
		$file_datas[$i] = array($listFile[$i], get_file_metadata("./$dir/$listFile[$i]"));
	}

	if($activate_slideshow)
	{
		//donn�es du slideshow
		$images='images = [';
		$titles='titles = [' ;
		$descriptions='descriptions = [';
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
				$legend = my_nl2br($legend);
				$descriptions.="'$legend'";
			}
			else
			{
				$descriptions.="''";
			}
		}
		$images.='];';
		$titles.='];';
		$descriptions.='];';

		echo '<script type="text/javascript" charset="utf-8">' , $images , $titles , $descriptions, 'function slideshow(){$.prettyPhoto.open(images,titles,descriptions);}</script>';
	}
	$totalPages =ceil(($total_files)/MINIATURES_PER_PAGE);
	$pages_html = display_pages($_SERVER["PHP_SELF"] . "?show_heading=list&dir=$photodir", $page_num, $totalPages);
	?>
	<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default&page_num=<?php echo $page_index; ?>" class="Style1"><?php echo HOME_NAME; ?></a> &raquo; <?php echo str_replace($separateurs, ' ', $photodir); ?> ( <?php echo (($page_num-1)*MINIATURES_PER_PAGE)+1; ?> -> <?php if ($page_num < ( ceil(($total_files)/MINIATURES_PER_PAGE)) ) { echo (($page_num)*MINIATURES_PER_PAGE); } else { echo $total_files; } ?> / <?php echo $total_files; ?>)</span>
	<?php if(GOOGLEMAP_ACTIVATE) { ?><span class="Style2" style="float:right;"><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=map&dir=<?php echo $photodir; ?>" class="Style2"><?php echo DISPLAY_MAP ?></a></span><?php } if( GOOGLEMAP_ACTIVATE && $activate_slideshow){?><span class="Style2" style="float:right;">&nbsp;&nbsp;|&nbsp;&nbsp;</span><?php } if($activate_slideshow){?><span class="Style2" style="float:right;"><a href="#" onClick="slideshow();return false;" class="Style2"><?php echo SLIDESHOW ?></a></span><?php } ?></div>

	<?php echo $pages_html; ?>
	<br>
	<table class="fdblanc">
		<tr>
	<?php
	//si les r�f�rences correspondent :
	$total_thumbFloor = MINIATURES_PER_PAGE*$page_num;
	$k=0;
	for ($i = $total_thumbFloor - MINIATURES_PER_PAGE; $i < ( ($total_files > $total_thumbFloor) ? $total_thumbFloor : $total_files); $i++) {//oncompte le nb d'�l�ments � afficher selon le num�ro de page
		list($image_file_name, $datas) = $file_datas[$i];
		//list($succes, $exifs, $iptcs, $legend, $tags) = $datas;
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
			echo "<td>" . insert_thumbnail_cell($photodir, $thumb_dir, $image_file_name, $i, $legend) . "</td>";
		$k++;
	}
	?>
	</table><br>
<?php
	echo $pages_html;
	break;//Fin : listing des miniatures dans un r�pertoire photo sp�cifi�


////////////////////
//d�tail de la photo
////////////////////
case ('detail'):
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
	$thumb_dir = $dir. "/" . THUMBS_DIR ."/";
	$photo = (isset($_GET['photo']) ? $_GET['photo'] : "");
	$dim = (isset($_GET['dim']) ? $_GET['dim'] : IMAGE_STDDIM);

	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));
	//
	if (!isset($_GET['photo']) || $_GET['photo'] == "" || !isset($listFile[$photo])) {//on v�rifie que la photo existe bien
		echo_message_with_history_back(NO_PHOTO_TO_DISPLAY);
		break;
	}
	//
	if (!file_exists($dir . "/" . $dim . "/" . $listFile[$photo])) {
		create_newimage($photodir, $listFile[$photo], $dim, $dim, "");
	}
	$total_images = count($listFile);// on compte le nombre d'�l�ments dans le dossier sans compter "." et ".."
	list($width, $height, $type, $attr) = getimagesize($dir . "/" . $dim . "/" . $listFile[$photo]);
	//on cr�� les miniatures si elles sont absentes
	if ($photo > 0)
	{
		create_miniature($photodir, $listFile[$photo-1]);
	}
	if ($photo < $total_images-1)
	{
		create_miniature($photodir, $listFile[$photo+1]);
	}
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir ?>&page_num=<?php echo ceil($photo/MINIATURES_PER_PAGE); ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $photodir); ?></a> &raquo; <?php echo $listFile[$photo]; ?> n&deg;<?php echo ($photo+1); ?> / <?php echo $total_images; ?></span></div>
<br>
<table  class="fdblanc">
	<tr>
		<td>
		<?php if ($photo > 0) { echo insert_thumbnail_cell($photodir, $thumb_dir, $listFile[$photo-1], $photo-1, ""); }?>
	</td>
	<td>
		<table  class="fdgris">
			<tr class="tddeco">
				<td align="center" valign="middle" class="tdover" onmouseover="this.style.borderColor='#666666'" onmouseout="this.style.borderColor='#FFFFFF'">
			<?php if ($photo >= 0 && $photo < $total_images) { ?>
						<a href="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $listFile[$photo]; ?>">
							<img src="<?php echo PHOTOS_DIR . "/" . rawurlencode($photodir) . "/" . $dim . "/" . $listFile[$photo]; ?>" alt="<?php echo $listFile[$photo]; ?>" <?php echo $attr; ?> border="0" class="imageborder">
						</a><?php
					} else { echo_message_with_history_back( NO_PHOTO_TO_DISPLAY ); } ?>
				</td>
			</tr>
			<tr>
				<td align="center">
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
	<?php if ($photo < $total_images -1) {echo insert_thumbnail_cell($photodir, $thumb_dir, $listFile[$photo+1], $photo+1, ""); }?>
		</td>
	</tr>
</table>

<?php
break;
case ('map'):
	if(!GOOGLEMAP_ACTIVATE) {break;}
	list($continue, $photodir, $dir) = verify_directories();
	if(!$continue) {break;}
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a> &raquo; <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=list&dir=<?php echo $photodir ?>" class="Style1"><?php echo str_replace($separateurs, ' ', $photodir); ?></a></span>
<?php
	$photo = (isset($_GET['photo']) ? $_GET['photo'] : "");
	$create_kml_file = (isset($_GET['create']) ? $_GET['create'] : "");
	$dim = (isset($_GET['dim']) ? $_GET['dim'] : IMAGE_STDDIM);

	list($listDir, $listFile) = list_directory($dir, ALPHABETIC_ORDER,
			array(".", "..", THUMBS_DIR , IMAGE_STDDIM, ICO_FILENAME),
			array("jpeg", "jpg", "gif", "png"));

	$kml_path =  "./" . PHOTOS_DIR . "/" . $photodir. ".kml";
	//echo $kml_path ;
	if(!file_exists($kml_path) || $create_kml_file="true") {
	//if(true){
	//Creer le fichier .kml
	$at_least_one = false;
	for ($i=0;$i < count($listFile); $i++) {
	
		$file_to_add = $listFile[$i];
		list($succes,$exifs, $iptcs, $legend, $tags, $decimal_lat, $decimal_long) = get_file_metadata_only_if_gps_exists($dir.'/'.$listFile[$i]);
		if($succes)
		{
			$name= $file_to_add;
			if($iptcs != null)
			{
				$html_code = "<a href=\"$url_path_datas$photodir/" . $file_to_add ."\"><img src=\"$url_path_datas$photodir/". THUMBS_DIR . "/__$file_to_add\"></a><br/>";
				$html_code = $html_code . "<span class=\"legend\">";
				$html_code = $html_code . my_nl2br($legend);
				$html_code = $html_code . "</span><br/>\n";
				$html_code = $html_code . $tags."<br/>\n";
			}
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
	echo '<span class="Style2" style="float:right;"><a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . $kml_url . '" target="_blank" class="Style2">' . OPEN_IN_GOOGLE_MAP . '</a></span>';
	echo "</div>";
	add_map($kml_url);
}
else
{
	echo '</div><div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
}
break;
case ('gallery_map'):
	if(!GOOGLEMAP_ACTIVATE) {break;}
	scan_invalid_char(PHOTOS_DIR); //scan des r�pertoires qui contiennent des caract�res interdits
?>
<div class="fdgris"><span class="Style1">// <a href="<?php echo $_SERVER["PHP_SELF"]; ?>?show_heading=default" class="Style1"><?php echo HOME_NAME ?></a></span>
<?php
	// listage des r�pertoires et fichiers
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
	//	echo $kml_url ;
		echo '<span class="Style2" style="float:right;"><a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . $kml_url . '" target="_blank" class="Style2">' . OPEN_IN_GOOGLE_MAP . '</a></span>';
		echo "</div>";
		add_map($kml_url);
	}
	else
	{
		echo '</div><div style="text-align:center; margin: auto; height: 50px;">' . NO_PHOTO_WITH_GPS_DATA .'</div>';
	}
break;
//fin du switch
}
if(DISPLAY_FOOTER)
	echo '<div class="fdgris" align="right"><span class="Style2">Php Photo Module 0.3.0 | auteur : <a href="http://www.jensen-siu.net" target="_blank" class="Style2" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" class="Style2" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a></span></div>';
?><noscript>
<!-- Si vous retirez la r�f�rence ci dessus pour des raisons esth�tiques, je vous remercie de laisser celle-ci que personne ne verra. Merci. -->
Php Photo Module 0.3.0 | auteur : Philippe Miossec | auteur original : <a href="http://www.jensen-siu.net" target="_blank" title="Graphiste - Concepteur multimedia">Jensen SIU</a> | distribution sur : <a href="http://www.atelier-r.net" target="_blank" title="Annuaire cooperatif du graphisme et du multimedia">Atelier R</a>
</noscript>
</body>
</html>
