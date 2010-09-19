<?php 
/*
Ce script offre la possibilité d'afficher des images de format GIF, JPG ou PNG.
*/
define('ALPHABETIC_ORDER', true); // Classer les fichiers et les dossiers par ordre alphabétique / false pour non classé
define('PHOTOS_DIR', 'photos'); //nom du répertoire un seront stockés les sous répertoires de photos
define('THUMBS_DIR', 'miniatures'); // nom des répertoires contenant les fichiers de miniatures
define('ICO_FILENAME', '_icon.jpg'); // nom de l'icone créée à partir de la 1ère image de chaque répertoire
define('ICO_WIDTH', '250'); // largeur de l'image de l'icone en pixel / ne pas dépasser la moitié de l'image originale
define('ICO_HEIGHT', '200'); // hauteur de l'image de l'icone en pixel / ne pas dépasser la moitié de l'image originale
define('MINIATURE_MAXDIM', '120'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('GLOBAL_JPG_QUALITY', '80'); // taux de compression des jpg créés
/* 
La capacité du script à créer vos miniatures photo dépend de la rapidité d'execution de votre serveur :
plus vous choisissez d'afficher de photos par page, plus il sera lent à la première execution.
Une fois créées, les photos restent sur le serveur.
 */
define('MINIATURES_PER_PAGE', 18); //nombre de miniatures à afficher par page
define('MINIATURES_PER_LINE', 6); //nombre de miniatures à afficher par ligne dans les tableaux
define('HOME_NAME', 'Mes Photos'); //nombre de miniatures à afficher par ligne dans les tableaux
define('ICO_PER_PAGE', 12); //nombre de miniatures à afficher par page
define('ICO_PER_LINE', 3); //nombre de miniatures à afficher par ligne dans les tableaux
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('IMAGE_400', '400'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('IMAGE_800', '1024'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('PHOTONAME_MAXCHAR', 17); // Nb max de caractères pour un nom de photo
?>
