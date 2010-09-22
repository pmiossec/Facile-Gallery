<?php 
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
define('ICO_PER_PAGE', 16); //nombre de miniatures � afficher par page
define('ICO_PER_LINE', 4); //nombre de miniatures � afficher par ligne dans les tableaux
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('IMAGE_400', '400'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('IMAGE_800', '1024'); // largeur de l'image de miniature en pixel / ne pas d�passer la moiti� de l'image originale
define('PHOTONAME_MAXCHAR', 17); // Nb max de caract�res pour un nom de photo
define('GOOGLEMAP_KEY', 'ABQIAAAABx5vCKtNDJk_FQsgjJNJLRRUKmS4dCwRnBAOqX9EW63ghWPLRxQZLcfc8gFjCYowIb2YgpiSB_vv2w'); // Cl� Google Map
define('GOOGLEMAP_ACTIVATE', 'true'); // Cl� Google Map

/***********************************************
 ***************IPTC tags to display************
 **********************************************/
//Reorder and comment/uncomment, change label to display the iptc you want
//ex: array(code , 'Label', isValueAnArray)
// code : don't touch!
// Label : do want you want :)
// isValueAnArray : don't touch!
$iptc_to_display = array(
array('2#025' , 'Tags', true),//tableau � plusieurs cases' , '64 par mots cl�'),
array('2#122' , 'Auteur', false),
//array('2#120' , 'L�gende / r�sum�', false),//2000
//array('2#118' , 'Contact', true),
//array('2#116' , 'Copyright', false),//128'),
//array('2#115' , 'Source', false),//32'),
//array('2#110' , 'Cr�dit', false),//32'),
//array('2#105' , 'Titre', false),//256'),
//array('2#103' , 'R�f�rence � la transmission', false),
//array('2#101' , 'Pays', false),//64'),
//array('2#100' , 'Code du pays', false),//3'),
//array('2#095' , 'Province / �tat', false),//32'),
//array('2#092' , 'R�gion', false),
//array('2#090' , 'Ville', false),//32'),
//array('2#085' , 'Titre du cr�ateur', false),
//array('2#080' , 'Cr�ateur', false),//64'),
//array('2#075' , 'Cycle de l\'objet', false),//3 valeurs possibles : a = matin, b = apr�s midi, c = soir' , '1'),
//array('2#070' , 'Version du programme', false),
//array('2#065' , 'Programme', false),//15'),
//array('2#060' , 'Heure de cr�ation', false),//HHMMSS'),
//array('2#055' , 'Date de cr�ation', false),//16'),
//array('2#040' , 'Instruction sp�ciale', false),//256'),
//array('2#035' , 'Heure de sortie / disponibilit�', false),//HHMMSS'),
//array('2#030' , 'Date de sortie / disponibilit�', false),//16'),
//array('2#026' , 'Location', false),
//array('2#022' , 'Identifiant', false),
//array('2#020' , 'Cat�gorie suppl�mentaire', false),//tableau � plusieurs cases'),
//array('2#015' , 'Cat�gorie', false),//3'),
//array('2#010' , 'Priorit�', false),//valeurs de 0 � 8 : 0 aucun, 1 = haut, 8 = faible' , '1'),
//array('2#007' , 'Statut �ditorial', false),
//array('2#005' , 'Nom de l\'objet', false),//64'),
);




/***********************************************
 *******************Translation*****************
 **********************************************/

define('PHOTO_DIR_NEEDED','Vous devez sp&eacute;cifier un r&eacute;pertoire photo !');
define('BACK','Retour');
define('PHOTO_DIR_NOT_EXISTING','Ce r&eacute;pertoire photo n\'existe pas !');
define('DISPLAY_MAP','Afficher la carte');
define('NO_PHOTO_TO_DISPLAY','Il n\'y a aucune photo � afficher !');
define('NO_PHOTO_WITH_GPS_DATA','Aucune photo ne contient de donn�es GPS � afficher');
//define('','');
//define('','');



?>
