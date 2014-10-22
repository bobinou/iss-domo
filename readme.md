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

> sudo apt-get update && sudo apt-get install php5 && sudo apt-get install php5-mcrypt && sudo apt-get install php5-curl && sudo apt-get install unzip && sudo apt-get install php5-sqlite && sudo apt-get install git-core && cd /var/www/ && sudo git clone -b beta-jeedom https://github.com/bobinou/iss-domo.git && sudo chmod -R 777 /var/www/iss-domo/

---
---
4.INSTALLATION du service ISS-DOMO :

> sudo cp /var/www/iss-domo/iss-domo.sh /etc/init.d/iss-domo.sh

> sudo chmod +x /etc/init.d/iss-domo.sh

> sudo update-rc.d iss-domo.sh defaults

---
---
5.CONFIGURATION du service ISS-DOMO :

Editer le fichier /etc/init.d/iss-domo.sh et modifier les variables suivantes en fonction de votre installation :

> sudo nano /etc/init.d/iss-domo.sh

``` ISSDOMO_IP="192.168.0.26" ```

``` ISSDOMO_PORT=8000 ```

``` ISSDOMO_PATH="/var/www/iss-domo/" ```

===

Pour démarrer le service :

> sudo service iss-domo.sh start

Pour arrêter le service :

> sudo service iss-domo.sh stop

Pour contrôler le service :

> sudo service iss-domo.sh status


---
---
7.PARAMETRAGE de Imperihome

Ajouter depuis l'application Imperihome un systeme "Imperihome Standard System"

Indiquer en url en fonction de l'adresse de votre serveur ISS-DOMO : ```http://192.168.0.26:8000/```

---
---
8.Mise a jour de ISS-DOMO

> cd /var/www/iss-domo/ && sudo git pull

La configuration est conservée.



---
---
12.PARAMETRAGE de ISS-DOMO pour Jeedom 

Editer le fichier ```/var/www/iss-domo/app/config/hardware.php```

> sudo nano /var/www/iss-domo/app/config/hardware.php

Activer la gestion de Jeedom en indiquant ``` 'jeedom' => 1,```.

Editer le fichier ```/var/www/iss-domo/app/config/jeedom.php```

> sudo nano /var/www/iss-domo/app/config/jeedom.php

Modifier la ligne : ```jeedom_url``` afin d'indiquer l'url d'accès à votre serveur Jeedom.

Si Jeedom est installé sur le même serveur, indiquer par exemple : ```http://localhost/jeedom/core/api/jeeApi.php```

Modifier la ligne : ```api_key``` afin d'indiquer la clé API à votre serveur Jeedom (à retrouver depuis Jeedom dans le module Administration).

---
---
13.Integration d'ISS-Domo a NGinx

Placer ISS-Domo dans le repertoire ```/usr/share/nginx/www/``` s'il est dans ```/var/www/```

> sudo cp -R /var/www/iss-domo/ /usr/share/nginx/www/

Donner les droits 777 au repertoire iss-domo

> sudo chmod -R 777 /usr/share/nginx/www/iss-domo/

Editer le fcihier  ```/etc/nginx/sites-enabled/default```

> sudo nano /etc/nginx/sites-enabled/default

Ajouter les lignes suivantes AVANT la location /jeedom

``` location /iss-domo/public/ { ```

```            try_files $uri $uri/ @rewrite; ```

```        } ```

```        location @rewrite { ```

```                rewrite ^/(?<appname>[^/]+)/public/(?<appurl>.+)$ /$appname/public/index.php?_url=/$appurl last; ```

```        } ```

Redemarrer Nginx

ISS-Domo est alors accessible depuis Imperihome à l'adresse ```http://IP-server-iss-domo/iss-domo/public```

### License

ISS-Domo is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
