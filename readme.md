ISS-DOMO
---

Convert ISS Imperihome to Domoticz / Freebox Player / Freebox Server / XBMC / NAS


---
---
1.INTRODUCTION

ISS-Domo permet de faire communiquer l'application Android Imperihome avec le logiciel Domoticz.

ISS-Domo repose sur le framework "Laravel" https://github.com/laravel/laravel.

ISS-Domo est développé en PHP.

ISS-DOMO peut être installé sur un Raspberry.

ISS-DOMO peut être installé sur le même serveur que Domoticz.

---
---
2.INSTALLATION des DEPENDANCES et de ISS-DOMO

ISS-Domo is install with the next command in the directory /var/www. You could install it where you want.

> sudo apt-get update && sudo apt-get install php5 && sudo apt-get install php5-mcrypt && sudo apt-get install php5-curl && sudo apt-get install unzip && sudo apt-get install git-core && cd /var/www/ && sudo git clone https://github.com/bobinou/iss-domo.git && sudo chmod -R 777 /var/www/iss-domo/

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
6.PARAMETRAGE de ISS-DOMO pour Domoticz

Editer le fichier ```/var/www/iss-domo/app/config/iss-domo.php```

> sudo nano /var/www/iss-domo/app/config/iss-domo.php

Modifier la ligne : ```domoticz_url``` afin d'indiquer l'url d'accès à votre serveur Domoticz.

Si Domoticz est installé sur le même serveur et indiquer par exemple : ```http://localhost:8180```

---
---
7.PARAMETRAGE de Imperihome

Ajouter depuis l'application Imperihome un systeme "Imperihome Standard System"

Indiquer en url en fonction de l'adresse de votre serveur ISS-DOMO : ```http://192.168.0.26:8000/```

---
---
8.Mise a jour de ISS-DOMO

> cd /var/www/iss-domo/ && git pull

La configuration est conservée.


### License

ISS-Domo is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
