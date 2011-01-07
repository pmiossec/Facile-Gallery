<?php
/// Facile Gallery
// => This file is used for documentation, configuration and translation
/// I. Summary
//This php file's goal is to produce an easy to use image gallery with only copying images on the server (and no database)

/// II. Main Features
// Gallery (GIF, JPG, PNG) with sub galleries
// Slideshow (full screen)
// Exif/IPTC displaying
// GPS Datas displaying (within google map)
// Multiple private galleries (with login access)
// Adapt easily the gallery to your colors
// Possibility of visitors comments (using disqus : http://www.disqus.com)
// Possibility of download photos zip file

///III. Installation / Configuration
// 1. Edit the "index.php" file and modify the 2ond line which should be require("conf_en.php");
// 2. Adapt this configuration file to your needs
// 3. Copy all the gallery files on your web server
// 4. Copy all your images files in subfolders of the folder define below in the parameter 'PHOTOS_DIR'
// 5. Visit your web site :) (It could be slower the first time due to the images gallery generation)

///IV Trick
// 1. Even if all is automatic, if you add new files in a folder, you should regenerate the kml file if you have google map activated
// To do that, just add the parameter to the url '&create=true'
// 2. Configuration of the parameters of the slideshow are actually in the "index.php" file (look for the string "prettyPhoto(")

/// Configuration parameters
//Global params
define('PHOTOS_DIR', 'photos'); //name of the folder of the public gallery where subfolders containing images files are stored (use a ftp client to upload your images in this directory)
define('ALPHABETIC_ORDER', true); // true : Order files in alphabetic order, false : do not order
define('SPACE_AROUND_MINIATURE', '10'); // Space around the thumbnails
define('GLOBAL_JPG_QUALITY', '50'); // jpeg compression rate for images created
define('HOME_NAME', 'My Photos'); //name of the main page
define('IMAGE_STDDIM', '800'); // maximum width or height of the image displayed
define('PHOTONAME_MAXCHAR', 20); // Maximum of characters displayed for a image name
define('DISPLAY_COPYLEFT', true); //Display non obstrusive copyleft credits (please keep true)
define('FOLDER_INFO_FILENAME', 'infos.txt'); //Filename of the informations of the sub-directory display as a tooltip (could be placed in each subgallery folder)

//Main page
define('ICO_WIDTH', '250'); // width of the thumbnail image displayed in the main page
define('ICO_HEIGHT', '150'); // height of the thumbnail image displayed in the main page
define('ICO_LINES', 3); //lines number of thumbnails displayed in the main page
define('ICO_PER_LINE', 4); //number of thumbnails per lines displayed in the main page

//Thumbnail page
define('MINIATURE_MAXDIM', '150'); // maximum width or height of the thumbnails generated for the thumbnail page
define('MINIATURES_LINES', 3); //lines number of thumbnails displayed in the thumbnail page
define('MINIATURES_PER_LINE', 6); //number of thumbnails per lines displayed in the thumbnail page
define('THUMB_MARGIN', 16); //margin around thumbnails

//Aditional features
define('GOOGLEMAP_ACTIVATE', true); // Enable GPS Images in Google Map
define('SLIDESHOW_ACTIVATE', true); // Enable Slideshow
define('SLIDESHOW_FULLSCREEN', true); //Display slideshow in full screen
define('ZIP_DOWNLOAD_ACTIVATE', true); // Enable download photos throught zip files

//Authentification
define('PRIVATE_GALLERY_ACTIVATE', true); //Enable a private gallery define below
define('ENCRYPTED_PASSWORD', false); //true : the passwords should be encrypted with sha1, false : the passwords are written in clear
//Trick:to encrypt with sha1, you can use the php script with the parameter "encode=" (ex: encode=my_pass) ans copy/paste the result
//array of parameters permitting to create different private galleries
//Note : different login/password could have the same directory to give access for different persons
//ex: array(login , password, 'directory of the private gallery')
$auth_right_and_path = array(
array('login','pwd','private'), //exemple login/pwd without sha1 encryption
array('sha1exemple','37fa265330ad83eaa879efb1e2db6380896cf639','private'), //exemple login/pwd with sha1 encryption
);

//Disqus Comments to permit visitors comments on photos (see http://www.disqus.com before activation)
define('COMMENTS_ACTIVATE', false); // Enable comments on photos
define('DISQUS_SHORTNAME', 'XXX'); // disqus forum shortname

/* If you configure a dark PAGE_COLOR (see "Style" part below),
 please place these lines in the "Custom CSS" of the "Appearance" Settings page from disqus to see comments well
#dsq-content #dsq-comments .dsq-comment-body {color: #fff;}
#dsq-content #dsq-comments .dsq-comment-header {color: #fff;}
div#dsq-new-post.dsq-post-area h3 {color: #fff;}
#dsq-content .dsq-request-user-stats span.dsq-request-user-stat {color: #fff;}
#dsq-comments-title {color: #fff;}
#dsq-content .dsq-options span.dsq-item-sort {color: #fff;}
#dsq-content #dsq-comments .dsq-collapsed-comment span {color: #fff;}
*/

//Style
define('MAIN_COLOR', '#CC7722'); // Theme color of the page
define('LIGHT_COLOR', '#CCCCCC'); // Light color around thumbnails
define('DARK_COLOR', '#666666'); // dark color for legends
define('PAGE_COLOR', '#000000'); // background color of the page
define('TEXT_COLOR', '#FFFFFF'); // color of the text

/// EXIF tags to display (in tooltips and in the detail page)
//Reorder and comment/uncomment, change label to display the EXIF datas you want
//ex: array(code1 , code2, 'Label')
// code1 & code2 : don't touch!
// Label : do want you want :)
$exif_to_display = array(
array('WINXP' , 'Title', 'Title'),
array('WINXP' , 'Comments', 'Comment'),
array('COMMENT' , '0', 'Comment'),
array('EXIF' , 'DateTimeOriginal', 'Date and Hour'),
array('EXIF' , 'ExposureTime', 'Exposition time'),
array('EXIF' , 'ISOSpeedRatings', 'ISO'),
array('COMPUTED' , 'ApertureFNumber', 'Focal Aperture'),
array('EXIF' , 'FocalLength', 'Focal length'),
array('EXIF' , 'Description', 'Description')
);

/// IPTC tags to display (in tooltips and in the detail page)
//Reorder and comment/uncomment, change label to display the IPTC datas you want
//ex: array(code , 'Label')
// code : don't touch!
// Label : do want you want :)
$iptc_to_display = array(
array('2#025' , 'Tags'),
array('2#122' , 'Autor'),
//array('2#120' , 'Legend / summary'),
//array('2#118' , 'Contact'),
//array('2#116' , 'Copyright'),
//array('2#115' , 'Source'),
//array('2#110' , 'Credit'),
//array('2#105' , 'Title'),
//array('2#103' , 'Référence à la transmission'),
//array('2#101' , 'Country'),
//array('2#100' , 'Country code'),
//array('2#095' , 'Province / stata'),
//array('2#092' , 'Region'),
//array('2#090' , 'City'),
//array('2#085' , 'Creator title'),
//array('2#080' , 'Creator'),
//array('2#075' , 'Object cycle'), //3 valeurs possibles : a = matin, b = après midi, c = soir'
//array('2#070' , 'Program version'),
//array('2#065' , 'Programm'),
//array('2#060' , 'Creation Hour'),
//array('2#055' , 'Creation Date'),
//array('2#040' , 'Special instruction'),
//array('2#035' , 'Output Hour / disponibility'), //HHMMSS'
//array('2#030' , 'Ouptut date / disponibility'), //16'
//array('2#026' , 'Location'),
//array('2#022' , 'Identifiant'),
//array('2#020' , 'Other category'), //tableau à plusieurs cases
//array('2#015' , 'Category'),
//array('2#010' , 'Priority'), //valeurs de 0 à 8 : 0 aucun, 1 = haut, 8 = faible'
//array('2#007' , 'Editorial Status'),
//array('2#005' , 'Object name'),
);

/// Translations
define('PHOTO_DIR_NEEDED','You must specify a photo directory !');
define('PHOTO_DIR_NOT_EXISTING','This photo directory do not exist !');
define('DISPLAY_MAP','Display the map');
define('NO_PHOTO_TO_DISPLAY','There is no photos to display !');
define('NO_PHOTO_WITH_GPS_DATA','No photo contains GPS datas :(');
define('SLIDESHOW','Slideshow');
define('OPEN_IN_GOOGLE_MAP','Open in google map');
define('TAGS','Tags : ');
define('AUTH_REQUIRED', 'Enter a login/password to access a private gallery.');
define('PRIVATE_GALLERY', 'Private Gallery');
define('PUBLIC_GALLERY', 'Public Gallery');
define('DOWNLOAD_ZIP', 'Download...');
define('DOWNLOAD_IMAGES', 'Download photos');
define('DOWNLOAD_FROM', 'Download the photos from');
define('DOWNLOAD_GALLERY', 'All the gallery photos (could be long and failed by timeout depnding the number of photos.)');
define('DOWNLOAD_LIST', 'Specific photo number');
define('DOWNLOAD_LIST_EX', 'Example of synthax:1,2,5');
define('DOWNLOAD_CANCEL', 'Cancel');
define('DOWNLOAD_DOWNLOAD', 'Download!');
define('DOWNLOAD_LIST_ERROR', 'Format error in the photos list string...');
define('INFO_ABOUT', 'About Facile Gallery');
define('INFO_WORK_BASED', 'Based on the work of');
define('INFO_GALLERY', 'Gallery');
define('INFO_SLIDESHOW', 'Slideshow');
define('INFO_CLOSE', 'Close');

///File under Licence CECILL
?>
