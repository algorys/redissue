<?php

$lang['redissue.url']      = 'Add your redmine\'s url server, without a slash ending. Example : http://myredmine.com';
$lang['redissue.img']      = "Type your image's url here. Example : http://www.example.com/mylogo.png";
$lang['redissue.bootstrap-themes'] = 'List of themes supporting "bootstrap", separated by commas. Redissue will use the "bootstrap" theme if these themes are applied. Eg. <code>bootstrap3,bootie</code>';
$lang['redissue.API']      = "Enter your Redmine API's key";
$lang['redissue.view']     = "<div>Choose the view you want to display. This will depend on the wiki user's access rights in Redmine.</div>
<div>Impersonate : select this if your wiki's users have the same UID as Redmine's users. e.g. : LDAP authentication. Redissue then will manage rights based on private or public projects.</div>
<div>Userview : doesn't manage access rights and display issue even if it's in private project.</div>";
$lang['redissue.view_o_4'] = "Impersonate";
$lang['redissue.view_o_2'] = "UserView";
$lang['redissue.short'] = '(<b>Dokuwiki Theme only</b>) Check this if you want redissue in short mode by default. Value can be override by parameter [short="0|1"]';
