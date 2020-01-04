# LAMP stack in docker

Based on https://doc.ubuntu-fr.org/docker_lamp

Fixed to have:
- php 7.4
- mariadb instead of mysql

Tested on ubuntu 19.10

# prerequisites

```
apt install docker.io docker-compose
```

# use it

start the _lamp_ stack with:

```
docker-compose up
```

For using the map, you need to edit www/tile.php and replace *YOUR_MAPBOX_TOKEN_HERE* with your mapbox token.

- Connect to your site:   [http://127.0.0.1:80](http://127.0.0.1:80)
- Connect to phpmyadmin:  [http://127.0.0.1:8080](http://127.0.0.1:8080)