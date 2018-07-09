#!/usr/bin/env bash
set -ex

OPG_PATH="${HOME}/OPG"
DC_PATH="${OPG_PATH}/opg-digicop"
OPG_BACKUP_PATH="${HOME}/OPG-BACKUP"


function launchDockerForMac() {
  if (! docker stats --no-stream ); then
    # On Mac OS this would be the terminal command to launch Docker
    open /Applications/Docker.app
   #Wait until Docker daemon is running and has completed initialisation
  while (! docker stats --no-stream ); do
    # Docker takes a few seconds to initialize
    echo "Waiting for Docker to launch..."
    sleep 1
  done
  fi
}

function launchDockerForLinux() {
  docker info || (print "Docker is not running, please start it\nTry: sudo service docker restart\n" && exit 1)
}

function checkDockerInstall() {
    local IN_GROUP=$(groups | grep docker > /dev/null)$?
    if [ $IN_GROUP -ne 0 ]; then
      echo "You are not in the docker group. Adding you now."
      sudo usermod -aG docker $USER
      echo "Please close down your terminal. Upon reloading you'll be in the docker group"
      exit 1
    fi
}

function launchDocker() {
  unameOut="$(uname -s)"
  case "${unameOut}" in
    "Linux"*)
      checkDockerInstall
      launchDockerForLinux
      ;;

    "Darwin"*)
      launchDockerForMac
      ;;
    *)
      echo "Unable to detect OS type - please contact WebOps"
      exit 1
      ;;
esac
}

# If a backup directory doesn't exist already, back up to it. If it does exist already, leave it alone
function backupExistingOPGEnv() {
  if [ -d "${OPG_PATH}" ]; then
    if [ ! -d "$OPG_BACKUP_PATH" ]; then
        echo "OPG dir found, and OPG-BACKUP dir not found. Making a backup"
        mv ${OPG_PATH} ${OPG_BACKUP_PATH}
    fi
  fi
}

function setupRepo() {
  if [ ! -d "$DC_PATH" ]; then
    cd ${OPG_PATH}
    git clone -b master git@github.com:ministryofjustice/opg-digicop.git
  else
    cd ${DC_PATH}
    git checkout master
    git pull || true "[WARNING] Cannot pull latest master changes."
  fi
}

launchDocker

backupExistingOPGEnv

setupRepo


# make our workspace if it doesn't exist already (could be removed due to backup)
mkdir -p ${DC_PATH}

# get rid of opg command pointing to any pre-existing installations
unlink `which dc` || true

# make the new "opg" binary
sudo ln -sf "${DC_PATH}/devkit/bin/console" /usr/local/bin/dcop
sudo chown $UID /usr/local/bin/dcop

# test the new "opg" binary
dcop > /dev/null 2>&1 || (echo "Unable to invoke the opg command" && exit 1)

# install composer for each subProject
docker-compose run --rm composer
# dcop composer

# run grunt first because ingest fails upon env:up without grunt running
docker-compose run --rm node /entrypoint-setup.sh
# dcop frontend:setup

docker-compose run --rm node /entrypoint-generate.sh
# dcop frontend:generate

# spin up the environment - this will run injest
docker-compose up frontend php
#dc up

echo "SUCCESS! Browse to https://localhost:8082"
echo ""
