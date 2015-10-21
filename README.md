# Plugin Redissue
Plugin Dokuwiki display issues of Redmine and keep the rights of Redmine too. Redissue have the following features :
* Name of issue
* Id, tracker, priority of issue
* Make a link to the choosen issue in Redmine.
* Display more or less information depending on your rights.
* Compatible with [Bootstrap](http://getbootstrap.com/) (need [Bootstrap3 Template](https://github.com/LotarProject/dokuwiki-template-bootstrap3/).

## Requirements
Redissue needs [Php-Redmine-API](https://github.com/kbsali/php-redmine-api) to work. Download it inside the ROOT of your redissue's folder or inside ``/usr/share/php`` folder. The second way is better if you use [Redproject](https://www.dokuwiki.org/plugin:redissue) too, as you've just to install API once time.

```bash
$ mkdir vendor
$ cd vendor
$ git clone https://github.com/kbsali/php-redmine-api.git
$ cd php-redmine-api
$ git checkout v1.5.5
```

Don't forget to install the requirements of PhP-Redmine-API :
```bash
$ sudo apt-get install php5-curl php5-common
```

## Install
Download this plugin into your ``${dokuwiki_root}/lib/plugins`` folder and restart dokuwiki.
* If you have bootstrap theme, you can checkout on bootstrap branch :
```bash
$ git checkout bootstrap
```
* If you have only dokuwiki theme or another one, try the v1.1
```bash
$ git checkout v1.1
```

## Configuration
You can configure the plugin in the Config Manager of DokuWiki :

* redissue.url : Put your Redmine's url server, without a slash ending. Example : ``http://myredmine.com``
* redissue.img : Maybe you have a custom icon for your Redmine installation. You can put image'url here. Example : ``http://www.example.com/image.png``
* redissue.API : Set your Redmine API's key, preference Administrator key.
* redissue.view : Choose the view you want to display. This will depend on the wiki user's access rights in Redmine.
  * Impersonate : select this if your wiki's users have the same UID as Redmine's users. e.g. : LDAP authentication. Redissue then will manage rights based on private or public projects.
  * Userview : doesn't manage access rights and display issue even if it's in private project.

## Syntax
There is two way to use this plugin :

* First Syntax :

``<redissue id='#number_issue' text="the link's text" /> ``
* Second Syntax :

``<redissue id='#number_issue' text="the link's text"> Your description...</redissue>``

## Preview
Here is a preview of redissue (dokuwiki theme):
![](http://s1.postimg.org/v1gniip1r/redissue.png)
And (bootstrap theme) :
![](http://s10.postimg.org/3jsjjq8sp/redissue2.png)

For further information, see also [Redissue on dokuwiki.org](https://www.dokuwiki.org/plugin:redissue)
