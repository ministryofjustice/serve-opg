# DigiCop protype
Symfony 3.4 PHP 5.5 version of Digicop.

Docker container config are temporary


## Install (only first time)

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

Beginning of the day
    
    cd ~/www/opg-digicop
    docker-compose up
    
 Check [http://localhost:8082/](http://localhost:8082/)
   
    
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
     
  instead   

## Other useful commands
    
    # watch logs
    docker exec -it dcphp bash -c "tail -f /var/log/app/*"
    docker exec -it dcnginx bash -c "tail -f /var/log/nginx/app*"

    # bash into container
    docker exec -it dcnginx bash
    docker exec -it dcphp bash
    
    # kill all running containers
    docker kill $(docker ps -q)
    # delete all stopped containers with 
    docker rm $(docker ps -a -q)
    # delete all images with 
    docker rmi $(docker images -q)
    
## Dev notes
composer libs are updated with PHP 5.5.38
