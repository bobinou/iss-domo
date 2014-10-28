ISS-DOMO Beta Version
---

Convert ISS Imperihome to Jeedom

Beta version !!!

Community for users here : https://plus.google.com/communities/113236981415459933411

---
---
1.INTRODUCTION

ISS-Domo permet de faire communiquer l'application Android Imperihome avec le logiciel Jeedom.

ISS-Domo repose sur le framework "Laravel" https://github.com/laravel/laravel.

ISS-Domo utilise la ClassePhpFreebox https://github.com/DjMomo/ClassePhpFreebox

ISS-Domo est développé en PHP.

ISS-DOMO peut être installé sur un Raspberry.

ISS-DOMO peut être installé sur le même serveur que Jeedom.

---
---
2.INSTALLATION des DEPENDANCES et de ISS-DOMO

ISS-Domo is install with the next command in the directory /var/www. You could install it where you want.

> sudo apt-get update && sudo apt-get install php5 && sudo apt-get install php5-mcrypt && sudo apt-get install php5-curl && sudo apt-get install unzip && sudo apt-get install php5-sqlite && sudo apt-get install git-core && cd /usr/share/nginx/www/ && sudo git clone -b beta-jeedom https://github.com/bobinou/iss-domo.git && sudo chmod -R 777 /usr/share/nginx/www/iss-domo/

---
---
3.Integration d'ISS-Domo a NGinx

Editer le fichier  ```/etc/nginx/sites-enabled/default```

> sudo nano /etc/nginx/sites-enabled/default

Ajouter les lignes suivantes AVANT la location /jeedom

``` location /iss-domo/public/ { ```

```            try_files $uri $uri/ @rewrite; ```

```        } ```

```        location @rewrite { ```

```                rewrite ^/(?<appname>[^/]+)/public/(?<appurl>.+)$ /$appname/public/index.php?_url=/$appurl last; ```

```        } ```

Redemarrer Nginx

> sudo /etc/init.d/nginx restart

ISS-Domo est alors accessible depuis Imperihome à l'adresse ```http://IP-server-iss-domo/iss-domo/public```

---
---
4.PARAMETRAGE de Imperihome

Ajouter depuis l'application Imperihome un systeme "Imperihome Standard System"

Indiquer en url en fonction de l'adresse de votre serveur ISS-DOMO : ```http://IP-server-iss-domo/iss-domo/public```

---
---
5.Mise a jour de ISS-DOMO

> cd /usr/share/nginx/www/iss-domo/ && sudo git pull

La configuration est conservée.

---
---
6.PARAMETRAGE de ISS-DOMO pour Jeedom 

Editer le fichier ```/usr/share/nginx/www/iss-domo/app/config/hardware.php```

> sudo nano /usr/share/nginx/www/iss-domo/app/config/hardware.php

Activer la gestion de Jeedom en indiquant ``` 'jeedom' => 1,``` et ``` 'domoticz' => 0,```.

Editer le fichier ```/usr/share/nginx/www/iss-domo/app/config/jeedom.php```

> sudo nano /usr/share/nginx/www/iss-domo/app/config/jeedom.php

Modifier la ligne : ```jeedom_url``` afin d'indiquer l'url d'accès à votre serveur Jeedom.

Si Jeedom est installé sur le même serveur, indiquer par exemple : ```http://localhost/jeedom/core/api/jeeApi.php```

Modifier la ligne : ```api_key``` afin d'indiquer la clé API à votre serveur Jeedom (à retrouver depuis Jeedom dans le module Administration).
### License

ISS-Domo is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
