<?php 
/*
Ce script offre la possibilité d'afficher des images de format GIF, JPG ou PNG.
*/
define('ALPHABETIC_ORDER', true); // Classer les fichiers et les dossiers par ordre alphabétique / false pour non classé
define('PHOTOS_DIR', 'photos'); //nom du répertoire un seront stockés les sous répertoires de photos
define('THUMBS_DIR', 'miniatures'); // nom des répertoires contenant les fichiers de miniatures
define('ICO_FILENAME', '_icon.jpg'); // nom de l'icone créée à partir de la 1ère image de chaque répertoire
define('ICO_WIDTH', '250'); // largeur de l'image de l'icone en pixel / ne pas dépasser la moitié de l'image originale
define('ICO_HEIGHT', '150'); // hauteur de l'image de l'icone en pixel / ne pas dépasser la moitié de l'image originale
define('MINIATURE_MAXDIM', '120'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('GLOBAL_JPG_QUALITY', '50'); // taux de compression des jpg créés
/* 
La capacité du script à créer vos miniatures photo dépend de la rapidité d'execution de votre serveur :
plus vous choisissez d'afficher de photos par page, plus il sera lent à la première execution.
Une fois créées, les photos restent sur le serveur.
 */
define('MINIATURES_PER_PAGE', 18); //nombre de miniatures à afficher par page
define('MINIATURES_PER_LINE', 6); //nombre de miniatures à afficher par ligne dans les tableaux
define('HOME_NAME', 'Mes Photos'); //nom de la page principale
define('ICO_PER_PAGE', 16); //nombre de miniatures à afficher par page
define('ICO_PER_LINE', 4); //nombre de miniatures à afficher par ligne dans les tableaux
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('IMAGE_400', '400'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('IMAGE_800', '1024'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('PHOTONAME_MAXCHAR', 17); // Nb max de caractères pour un nom de photo
define('GOOGLEMAP_KEY', 'ABQIAAAABx5vCKtNDJk_FQsgjJNJLRRUKmS4dCwRnBAOqX9EW63ghWPLRxQZLcfc8gFjCYowIb2YgpiSB_vv2w'); // Clé Google Map
define('GOOGLEMAP_ACTIVATE', 'true'); // Clé Google Map

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
array('2#025' , 'Tags'),//tableau à plusieurs cases' , '64 par mots clé'),
array('2#122' , 'Auteur'),
//array('2#120' , 'Légende / résumé'),//2000
//array('2#118' , 'Contact'),
//array('2#116' , 'Copyright'),//128'),
//array('2#115' , 'Source'),//32'),
//array('2#110' , 'Crédit'),//32'),
//array('2#105' , 'Titre'),//256'),
//array('2#103' , 'Référence à la transmission'),
//array('2#101' , 'Pays'),//64'),
//array('2#100' , 'Code du pays'),//3'),
//array('2#095' , 'Province / état'),//32'),
//array('2#092' , 'Région'),
//array('2#090' , 'Ville'),//32'),
//array('2#085' , 'Titre du créateur'),
//array('2#080' , 'Créateur'),//64'),
//array('2#075' , 'Cycle de l\'objet'),//3 valeurs possibles : a = matin, b = après midi, c = soir' , '1'),
//array('2#070' , 'Version du programme'),
//array('2#065' , 'Programme'),//15'),
//array('2#060' , 'Heure de création'),//HHMMSS'),
//array('2#055' , 'Date de création'),//16'),
//array('2#040' , 'Instruction spéciale'),//256'),
//array('2#035' , 'Heure de sortie / disponibilité'),//HHMMSS'),
//array('2#030' , 'Date de sortie / disponibilité'),//16'),
//array('2#026' , 'Location'),
//array('2#022' , 'Identifiant'),
//array('2#020' , 'Catégorie supplémentaire'),//tableau à plusieurs cases'),
//array('2#015' , 'Catégorie'),//3'),
//array('2#010' , 'Priorité'),//valeurs de 0 à 8 : 0 aucun, 1 = haut, 8 = faible' , '1'),
//array('2#007' , 'Statut éditorial'),
//array('2#005' , 'Nom de l\'objet'),//64'),
);

/***********************************************
 *******************Translation*****************
 **********************************************/
define('PHOTO_DIR_NEEDED','Vous devez sp&eacute;cifier un r&eacute;pertoire photo !');
define('BACK','Retour');
define('PHOTO_DIR_NOT_EXISTING','Ce r&eacute;pertoire photo n\'existe pas !');
define('DISPLAY_MAP','Afficher la carte');
define('NO_PHOTO_TO_DISPLAY','Il n\'y a aucune photo à afficher !');
define('NO_PHOTO_WITH_GPS_DATA','Aucune photo ne contient de données GPS :(');
?>
