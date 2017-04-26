<?php

$lang['redissue.url']      = 'Ajoutez l\'url de votre serveur Redmine, sans le slash à la fin. Exemple : http://myredmine.com';
$lang['redissue.img']      = "Indiquez l'url de votre image. Exemple : http://www.example.com/image.png";
$lang['redissue.API']      = "Entrez la clé API de l'utilisateur Redmine, de préférence la clé d'un utilisateur Administrateur.";
$lang['redissue.view']     = "<div>Choisissez la vue que vous souhaitez afficher.</div><div>  * Impersonate : Fonctionnera si vous avez les même identifiants pour Redmine que pour Dokuwiki, par exemple une authentification avec LDAP. Redissue gèrera ensuite l'affichage en fonction des droits définis dans Redmine.</div><div>  * UserView : ne gère pas les droits de Redmine et affiche l'issue même si le projet est privé.</div> ";
$lang['redissue.view_o_4'] = "Impersonate";
$lang['redissue.view_o_2'] = "UserView";
$lang['redissue.short'] = '(<b>Theme Dokuwiki seulement</b>) Cochez cette case si vous voulez que redissue s\'affiche en mode short par défaut. La valeur peut être surchargée par le paramètre [short="0|1"]';
