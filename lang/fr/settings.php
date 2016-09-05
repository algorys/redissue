<?php

$lang['redissue.url']      = 'Ajoutez l\'url de votre serveur Redmine, sans le slash à la fin. Exemple : http://myredmine.com';
$lang['redissue.img']      = "Indiquez l'url de votre image. Exemple : http://www.example.com/image.png";
$lang['redissue.theme']    = 'Choisissez le theme de redissue. Le thème Bootstrap nécessite le <a href="https://www.dokuwiki.org/template:bootstrap3">Template Bootstrap 3</a> ou un theme utilisant <a href="http://getbootstrap.com/">Bootstrap</a>.';
$lang['redissue.theme_o_8'] = "Bootstrap";
$lang['redissue.theme_o_6'] = "Dokuwiki";
$lang['redissue.API']      = "Entrez la clé API de l'utilisateur Redmine, de préférence la clé d'un utilisateur Administrateur.";
$lang['redissue.view']     = "<div>Choisissez la vue que vous souhaitez afficher.</div><div>  * Impersonate : Fonctionnera si vous avez les même identifiants pour Redmine que pour Dokuwiki, par exemple une authentification avec LDAP. Redissue gèrera ensuite l'affichage en fonction des droits définis dans Redmine.</div><div>  * UserView : ne gère pas les droits de Redmine et affiche l'issue même si le projet est privé.</div> ";
$lang['redissue.view_o_4'] = "Impersonate";
$lang['redissue.view_o_2'] = "UserView";
