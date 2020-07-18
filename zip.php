<?php
/*$files =  array('./photos/Corbeille/img000.jpeg','./photos/2010-08-26_Mariage_Thomas_Gaelle/2010-08-28_10-12-31.JPG');
download_zip($files);
*/
function download_zip($files,$zip_file = null,$custom_file_string = null){
	if($zip_file == null || length($zip_file) == 0)
	{
		$zip_file = "photos_";
		if($zip_file == null || length($zip_file) == 0)
			$zip_file .= $custom_file_string ."_";
		$zip_file .= date("Y-m-d_G-i-s").".zip";
	}
	$createZip = new createZip;
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="'.basename($zip_file).'"');
	header('Content-Transfer-Encoding: binary');
	foreach ($files as $file_path) {
		echo $createZip->addFile($file_path, basename($file_path));
	}
	echo $createZip->EndOfFile();
}


class createZip {
	public $compressedData = array();
	public $centralDirectory = array(); // central directory
	public $endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory recordvar $oldOffset = 0;
	var $newOffset = 0;
	var $directories__ = array();
	
	function crc32_file($file) {
		$crc32__ = unpack('N', hash_file('crc32b', $file, TRUE));
		return sprintf('%u', $crc32__[1]);
	}

	public function addFile($src_file, $file) {
		$crc32 = $this->crc32_file($src_file);
		$src_file = str_replace("\\", "/", $src_file);
		$file = str_replace("\\", "/", $file);
		if (!file_exists($src_file) || !is_readable($src_file)) {
			return FALSE;
		}
		$path = dirname($file);
		if (!isset($this->directories__[$path])) {
			$dir_data = $this->addDirectory($path);
			$this->directories__[$path] = TRUE;
		} else {
			$dir_data = '';
		}
		$date = getdate();
		$date = (($date['year'] - 1980) << 25) | ($date['mon'] << 21) | ($date['mday'] << 16) | ($date['hours'] << 11) | ($date['minutes'] << 5) | ($date['seconds'] >> 1);
		$crc32 = $this->ucrc32($crc32);
		$length = filesize($src_file);
		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x14\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= pack("V", $date);
		$feedArrayRow .= $crc32;
		$feedArrayRow .= pack("V", $length);
		$feedArrayRow .= pack("V", $length);
		$feedArrayRow .= pack("v", strlen($file));
		$feedArrayRow .= pack("v", 0);
		$feedArrayRow .= $file;
		$feedArrayRow .= file_get_contents($src_file);
		$feedArrayRow .= $crc32;
		$feedArrayRow .= pack("V", $length);
		$feedArrayRow .= pack("V", $length);
		$this->newOffset += strlen($feedArrayRow);
		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x14\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= pack("V", $date);
		$addCentralRecord .= $crc32;
		$addCentralRecord .= pack("V", $length);
		$addCentralRecord .= pack("V", $length);
		$addCentralRecord .= pack("v", strlen($file));
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("V", 32);
		$addCentralRecord .= pack("V", $this->oldOffset);
		$addCentralRecord .= $file;
		$this->oldOffset = $this->newOffset;
		$this->centralDirectory[] = $addCentralRecord;
		return $dir_data.$feedArrayRow;
	}

	public function addDirectory($directoryName) {
		$directoryName = str_replace("\\", "/", $directoryName);
		$date = getdate();
		$date = (($date['year'] - 1980) << 25) | ($date['mon'] << 21) | ($date['mday'] << 16) | ($date['hours'] << 11) | ($date['minutes'] << 5) | ($date['seconds'] >> 1);$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x0a\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= pack("V", $date);
		$feedArrayRow .= pack("V", 0);
		$feedArrayRow .= pack("V", 0);
		$feedArrayRow .= pack("V", 0);
		$feedArrayRow .= pack("v", strlen($directoryName));
		$feedArrayRow .= pack("v", 0);
		$feedArrayRow .= $directoryName;
		$feedArrayRow .= pack("V", 0);
		$feedArrayRow .= pack("V", 0);
		$feedArrayRow .= pack("V", 0);
		$this->newOffset += strlen($feedArrayRow);$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x0a\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= pack("V", $date);
		$addCentralRecord .= pack("V", 0);
		$addCentralRecord .= pack("V", 0);
		$addCentralRecord .= pack("V", 0);
		$addCentralRecord .= pack("v", strlen($directoryName));
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("v", 0);
		$addCentralRecord .= pack("V", 16);
		$addCentralRecord .= pack("V", $this->oldOffset);
		$addCentralRecord .= $directoryName;
		$this->oldOffset = $this->newOffset;
		$this->centralDirectory[] = $addCentralRecord;
		return $feedArrayRow;
	}
	
	public function EndOfFile() {
		$controlDirectory = implode('', $this->centralDirectory);
		return	$controlDirectory.$this->endOfCentralDirectory.
		pack('v', sizeof($this->centralDirectory)).
		pack('v', sizeof($this->centralDirectory)).
		pack('V', strlen($controlDirectory)).
		pack('V', $this->oldOffset).
		"\x00\x00";
		}
		
	function ucrc32($crc32) {
		$hex = str_pad(base_convert($crc32, 10, 16), 8, '0', STR_PAD_LEFT);
		return (chr(hexdec($hex{6}.$hex{7})).
			chr(hexdec($hex{4}.$hex{5})).
			chr(hexdec($hex{2}.$hex{3})).
			chr(hexdec($hex{0}.$hex{1})));
		}
}
?>