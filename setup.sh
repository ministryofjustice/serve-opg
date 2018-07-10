#!/usr/bin/env bash
set -ex

PROJECT="$HOME/OPG/opg-digicop"

function install_dcop() {
  cd $PROJECT/dcop_toolkit && composer install
}

function install_frontend() {
  dcop composer
}

function setup_dcop_command() {
  # make the new "dcop" binary
  sudo ln -sf "${PROJECT}/dcop_toolkit/vendor/bin/robo" /usr/local/bin/dcop
  sudo chown $UID /usr/local/bin/dcop
}

which dcop || install_dcop

setup_dcop_command

install_frontend
