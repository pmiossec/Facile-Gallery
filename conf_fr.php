<?php
///Facile Gallery
// => Ce fichier a pour but de servir comme documentation, configuration et traduction

/// I. R�sum�
// Le but de ce fichier php est de fournir une gallerie d'image facile d'utilisation,
// uniquement en copier les images sur le serveur (et sans base de donn�es)''

/// II. Principales fonctionnalit�
// gallerie (GIF, JPG, PNG), panorama, Affichage des donn�es Exif/IPTC displaying, affichage des donn�es GPS (avec google map)

///III. Installation / Configuration
// 1. Editer le fichier "index.php" et modifier la 2onde ligne de fa�on � avoir require("conf_fr.php");
// 2. Adapter ce fichier de configuration � vos besoins
// 3. Copier tous les fichiers de la gallerie sur le serveur web
// 4. Copier tous les fichiers images dans des sous-r�pertoire du r�pertoire d�fini ci-dessous par le param�tre 'PHOTOS_DIR'
// 5. Allez visiter votre gallerie :) (La 1�re consultation peut �tre lente du fait de la g�n�ration de la gallerie)

///IV Astuce
// 1. M�me si le fonctionnement de la galerie est automatique, lorsque vous ajoutez des images � une sous-gallerie existante,
// les fichiers ne sont pas pris en compte dans google map. Il faut r�g�n�rer le fichier kml en ajoutant le param�tre '&create=true' � l'url'
// 2. La configuration du panorama se trouve actuellement dans le fichier "index.php" ( cherchez la chaine "prettyPhoto(")

/// Param�tres de configuration
//Param�tres principaux
define('PHOTOS_DIR', 'photos'); //nom du r�pertoire un seront stock�s les sous r�pertoires de photos
define('ALPHABETIC_ORDER', true); // Classer les fichiers et les dossiers par ordre alphab�tique / false pour non class�
define('THUMBS_DIR', 'miniatures'); // nom des r�pertoires contenant les fichiers de miniatures
define('SPACE_AROUND_MINIATURE', '10'); // Espace blanc autours des miniatures
define('GLOBAL_JPG_QUALITY', '50'); // taux de compression des jpg cr��s
define('HOME_NAME', 'Mes Photos'); //nom de la page principale
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('PHOTONAME_MAXCHAR', 20); // Nb max de caract�res pour un nom de photo
define('DISPLAY_COPYLEFT', true); //Afficher les informations non obstrusives de copyleft (laissez true s'il vous plait')
define('FOLDER_INFO_FILENAME', 'infos.txt'); //Nom du fichier dont le contenus sera affich� comme un tooltip (peut �tre plac� dans chaque r�pertoire de sous-gallerie )

//Page principale
define('ICO_FILENAME', '_icon.jpg'); // nom de l'icone cr��e � partir de la 1�re image de chaque r�pertoire
define('ICO_WIDTH', '250'); // largeur de l'image de l'icone en pixel / ne pas d�passer la moiti� de l'image originale
define('ICO_HEIGHT', '150'); // hauteur de l'image de l'icone en pixel / ne pas d�passer la moiti� de l'image originale
define('ICO_LINES', 3); //nombre de lignes de miniatures � afficher sur la page d'accueil
define('ICO_PER_LINE', 4); //nombre de miniatures � afficher par ligne sur la page d'accueil

//Page des miniatures
define('MINIATURE_MAXDIM', '150'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('MINIATURES_LINES', 3); //nombre de lignes de miniatures � afficher par page
define('MINIATURES_PER_LINE', 6); //nombre de miniatures � afficher par ligne dans les tableaux

//Fonctionalit�s additionelles
define('GOOGLEMAP_ACTIVATE', true); // Activation de la fonctionnalit� Google Map
define('SLIDESHOW_ACTIVATE', true); // Activation de la fonctionnalit� Slideshow (necessite prettyphoto : http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/)
define('SLIDESHOW_FULLSCREEN', true); //Afficher le slideshow en plein �cran

/// EXIF tags to display
//Retrier, commenter/d�commenter et changer le libell� pour afficher les donn�es EXIF souhait�es
//ex: array(code1 , code2, 'Label')
// code1 & code2 : don't touch!
// Label : do want you want :)
$exif_to_display = array(
array('WINXP' , 'Title', 'Titre'),
array('WINXP' , 'Comments', 'Commentaire'),
array('COMMENT' , '0', 'Commentaire'),
array('EXIF' , 'DateTimeOriginal', 'Date et Heure'),
array('EXIF' , 'ExposureTime', 'Temps d\'exposition'),
array('EXIF' , 'ISOSpeedRatings', 'ISO'),
array('COMPUTED' , 'ApertureFNumber', 'Ouverture de la focale'),
array('EXIF' , 'FocalLength', 'Longueur de la focale'),
array('EXIF' , 'Description', 'Description')
);

/// IPTC tags to display
//Retrier, commenter/d�commenter et changer le libell� pour afficher les donn�es IPTC souhait�es
//ex: array(code , 'Label')
// code : ne pas changer!
// Label : mettre le libell� souhait� :)
$iptc_to_display = array(
array('2#025' , 'Tags'),
array('2#122' , 'Auteur'),
//array('2#120' , 'L�gende / r�sum�'),
//array('2#118' , 'Contact'),
//array('2#116' , 'Copyright'),
//array('2#115' , 'Source'),
//array('2#110' , 'Cr�dit'),
//array('2#105' , 'Titre'),
//array('2#103' , 'R�f�rence � la transmission'),
//array('2#101' , 'Pays'),
//array('2#100' , 'Code du pays'),
//array('2#095' , 'Province / �tat'),
//array('2#092' , 'R�gion'),
//array('2#090' , 'Ville'),
//array('2#085' , 'Titre du cr�ateur'),
//array('2#080' , 'Cr�ateur'),
//array('2#075' , 'Cycle de l\'objet'), //3 valeurs possibles : a = matin, b = apr�s midi, c = soir'
//array('2#070' , 'Version du programme'),
//array('2#065' , 'Programme'),
//array('2#060' , 'Heure de cr�ation'),
//array('2#055' , 'Date de cr�ation'),
//array('2#040' , 'Instruction sp�ciale'),
//array('2#035' , 'Heure de sortie / disponibilit�'), //HHMMSS'
//array('2#030' , 'Date de sortie / disponibilit�'), //16'
//array('2#026' , 'Location'),
//array('2#022' , 'Identifiant'),
//array('2#020' , 'Cat�gorie suppl�mentaire'), //tableau � plusieurs cases
//array('2#015' , 'Cat�gorie'),
//array('2#010' , 'Priorit�'), //valeurs de 0 � 8 : 0 aucun, 1 = haut, 8 = faible'
//array('2#007' , 'Statut �ditorial'),
//array('2#005' , 'Nom de l\'objet'),
);

/// Translations
define('PHOTO_DIR_NEEDED','Vous devez sp&eacute;cifier un r&eacute;pertoire photo !');
define('PHOTO_DIR_NOT_EXISTING','Ce r&eacute;pertoire photo n\'existe pas !');
define('DISPLAY_MAP','Afficher la carte');
define('NO_PHOTO_TO_DISPLAY','Il n\'y a aucune photo � afficher !');
define('NO_PHOTO_WITH_GPS_DATA','Aucune photo ne contient de donn�es GPS :(');
define('SLIDESHOW','Diaporama');
define('OPEN_IN_GOOGLE_MAP','Ouvrir sur google map');
define('TAGS','Tags : ');

///File under Licence CECILL
?>
