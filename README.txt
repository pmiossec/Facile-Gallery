PHP Photo Module 0.2.3
________________________

Cr�� le 7 mars 2005
par Jensen SIU
________________________


PAGE DE TELECHARGEMENT : http://www.atelier-r.net/scripts.php
PAGE DEMO : http://jensensiu.free.fr

Distribu� sous licence CECILL :
http://www.cecill.info
________________________

INSTALATION :

- modifiez le fichier conf.php si vous le souhaitez.
- uploadez les fichiers dans votre r�pertoire FTP.

...et voil� !!! il n'y a plus qu' � vous rendre sur votre site pour tester.

Pour une installation sur une version pr�c�dente : �crasez tous simplement les anciens fichiers par les nouveaux.

________________________

DESCRIPTION :

Ce module est pr�vu pour une utilisation personnelle (mono utilisateur). De par sa simplicit�, il est facilement int�grable dans un site Web existant.

Propri�t�s du programme :
- un seul fichier pour tout le module.
- pas de base de donn�e.
- une seule profondeur de dossier pour plus de simplicit�.
- les miniatures se g�n�rent automatiquement � la dimension choisie.
- possibilit� de voir les photos en 3 tailles standards pr�d�finies.
- les titres des albums sont les titres des dossiers.
- affiche les donn�es EXIF des photos (dimensions, date, iso, focale...) si elles existent.
- les images de tailles diff�rentes sont g�n�r�es � la premi�re visite et restent ainsi en cache sauf si elles sont modifi�es.
- les dossiers comprenant des caract�res incompatibles sont automatiquements corrig�s.
- les dossiers photos sont automatiquement nettoy�s de tous dossiers et fichiers autres que jpg, gif et png.

________________________

MODIFICATION :

10 mars 2005 / version 0.1.1 :
- Ajout d'une fonction de renommage � la vol�e de nom de dossiers contenant des caract�res succeptibles de semer la confusion lors du passage du nom de dossier dans l'URL.
- Correction de bug lors de la cr�ation de la miniature de r�pertoire.

15 mars 2005 / version 0.2.0 :
- Ajout d'une fonction de renommage � la vol�e de nom de dossiers contenant des caract�res incompatibles.
Si vous utilisez les caract�res suivants : ][����������������������������������������������.!@#$%^&*+{}()'=$, ils seront automatiquement remplac�s.
- Ajout d'une fonction pour raccourcir l'affichage des noms d'image trop long pour les largeurs de tableaux.

4 avril 2005 / version 0.2.1 :
- Correction de plusieurs failles de s�curit� concernant le listage de dossiers et les "Full path disclosure".

3 juillet 2005 / version 0.2.2 :
- Ajout de la possibilit� de classer les dossiers et photos par ordre alphab�tique (merci � Yoome pour son d�but de contribution).
- Correction de l'affichage des titres des miniatures photo (fonction wordTruncate).
- Nettoyage de code inutile.

14 novembre 2005 / version 0.2.3 :
- Correction de la fonction de classement des dossiers et des fichiers photos par ordre alphab�tique (merci � Florent pour sa contribution).