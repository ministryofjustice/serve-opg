#!/usr/bin/env bash
set -e
# this was added to try and resolve aliases set from ~/.bash_profile, but it didn't make a difference.
# Getting this working would mean we don't need to do ${DCOP_COMMAND} and can just run 'dcop'
shopt -s expand_aliases

PROJECT="$HOME/OPG/opg-digicop"
DCOP_COMMAND="${PROJECT}/env/local/run_dcop.sh"
SOURCE_COMMAND_TO_RUN=""

function setup_dcop_project() {
  ${DCOP_COMMAND} env_setup
}

function install_frontend() {
  ${DCOP_COMMAND} composer
  ${DCOP_COMMAND} frontend_node_setup
  ${DCOP_COMMAND} frontend_node_gen
}

function setup_dcop_command() {
    if [[ "$SHELL" == *"zsh"* ]]; then
    echo "alias dcop='${PROJECT}/env/local/run_dcop.sh'" >> ~/.zshrc
    SOURCE_COMMAND_TO_RUN="source ~/.zshrc"
  else
    echo "alias dcop='${PROJECT}/env/local/run_dcop.sh'" >> ~/.bash_profile
    SOURCE_COMMAND_TO_RUN="source ~/.bash_profile"
  fi
}

function env_up() {
  ${DCOP_COMMAND} up
}

function env_down() {
  ${DCOP_COMMAND} down
}

setup_dcop_command

setup_dcop_project

install_frontend

env_down
env_up

# this warms the app
curl -s http://localhost:8888 > /dev/null

echo "To begin using dcop toolkit, reopen your terminal or run this:"
echo "${SOURCE_COMMAND_TO_RUN}"
echo ""
echo "Project up! Browse to http://localhost:8888"
echo ""
