# DigiCop
Symfony 3.4 + PHP 7.2

# Local install

## Only first time

Software to download and install
  *  [docker](https://docs.docker.com/install/)
  *  [docker-compose](https://docs.docker.com/compose/install/)
  *  [git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
  
Checkout repositories locally

    git clone git@github.com:ministryofjustice/opg-digicop.git ~/www/opg-digicop

Build

    cd ~/www/opg-digicop
    docker-compose build

## Run 
    
    cd ~/www/opg-digicop
    docker-compose up
    
 Check at [http://localhost:8082/](http://localhost:8082/)
   
    
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


## Composer
``` bash
docker-compose run composer
```

## Compile Assets

##### Set up node. 
You'll only need to do this once, or when you change package.json

``` bash
docker-compose run node /entrypoint-setup.sh
```

##### Compile Assets
``` bash
docker-compose run node /entrypoint-generate.sh
```

## Run PHPUnit

``` bash
docker-compose run phpunit
```

## Behat
``` bash
docker-compose run behat
```

## Cache delete
``` bash
docker-compose exec php /scripts/cache-clear.sh
```

alternative to delete cache and logs:
``` bash
docker-compose exec rm -rf /tmp/app-cache/* /tmp/app-logs/*
```

## Get a root shell

docker-compose exec nginx bash
docker-compose exec php bash

docker-compose run --entrypoint="bash" node
docker-compose run --entrypoint="bash" composer
docker-compose run --entrypoint="bash" behat
docker-compose run --entrypoint="bash" phpunit


# todo - 
dcop shell node
dcop shell php

## Toggle prod/dev mode on local env

    # prod mode
    docker-compose exec php touch /app/.enableProdMode
    # dev mode
    docker-compose exec php rm /app/.enableProdMode


## Watch logs
Use the main window where `docker-compose up` was launched

## Other useful commands
    
    # watch logs
    watch the main window
    
    # kill all running containers
    docker kill $(docker ps -q)
    # delete all stopped containers with 
    docker rm $(docker ps -a -q)
    # delete all images 
    docker rmi $(docker images -q)
    
## Dynamodb shell
Open the [web interface](http://localhost:8000/shell/) and run query. E.g. select data from session table

        {
            TableName: 'sessions'
        }
    
    
# Dev notes
composer libs are updated with PHP 5.5.38 
(in order to have them aligned with Digideps and make it easier to start with digideps component if needed )
