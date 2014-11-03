ISS-DOMO
---

Convert ISS Imperihome to Domoticz / Jeedom / Freebox Player / Freebox Server / XBMC / NAS from Freebox Player

Support history and graph to Imperihome

Community for users here : https://plus.google.com/communities/113236981415459933411

---
---
1.INTRODUCTION

ISS-Domo repose sur le framework "Laravel" https://github.com/laravel/laravel.

ISS-Domo utilise la ClassePhpFreebox https://github.com/DjMomo/ClassePhpFreebox

ISS-Domo est développé en PHP.

ISS-DOMO peut être installé sur un Raspberry.

ISS-DOMO peut être installé sur le même serveur que Domoticz ou Jeedom.

---
---
2.INSTALLATION des DEPENDANCES et de ISS-DOMO

ISS-Domo is install with the next command in the directory /var/www. You could install it where you want.

> sudo apt-get update && sudo apt-get install -y php5 && sudo apt-get install -y php5-mcrypt && sudo apt-get install -y php5-curl && sudo apt-get install -y unzip && sudo apt-get install -y php5-sqlite && sudo apt-get install -y git-core && cd /var/www/ && sudo git clone https://github.com/bobinou/iss-domo.git && sudo chmod -R 777 /var/www/iss-domo/

---
---
AU CHOIX SOIT UTILISATION DE NGINX (3) / OU UTILISATION DU SERVICE ISS-DOMO(4)

---
---
3.Integration d'ISS-Domo a NGinx

Copier iss-domo dans le répertoire www de Nginx iss-domo :

> sudo mv /var/www/iss-domo/ /usr/share/nginx/www/

Appliquer les droits :

> sudo chmod -R 777 /usr/share/nginx/www/iss-domo/

Editer le fichier  ```/etc/nginx/sites-enabled/default```

> sudo nano /etc/nginx/sites-enabled/default

Ajouter les lignes suivantes AVANT la location /jeedom si elle existe

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
4.INSTALLATION du service ISS-DOMO :

> sudo cp /var/www/iss-domo/iss-domo.sh /etc/init.d/iss-domo.sh

> sudo chmod +x /etc/init.d/iss-domo.sh

> sudo update-rc.d iss-domo.sh defaults

---
---
4.1.CONFIGURATION du service ISS-DOMO :

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
5.PARAMETRAGE de ISS-DOMO pour Domoticz

Editer le fichier ```/var/www/iss-domo/app/config/hardware.php```

> sudo nano /var/www/iss-domo/app/config/hardware.php

Activer la gestion de Domoticz en indiquant ``` 'domoticz' => 1,```.

Editer le fichier ```/var/www/iss-domo/app/config/iss-domo.php```

> sudo nano /var/www/iss-domo/app/config/iss-domo.php

Modifier la ligne : ```domoticz_url``` afin d'indiquer l'url d'accès à votre serveur Domoticz.

Si Domoticz est installé sur le même serveur et indiquer par exemple : ```http://localhost:8180```

---
---
6.PARAMETRAGE de ISS-DOMO pour Jeedom

Editer le fichier ```/var/www/iss-domo/app/config/hardware.php``` ou ```/usr/share/nginx/www/iss-domo/app/config/hardware.php``` en fonction de votre configuration.

> sudo nano /var/www/iss-domo/app/config/hardware.php

ou

> sudo nano /usr/share/nginx/www/iss-domo/app/config/hardware.php

Activer la gestion de Jeedom en indiquant ``` 'jeedom' => 1,``` et desactiver Domoticz ``` 'domoticz' => 0,```.

Editer le fichier ```/var/www/iss-domo/app/config/jeedom.php``` ou ```/usr/share/nginx/www/iss-domo/app/config/jeedom.php``` en fonction de votre configuration.

> sudo nano /var/www/iss-domo/app/config/jeedom.php

ou

> sudo nano /usr/share/nginx/www/iss-domo/app/config/jeedom.php

Modifier la ligne : ```jeedom_url``` afin d'indiquer l'url d'accès à votre serveur Jeedom.

Si Jeedom est installé sur le même serveur, indiquer par exemple : ```http://localhost/jeedom/core/api/jeeApi.php```

Modifier la ligne : ```api_key``` afin d'indiquer la clé API à votre serveur Jeedom (à retrouver depuis Jeedom dans le module Administration).

---
---
7.PARAMETRAGE de Imperihome

Ajouter depuis l'application Imperihome un systeme "Imperihome Standard System"

Indiquer en url en fonction de l'adresse de votre serveur ISS-DOMO : 

- Pour un fonctionnement avec le service ISS-Domo : ```http://192.168.0.26:8000/```
- Pour un fonctionnement avec Nginx : ```http://192.168.0.26/iss-domo/public/```

---
---
8.Mise a jour de ISS-DOMO

Pour le service ISS-Domo :

> cd /var/www/iss-domo/ && sudo git pull

Pour ISS-Domo sur Nginx :

> cd /usr/share/nginx/www/iss-domo/ && sudo git pull

La configuration est conservée.

---
---
9.PARAMETRAGE de ISS-DOMO pour Freebox Server (SANS Domoticz)

Editer le fichier ```/var/www/iss-domo/app/config/hardware.php```

> sudo nano /var/www/iss-domo/app/config/hardware.php

Activer la gestion de la Freebox Server en indiquant ``` 'freebox_server' => 1,```.

Désactiver la gestion de Domoticz en indiquant ``` 'domoticz' => 0,```.

Supprimer le fichier ```/var/www/iss-domo/app/storage/freebox/token```

> sudo rm /var/www/iss-domo/app/storage/freebox/token

Lancer depuis votre navigateur l'url en fonction de votre configuration) ```http://192.168.0.26:8000/freebox```.

Votre Freebox Server (sur son écran) va alors vous demander de valider l'accès au logiciel ISS-Domo, répondre OUI avec la flèche de droite.

Si l'url lancée précédement vous a renvoyé une erreur, relancez la. En fonction normal cette url doit afficher une liste de valeurs.

---
---
10.PARAMETRAGE de ISS-DOMO pour Freebox Server (AVEC Domoticz)

Editer le fichier ```/var/www/iss-domo/app/config/hardware.php```

> sudo nano /var/www/iss-domo/app/config/hardware.php

Activer la gestion de la Freebox Server en indiquant ``` 'freebox_server' => 1,```.

Activer la gestion de Domoticz en indiquant ``` 'domoticz' => 1,```.

Supprimer le fichier ```/var/www/iss-domo/app/storage/freebox/token```

> sudo rm /var/www/iss-domo/app/storage/freebox/token

Lancer depuis votre navigateur l'url en fonction de votre configuration) ```http://192.168.0.26:8000/freebox```.

Votre Freebox Server (sur son écran) va alors vous demander de valider l'accès au logiciel ISS-Domo, répondre OUI avec la flèche de droite.

Si l'url lancée précédement vous a renvoyé une erreur, relancez la. En fonction normal cette url doit afficher une liste de valeurs.

---
---
11.INTEGRATION de Freebox Server à Domoticz

Une fois l'étape précédente n°9 effectuée, il vous faut intégrer la Freebox Server à Domoticz.

Dans Domoticz :

-Ajouter un materiel de type "Dummy" 

-Créer 3 "virtual sensors" type "Temperature" (Freebox Server Temp SW, Freebox Server Temp CPU B, Freebox Server Temp CPU M)

-Créer 1 "virtual sensors" type "Pourcentage" (Freebox Fan)

-Récupérer les idx des "virtual sensors" précédement créé dans la liste des périphériques et les ajouter

Sur votre serveur ISS-Domo :

-Editer le script freebox.sh et remplacer les variables d'initialisation par vos paramètres

> sudo nano /var/www/iss-domo/freebox.sh

``` ISSDOMO_SERVER="192.168.0.26:8000" ```

``` DOMOTICZ_SERVER="192.168.0.26:8180" ``` 

``` FREE_SERV_TEMP_SW="29" ``` 

``` FREE_SERV_TEMP_CPU_B="30" ``` 

``` FREE_SERV_TEMP_CPU_M="31" ``` 

``` FREE_SERV_FAN="32" ``` 

Rendez exécutable le script freebox.sh

> sudo chmod +x /var/www/iss-domo/freebox.sh

Lancer le script manuellement afin de vérifier que les données de la Freebox Server remontent dans Domoticz.

> sudo /var/www/iss-domo/freebox.sh

Si le fonctionnement est OK, ajouter une tâche planifiée, par exemple :

> crontab -e

```*/2 * * * * /var/www/iss-domo/freebox.sh ``` 

La tâche planifiée remontera dans Domoticz les données de la Freebox Server toutes les 2 minutes.

---
---
DEBUG ISS-DOMO et Freebox Server:

Pour vérifier qu'ISS-Domo a bien accès à la Freebox Server, rendez-vous sur son interface de gestion, Paramètres de la Freebox, Gestion des Accès, Onglet Application. Dans cette liste doit se trouver ISS-Domo.

Si ISS-Domo est dans la liste mais est "en attente de validation" ou "délais dépassé" :

-supprimer l'application depuis l'interface Freebox Server

-supprimer le fichier ```/var/www/iss-domo/app/storage/freebox/token```

> sudo rm /var/www/iss-domo/app/storage/freebox/token

-relancer l'url ```http://192.168.0.26:8000/freebox```

-Votre Freebox Server (sur son écran) va alors vous demander de valider l'accès au logiciel ISS-Domo, répondre OUI avec la flèche de droite.

---
---
12.PARAMETRAGE de ISS-DOMO pour XBMC (AVEC ou SANS Domoticz)

Editer le fichier ```/var/www/iss-domo/app/config/hardware.php```

> sudo nano /var/www/iss-domo/app/config/hardware.php

Activer la gestion de XBMC en indiquant ``` 'xbmc' => 1,```.

Désactiver ou activer la gestion de Domoticz en indiquant ``` 'domoticz' => 0,``` ou ``` 'domoticz' => 1,```.

Editer le fichier ```/var/www/iss-domo/app/config/xbmc.php```

> sudo nano /var/www/iss-domo/app/config/xbmc.php

Dans ce fichier indiquer l'url d'XBMC ainsi que son port à la ligne  ``` 'xbmc_url' => 'http://192.168.0.26:8080/jsonrpc' ```

Indiquer ensuite si vous souhaitez utiliser la médiathèque de films et/ou de musiques aux lignes
  ``` ''xbmc_movies' => 1   ```
  ```	'xbmc_songs' => 1   ```

### License

ISS-Domo is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
