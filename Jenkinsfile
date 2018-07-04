#!/usr/bin/env groovy

pipeline {

  agent { label '!master' } // run on slaves only

  environment {
    IS_CI = "true"
    DOCKER_REGISTRY = 'registry.service.opg.digital'
    FRONTEND_NGINX_IMAGE_NAME = 'opguk/digicop-frontend-nginx'
    FRONTEND_PHP_IMAGE = 'opguk/digicop-frontend-php'

    DIGICOP_TAG = "${getBranchName()}__${dateString}__${env.BUILD_NUMBER}"

    FRONTEND_NGINX_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_NGINX_IMAGE_NAME}:${env.DIGICOP_TAG}"
    FRONTEND_PHP_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_PHP_IMAGE}:${env.DIGICOP_TAG}"

  }

}