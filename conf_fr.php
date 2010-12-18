<?php
///Facile Gallery
// => Ce fichier a pour but de servir comme documentation, configuration et traduction

/// I. Résumé
// Le but de ce fichier php est de fournir une galerie d'image facile d'utilisation,
// uniquement en copier les images sur le serveur (et sans base de données)''

/// II. Principales fonctionnalité
// Galerie (GIF, JPG, PNG) avec gestion des sous galeries
// Panorama
// Affichage des données Exif/IPTC
// Affichage des données GPS (avec google map)
// Gestion de galeries privées multiples (avec accès par mot de passe)
// Adaptation facile des couleurs de la gallerie à vos souhaits
// Possibilité de permettre les commentaires par les visiteurs (en utilisant disqus : http://www.disqus.com)

///III. Installation / Configuration
// 1. Editer le fichier "index.php" et modifier la 2onde ligne de façon à avoir require("conf_fr.php");
// 2. Adapter ce fichier de configuration à vos besoins
// 3. Copier tous les fichiers de la gallerie sur le serveur web
// 4. Copier tous les fichiers images dans des sous-répertoire du répertoire défini ci-dessous par le paramètre 'PHOTOS_DIR'
// 5. Allez visiter votre galerie :) (La 1ère consultation peut être lente du fait de la génération de la gallerie)

///IV Astuce
// 1. Même si le fonctionnement de la galerie est automatique, lorsque vous ajoutez des images à une sous-gallerie existante,
// les fichiers ne sont pas pris en compte dans google map. Il faut régénérer le fichier kml en ajoutant le paramètre '&create=true' à l'url'
// 2. La configuration du panorama se trouve actuellement dans le fichier "index.php" ( cherchez la chaine "prettyPhoto(")

/// Paramètres de configuration
//Paramètres principaux
define('PHOTOS_DIR', 'photos'); //nom du répertoire de la galerie publique où seront stockés les sous répertoires de photos (utilisez un client ftp pour mettre les images dans ce répertoire)
define('ALPHABETIC_ORDER', true); // Classer les fichiers et les dossiers par ordre alphabétique / false pour non classé
define('THUMBS_DIR', 'miniatures'); // nom des répertoires contenant les fichiers de miniatures
define('SPACE_AROUND_MINIATURE', '10'); // Espace blanc autours des miniatures
define('GLOBAL_JPG_QUALITY', '50'); // taux de compression des jpg créés
define('HOME_NAME', 'Mes Photos'); //nom de la page principale
define('IMAGE_STDDIM', '800'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('PHOTONAME_MAXCHAR', 20); // Nb max de caractères pour un nom de photo
define('DISPLAY_COPYLEFT', true); //Afficher les informations non obstrusives de copyleft (laissez true s'il vous plait')
define('FOLDER_INFO_FILENAME', 'infos.txt'); //Nom du fichier dont le contenus sera affiché comme un tooltip (peut être placé dans chaque répertoire de sous-gallerie )

//Page principale
define('ICO_WIDTH', '250'); // largeur de l'image de l'icone en pixel / ne pas dépasser la moitié de l'image originale
define('ICO_HEIGHT', '150'); // hauteur de l'image de l'icone en pixel / ne pas dépasser la moitié de l'image originale
define('ICO_LINES', 3); //nombre de lignes de miniatures à afficher sur la page d'accueil
define('ICO_PER_LINE', 4); //nombre de miniatures à afficher par ligne sur la page d'accueil

//Page des miniatures
define('MINIATURE_MAXDIM', '150'); // largeur de l'image de miniature en pixel / ne pas dépasser la moitié de l'image originale
define('MINIATURES_LINES', 3); //nombre de lignes de miniatures à afficher par page
define('MINIATURES_PER_LINE', 6); //nombre de miniatures à afficher par ligne dans les tableaux
define('THUMB_MARGIN', 16); //espace autours de miniatures

//Fonctionalités additionelles
define('GOOGLEMAP_ACTIVATE', true); // Activation de la fonctionnalité Google Map
define('SLIDESHOW_ACTIVATE', true); // Activation de la fonctionnalité Slideshow
define('SLIDESHOW_FULLSCREEN', true); //Afficher le slideshow en plein écran

//Authentification
define('PRIVATE_GALLERY_ACTIVATE', true); //Activation des galleries privées
define('ENCRYPTED_PASSWORD', false); //true : les mots de passes sont saisis avec le chiffrage sha1, false : les mots de passe sont stockés en clair
//astuce:to encrypt with sha1, you can use the php script with the parameter "encode=" (ex: encode=my_pass) ans copy/paste the result
//tableau de paramètres permettant de donnez accès aux différentes galeries privé
//Note : differents login/mot_de_passe peuvent être urilisé pour donner accès à la gallerie privée à plusieurs personnes
//ex: array(login , mot_de_passe, 'répertoire de la gallerie privée')
$auth_right_and_path = array(
array('login','pwd','private'), //exemple login/mot_de_passe sans chiffrage sha1
array('sha1exemple','37fa265330ad83eaa879efb1e2db6380896cf639','private'), //exemple login/mot_de_passe avec chiffrage sha1
);

//Commentaires Disqus pour permettre aux visiteurs de commenter les photos (cf http://www.disqus.com avant activation)
define('COMMENTS_ACTIVATE', false); // Activer les commentaires sur les photos
define('DISQUS_SHORTNAME', 'XXX'); // shortname du forum disqus

//Style
define('MAIN_COLOR', '#CC7722'); // Couleur thème de la page
define('LIGHT_COLOR', '#CCCCCC'); // Couleur claire autours des miniatures
define('DARK_COLOR', '#666666'); // Couleur foncée autours des legendes
define('PAGE_COLOR', '#000000'); // Couleur de fond de la page
define('TEXT_COLOR', '#FFFFFF'); // Couleur du texte

/// EXIF tags to display
//Retrier, commenter/décommenter et changer le libellé pour afficher les données EXIF souhaitées
//ex: array(code1 , code2, 'Libellé')
// code1 & code2 : Ne pas modifier!
// Label : mettre ce qu'il vous plait :)
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
//Retrier, commenter/décommenter et changer le libellé pour afficher les données IPTC souhaitées
//ex: array(code , 'Label')
// code : ne pas changer!
// Label : mettre le libellé souhaité :)
$iptc_to_display = array(
array('2#025' , 'Tags'),
array('2#122' , 'Auteur'),
//array('2#120' , 'Légende / résumé'),
//array('2#118' , 'Contact'),
//array('2#116' , 'Copyright'),
//array('2#115' , 'Source'),
//array('2#110' , 'Crédit'),
//array('2#105' , 'Titre'),
//array('2#103' , 'Référence à la transmission'),
//array('2#101' , 'Pays'),
//array('2#100' , 'Code du pays'),
//array('2#095' , 'Province / état'),
//array('2#092' , 'Région'),
//array('2#090' , 'Ville'),
//array('2#085' , 'Titre du créateur'),
//array('2#080' , 'Créateur'),
//array('2#075' , 'Cycle de l\'objet'), //3 valeurs possibles : a = matin, b = après midi, c = soir'
//array('2#070' , 'Version du programme'),
//array('2#065' , 'Programme'),
//array('2#060' , 'Heure de création'),
//array('2#055' , 'Date de création'),
//array('2#040' , 'Instruction spéciale'),
//array('2#035' , 'Heure de sortie / disponibilité'), //HHMMSS'
//array('2#030' , 'Date de sortie / disponibilité'), //16'
//array('2#026' , 'Location'),
//array('2#022' , 'Identifiant'),
//array('2#020' , 'Catégorie supplémentaire'), //tableau à plusieurs cases
//array('2#015' , 'Catégorie'),
//array('2#010' , 'Priorité'), //valeurs de 0 à 8 : 0 aucun, 1 = haut, 8 = faible'
//array('2#007' , 'Statut éditorial'),
//array('2#005' , 'Nom de l\'objet'),
);

/// Translations
define('PHOTO_DIR_NEEDED','Vous devez sp&eacute;cifier un r&eacute;pertoire photo !');
define('PHOTO_DIR_NOT_EXISTING','Ce r&eacute;pertoire photo n\'existe pas !');
define('DISPLAY_MAP','Afficher la carte');
define('NO_PHOTO_TO_DISPLAY','Il n\'y a aucune photo à afficher !');
define('NO_PHOTO_WITH_GPS_DATA','Aucune photo ne contient de données GPS :(');
define('SLIDESHOW','Diaporama');
define('OPEN_IN_GOOGLE_MAP','Ouvrir sur google map');
define('TAGS','Tags : ');
define('AUTH_REQUIRED', 'Enterez un login/mot de passe pour accéder à une galerie privée.');
define('PRIVATE_GALLERY', 'Galerie privée');
define('PUBLIC_GALLERY', 'Galerie publique');

///File under Licence CECILL
?>
