# Plugin Redissue
Plugin Dokuwiki for connecting with Redmine :

Currently, this plugin only make a link in Dokuwiki to your Redmine's issues. In the future, this plugin will be able to display the status of issues : Open, Closed, Private.

## Install
Download this plugins into your ``${dokuwiki_root}/lib/plugins`` folder and restart dokuwiki.

## Configuration
You can configure the plugin in the Config Manager of DokuWiki :

* redmine.url : Put your redmine's url server, without a slash ending. Example : ``http://myredmine.com``
* redmine.img : Maybe you have a custom icon for your Redmine installation. You can put image'url here. Example : ``http://www.example.com/image.png``

## Syntax
There is two way to use this plugin :

* First Syntax :

``<redissue id='#number_issue' text="the link's text" /> ``
* Second Syntax :

``<redissue id='#number_issue' text="the link's text"> Your description...</redissue>``

For further information, see also [Redissue on dokuwiki.org](https://www.dokuwiki.org/plugin:redissue)
