# DigiCop
Symfony 3.4 + PHP 7.2

# Local install

## Only first time

Software to download and install
  *  [docker](https://docs.docker.com/install/)
  *  [docker-compose](https://docs.docker.com/compose/install/)
  *  [git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

# Installing digicop project

``` bash
git clone git@github.com:ministryofjustice/opg-digicop.git ~/OPG/opg-digicop
cd ~/OPG/opg-digicop
./setup.sh
```

Frontend [http://localhost:8888/](http://localhost:8888/)

API [http://localhost:8889/](http://localhost:8889/)

# DCOP Toolkit Commands

##### Setup
Under the hood, env_setup will run:
- dcop frontend_setup
- dcop frontend_gen
- dcop composer
- dcop api_composer

``` bash
dcop env_setup
```

## Composer
``` bash
dcop composer
dcop api_composer
```

## Compile Assets

##### Set up node.
You'll only need to do this once, or when you change package.json

``` bash
dcop frontend_node_setup
```

##### Generate node Assets
When you change the .scss content then re-run this
``` bash
dcop frontend_node_gen
```

## Run PHPUnit
``` bash
dcop phpunit
```

## Behat
``` bash
dcop behat
```

# Handy dcop commands
``` bash
dcop ps
```

## Get a shell in a container
``` bash
dcop shell frontend
dcop shell frontend_php
dcop shell api
dcop shell api_php
dcop shell frontend_composer
dcop shell api_composer
dcop shell node
dcop shell behat
```

## Cache delete
``` bash
docker-compose exec php /scripts/cache-clear.sh
```


## Pull and view digicop code updates

    cd ~/www/opg-digicop
    docker-compose stop
    git pull origin master
    docker-compose up --build

 Check [http://localhost:8082/](http://localhost:8082/)

  In case of cache issues, run the following instead

     cd ~/www/opg-digicop
     docker-compose stop
     git pull origin master
     docker-compose build --force-rm --no-cache
     docker-compose up



## Toggle prod/dev mode on local env

    # prod mode
    docker exec dcphp touch /app/.enableProdMode
    # dev mode
    docker exec dcphp rm /app/.enableProdMode



## Other useful commands

    # watch logs
    docker logs dcnginx -f
    docker logs dcphp -f

    # sh into container (note: bash not installed)
    docker exec -it dcnginx sh
    docker exec -it dcphp sh

    # kill all running containers
    docker kill $(docker ps -q)
    # delete all stopped containers with
    docker rm $(docker ps -a -q)
    # delete all images
    docker rmi $(docker images -q)

# Dev notes
composer libs are updated with PHP 5.5.38
(in order to have them aligned with Digideps and make it easier to start with digideps component if needed )
