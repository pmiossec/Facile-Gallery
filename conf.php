<?php 
//TODO:
// - Googlemap : eviter de construire le kml � chaque fois
// - Googlemap : comprendre pourquoi le dossier Ecosse ne passe pas
// - Am�liorer carte pour la page principale avec image dans pushpin + lien vers la galerie
// - mode list : diminuer espace blanc entre photo et cadre des miniatures
// - Slideshow : ajouter les l�gendes
// - Slideshow : Tester => pouvoir le d�sactiver dans le code par une variable de config
// - Fusionner les pushpins pour afficher les donn�es des plusieurs photos sur le m�me pushpin (3max)
// - int�grer la carte pour ne pas qu'elle soit sur une autre page ?!?
// - nettoyer le code....Mutualiser code de la ligne d'en haut et du calcul de certaines variables,...
// - afficher les propri�t�s de l'image sur le c�t� ?!?

/*
Ce script offre la possibilit� d'afficher des images de format GIF, JPG ou PNG.
*/
define('ALPHABETIC_ORDER', true); // Classer les fichiers et les dossiers par ordre alphab�tique / false pour non class�
define('PHOTOS_DIR', 'photos'); //nom du r�pertoire un seront stock�s les sous r�pertoires de photos
define('THUMBS_DIR', 'miniatures'); // nom des r�pertoires contenant les fichiers de miniatures
define('ICO_FILENAME', '_icon.jpg'); // nom de l'icone cr��e � partir de la 1�re image de chaque r�pertoire
define('ICO_WIDTH', '250'); // largeur de l'image de l'icone en pixel / ne pas d�passer la moiti� de l'image originale
define('ICO_HEIGHT', '150'); // hauteur de l'image de l'icone en pixel / ne pas d�passer la moiti� de l'image originale
define('MINIATURE_MAXDIM', '120'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('GLOBAL_JPG_QUALITY', '50'); // taux de compression des jpg cr��s
/* 
La capacit� du script � cr�er vos miniatures photo d�pend de la rapidit� d'execution de votre serveur :
plus vous choisissez d'afficher de photos par page, plus il sera lent � la premi�re execution.
Une fois cr��es, les photos restent sur le serveur.
 */
define('MINIATURES_PER_PAGE', 18); //nombre de miniatures � afficher par page
define('MINIATURES_PER_LINE', 6); //nombre de miniatures � afficher par ligne dans les tableaux
define('HOME_NAME', 'Mes Photos'); //nom de la page principale
define('ICO_PER_PAGE', 16); //nombre de miniatures � afficher sur la page d'accueil
define('ICO_PER_LINE', 4); //nombre de miniatures � afficher par ligne sur la page d'accueil
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('IMAGE_400', '400'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('IMAGE_800', '1024'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('PHOTONAME_MAXCHAR', 20); // Nb max de caract�res pour un nom de photo
define('GOOGLEMAP_ACTIVATE', true); // Activation de la fonctionnalit� Google Map
define('SLIDESHOW_ACTIVATE', true); // Activation de la fonctionnalit� Slideshow (necessite prettyphoto : http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/)
define('DISPLAY_FOOTER', false); //Afficher le pied de page

/***********************************************
 ***************EXIF tags to display************
 **********************************************/
//Reorder and comment/uncomment, change label to display the iptc you want
//ex: array(code1 , code2, 'Label')
// code1 & code2 : don't touch!
// Label : do want you want :)
$exif_to_display = array(
array('EXIF' , 'DateTimeOriginal', 'Date et Heure'),
array('EXIF' , 'ExposureTime', 'Temps d\'exposition'),
array('EXIF' , 'ISOSpeedRatings', 'ISO'),
array('COMPUTED' , 'ApertureFNumber', 'Ouverture de la focale'),
array('EXIF' , 'FocalLength', 'Longueur de la focale'),
array('EXIF' , 'Description', 'Description')
);

/***********************************************
 ***************IPTC tags to display************
 **********************************************/
//Reorder and comment/uncomment, change label to display the iptc you want
//ex: array(code , 'Label')
// code : don't touch!
// Label : do want you want :)
$iptc_to_display = array(
array('2#025' , 'Tags'),//tableau � plusieurs cases' , '64 par mots cl�'),
array('2#122' , 'Auteur'),
//array('2#120' , 'L�gende / r�sum�'),//2000
//array('2#118' , 'Contact'),
//array('2#116' , 'Copyright'),//128'),
//array('2#115' , 'Source'),//32'),
//array('2#110' , 'Cr�dit'),//32'),
//array('2#105' , 'Titre'),//256'),
//array('2#103' , 'R�f�rence � la transmission'),
//array('2#101' , 'Pays'),//64'),
//array('2#100' , 'Code du pays'),//3'),
//array('2#095' , 'Province / �tat'),//32'),
//array('2#092' , 'R�gion'),
//array('2#090' , 'Ville'),//32'),
//array('2#085' , 'Titre du cr�ateur'),
//array('2#080' , 'Cr�ateur'),//64'),
//array('2#075' , 'Cycle de l\'objet'),//3 valeurs possibles : a = matin, b = apr�s midi, c = soir' , '1'),
//array('2#070' , 'Version du programme'),
//array('2#065' , 'Programme'),//15'),
//array('2#060' , 'Heure de cr�ation'),//HHMMSS'),
//array('2#055' , 'Date de cr�ation'),//16'),
//array('2#040' , 'Instruction sp�ciale'),//256'),
//array('2#035' , 'Heure de sortie / disponibilit�'),//HHMMSS'),
//array('2#030' , 'Date de sortie / disponibilit�'),//16'),
//array('2#026' , 'Location'),
//array('2#022' , 'Identifiant'),
//array('2#020' , 'Cat�gorie suppl�mentaire'),//tableau � plusieurs cases'),
//array('2#015' , 'Cat�gorie'),//3'),
//array('2#010' , 'Priorit�'),//valeurs de 0 � 8 : 0 aucun, 1 = haut, 8 = faible' , '1'),
//array('2#007' , 'Statut �ditorial'),
//array('2#005' , 'Nom de l\'objet'),//64'),
);

/***********************************************
 *******************Translation*****************
 **********************************************/
define('PHOTO_DIR_NEEDED','Vous devez sp&eacute;cifier un r&eacute;pertoire photo !');
define('BACK','Retour');
define('PHOTO_DIR_NOT_EXISTING','Ce r&eacute;pertoire photo n\'existe pas !');
define('DISPLAY_MAP','Afficher la carte');
define('NO_PHOTO_TO_DISPLAY','Il n\'y a aucune photo � afficher !');
define('NO_PHOTO_WITH_GPS_DATA','Aucune photo ne contient de donn�es GPS :(');
define('SLIDESHOW','Diaporama');
?>
