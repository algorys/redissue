# Plugin Redissue
Plugin Dokuwiki for connecting with Redmine :

Currently, this plugin only make a link in Dokuwiki to your Redmine's issues. In the future, this plugin will be able to display the status of issues : Open, Closed, Private.

## Requirements
Redissue needs a php API to work. You have to download [Php-Redmine-API](https://github.com/kbsali/php-redmine-api) inside the ROOT of your Dokuwiki's install.
```bash
$ mkdir vendor
$ cd vendor
$ git clone https://github.com/kbsali/php-redmine-api.git
$ git checkout v1.5.5
```
## Install
Download this plugin into your ``${dokuwiki_root}/lib/plugins`` folder and restart dokuwiki.

## Configuration
You can configure the plugin in the Config Manager of DokuWiki :

* redmine.url : Put your Redmine's url server, without a slash ending. Example : ``http://myredmine.com``
* redmine.img : Maybe you have a custom icon for your Redmine installation. You can put image'url here. Example : ``http://www.example.com/image.png``
* redmine.API : Set your Redmine API's key.
* redmine.view : Choose the view you want to display. This will depend on the wiki user's access rights in Redmine.

## Syntax
There is two way to use this plugin :

* First Syntax :

``<redissue id='#number_issue' text="the link's text" /> ``
* Second Syntax :

``<redissue id='#number_issue' text="the link's text"> Your description...</redissue>``

For further information, see also [Redissue on dokuwiki.org](https://www.dokuwiki.org/plugin:redissue)
