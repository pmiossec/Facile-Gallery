PHP Photo Module 0.2.3
________________________

Créé le 7 mars 2005
par Jensen SIU
________________________


PAGE DE TELECHARGEMENT : http://www.atelier-r.net/scripts.php
PAGE DEMO : http://jensensiu.free.fr

Distribué sous licence CECILL :
http://www.cecill.info
________________________

INSTALATION :

- modifiez le fichier conf.php si vous le souhaitez.
- uploadez les fichiers dans votre répertoire FTP.

...et voilà !!! il n'y a plus qu' à vous rendre sur votre site pour tester.

Pour une installation sur une version précédente : écrasez tous simplement les anciens fichiers par les nouveaux.

________________________

DESCRIPTION :

Ce module est prévu pour une utilisation personnelle (mono utilisateur). De par sa simplicité, il est facilement intégrable dans un site Web existant.

Propriétés du programme :
- un seul fichier pour tout le module.
- pas de base de donnée.
- une seule profondeur de dossier pour plus de simplicité.
- les miniatures se génèrent automatiquement à la dimension choisie.
- possibilité de voir les photos en 3 tailles standards prédéfinies.
- les titres des albums sont les titres des dossiers.
- affiche les données EXIF des photos (dimensions, date, iso, focale...) si elles existent.
- les images de tailles différentes sont générées à la première visite et restent ainsi en cache sauf si elles sont modifiées.
- les dossiers comprenant des caractères incompatibles sont automatiquements corrigés.
- les dossiers photos sont automatiquement nettoyés de tous dossiers et fichiers autres que jpg, gif et png.

________________________

MODIFICATION :

10 mars 2005 / version 0.1.1 :
- Ajout d'une fonction de renommage à la volée de nom de dossiers contenant des caractères succeptibles de semer la confusion lors du passage du nom de dossier dans l'URL.
- Correction de bug lors de la création de la miniature de répertoire.

15 mars 2005 / version 0.2.0 :
- Ajout d'une fonction de renommage à la volée de nom de dossiers contenant des caractères incompatibles.
Si vous utilisez les caractères suivants : ][àáâãäåÀÁÂÃÄÅÈÉÊËèéêëÌÍÎÏìíîïÒÓÔÕÖòóôõöÙÚÛÜùúûü.!@#$%^&*+{}()'=$, ils seront automatiquement remplacés.
- Ajout d'une fonction pour raccourcir l'affichage des noms d'image trop long pour les largeurs de tableaux.

4 avril 2005 / version 0.2.1 :
- Correction de plusieurs failles de sécurité concernant le listage de dossiers et les "Full path disclosure".

3 juillet 2005 / version 0.2.2 :
- Ajout de la possibilité de classer les dossiers et photos par ordre alphabétique (merci à Yoome pour son début de contribution).
- Correction de l'affichage des titres des miniatures photo (fonction wordTruncate).
- Nettoyage de code inutile.

14 novembre 2005 / version 0.2.3 :
- Correction de la fonction de classement des dossiers et des fichiers photos par ordre alphabétique (merci à Florent pour sa contribution).