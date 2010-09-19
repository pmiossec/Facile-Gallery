<?php 
/*
Ce script offre la possibilit� d'afficher des images de format GIF, JPG ou PNG.
*/
define('ALPHABETIC_ORDER', true); // Classer les fichiers et les dossiers par ordre alphab�tique / false pour non class�
define('PHOTOS_DIR', 'photos'); //nom du r�pertoire un seront stock�s les sous r�pertoires de photos
define('THUMBS_DIR', 'miniatures'); // nom des r�pertoires contenant les fichiers de miniatures
define('ICO_FILENAME', '_icon.jpg'); // nom de l'icone cr��e � partir de la 1�re image de chaque r�pertoire
define('ICO_WIDTH', '250'); // largeur de l'image de l'icone en pixel / ne pas d�passer la moiti� de l'image originale
define('ICO_HEIGHT', '200'); // hauteur de l'image de l'icone en pixel / ne pas d�passer la moiti� de l'image originale
define('MINIATURE_MAXDIM', '120'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('GLOBAL_JPG_QUALITY', '80'); // taux de compression des jpg cr��s
/* 
La capacit� du script � cr�er vos miniatures photo d�pend de la rapidit� d'execution de votre serveur :
plus vous choisissez d'afficher de photos par page, plus il sera lent � la premi�re execution.
Une fois cr��es, les photos restent sur le serveur.
 */
define('MINIATURES_PER_PAGE', 18); //nombre de miniatures � afficher par page
define('MINIATURES_PER_LINE', 6); //nombre de miniatures � afficher par ligne dans les tableaux
define('HOME_NAME', 'Mes Photos'); //nombre de miniatures � afficher par ligne dans les tableaux
define('ICO_PER_PAGE', 12); //nombre de miniatures � afficher par page
define('ICO_PER_LINE', 3); //nombre de miniatures � afficher par ligne dans les tableaux
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('IMAGE_400', '400'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('IMAGE_800', '1024'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('PHOTONAME_MAXCHAR', 17); // Nb max de caract�res pour un nom de photo
?>
