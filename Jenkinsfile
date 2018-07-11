#!/usr/bin/env groovy

// ###############################################################################
// This file is owned by WebOps. Do not merge to master without WebOps input first
// ###############################################################################

def dateString = (new Date()).format('YYYY-MM-dd')
def enablePushDockerImages = true
def slackChannel = '#opg-digicop-builds'
def githubUserPassCredentialsId = '9fb2f9f6-657a-463c-bc1d-2da04b886e41'

def getGitHubBranchUrl() {

    if(env.CHANGE_URL != null) {
      return env.CHANGE_URL;
    }
    def githubRepo = 'https://github.com/ministryofjustice/opg-digicop/'
    def githubBranchUrl = githubRepo + 'tree/' + getBranchName()
    return githubBranchUrl;
}

@NonCPS
def getCommitOwner() {
  if(env.CHANGE_AUTHOR_DISPLAY_NAME != null) {
    return env.CHANGE_AUTHOR_DISPLAY_NAME
  }
  return (sh(returnStdout: true, script: 'git show --no-patch --format="%an" HEAD')).trim()
}

@NonCPS
def getLastCommitMessage() {
    return sh(returnStdout: true, script: 'git log -1 --pretty=%B').trim()
}

// Because we can build both branches and pull requests, we don't want the release tag to container PR-95
// as it's not descriptive, instead we want the originating branch name i.e: SW-50. env.CHANGE_BRANCH contains this value if it's a PR being built.
def getBranchName() {
    if(env.CHANGE_BRANCH != null) {
        return env.CHANGE_BRANCH
    }
    return env.BRANCH_NAME
}

def getBaseSlackContent(currentResult) {
    // Slave: ${getSlaveHostname()}
    def blueOceanUrl = env.RUN_DISPLAY_URL
    def slackContent = """BUILD ${currentResult}
Branch: <${getGitHubBranchUrl()}|${getBranchName()}>
Build Number: <${env.BUILD_URL}|${env.BUILD_NUMBER}>
Urls: <${env.BUILD_URL}|Jenkins Classic> || <${blueOceanUrl}|Blue Ocean>
Commit Author: ${getCommitOwner()}
Commit Message: ${getLastCommitMessage()}
"""
    return slackContent
}

def getSlackColorCodeFromBuildResult(buildResult) {
  // SLACK STUFF
  colorCode = 'danger'
  if(buildResult == 'SUCCESS') {
    colorCode = 'good'
  } else if(buildResult == 'ABORTED') {
    colorCode = '#808080'
  }
  return colorCode;
}


pipeline {

  agent { label 'digicop_slave' } // run on slaves only

  environment {
    IS_CI = "true"
    DOCKER_REGISTRY = 'registry.service.opg.digital'

    FRONTEND_NGINX_IMAGE_NAME = 'opguk/digicop-frontend-nginx'
    FRONTEND_PHP_IMAGE = 'opguk/digicop-frontend-php'
    API_NGINX_IMAGE_NAME = 'opguk/digicop-api-nginx'
    API_PHP_IMAGE = 'opguk/digicop-api-php'

    DIGICOP_TAG = "${getBranchName()}__${dateString}__${env.BUILD_NUMBER}"

    FRONTEND_NGINX_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_NGINX_IMAGE_NAME}:${env.DIGICOP_TAG}"
    FRONTEND_PHP_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_PHP_IMAGE}:${env.DIGICOP_TAG}"

    API_NGINX_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_NGINX_IMAGE_NAME}:${env.DIGICOP_TAG}"
    API_PHP_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_PHP_IMAGE}:${env.DIGICOP_TAG}"
  }

  stages {

    stage('Clean') {
      steps {
        script {
            sh 'git reset --hard HEAD && git clean -fdx'
        }
      }
    }

    stage('Setup') {
      parallel {
        stage('Frontend Composer') {
          steps {
            script {
                sh 'docker-compose -f docker-compose.ci.yml build --no-cache composer'
                sh "docker-compose -f docker-compose.ci.yml run --rm composer"
            }
          }
        }

        stage('API Composer') {
          steps {
            script {
                sh 'docker-compose -f docker-compose.ci.yml build --no-cache api_composer'
                sh "docker-compose -f docker-compose.ci.yml run --rm api_composer"
            }
          }
        }

        stage('Notify Slack') {
          steps {
            script {
              slackContent = getBaseSlackContent('STARTED')
              echo slackContent
              slackSend(message: slackContent, color: '#FFCC00', channel: slackChannel)
              currentBuild.description = "Tag: ${DIGICOP_TAG}"
            }
          }
        }

        stage('Compile Assets') {
          steps {
            script {
              sh 'docker-compose -f docker-compose.ci.yml build --no-cache node'
              sh "docker-compose -f docker-compose.ci.yml run --rm -e CI_USER_ID=`id -u` node /entrypoint-setup.sh"
              sh "docker-compose -f docker-compose.ci.yml run --rm -e CI_USER_ID=`id -u` node /entrypoint-generate.sh"
            }
          }
        }
      } // parallel
    } // Phase 1

    stage('Static Analysis') {
      parallel {
        stage('Frontend PHPCS') {
          steps {
            script {
              try {
                sh 'docker-compose run --rm --name=phpcs qa phpcs src'
              } catch(e) {
                // Do Nothing. Let the build pass.
              }
            }
          }
        }

        stage('Frontend PHPStan') {
          steps {
            script {
              // todo - https://github.com/phpstan/phpstan-symfony
              try {
                sh 'docker-compose run --rm --name=phpstan qa phpstan analyse -l 4 src'
              } catch(e) {
                // Do Nothing. Let the build pass.
              }
            }
          }
        }

        stage('Frontend PHP Lint') {
          steps {
            script {
              try {
                sh 'docker-compose run --rm --name=phplint qa parallel-lint src web app tests'
              } catch(e) {
                // Do Nothing. Let the build pass.
              }
            }
          }
        }

        stage('Frontend PHP Security Checks') {
          steps {
            script {
              sh 'docker-compose run --rm --name=phpseccheck qa security-checker security:check'
            }
          }
        }

        stage('API PHPCS') {
          steps {
            script {
              try {
                sh 'docker-compose run --rm --name=api_phpcs api_qa phpcs src'
              } catch(e) {
                // Do Nothing. Let the build pass.
              }
            }
          }
        }

        stage('API PHPStan') {
          steps {
            script {
              // todo - https://github.com/phpstan/phpstan-symfony
              try {
                sh 'docker-compose run --rm --name=api_phpstan api_qa phpstan analyse -l 4 src'
              } catch(e) {
                // Do Nothing. Let the build pass.
              }
            }
          }
        }

        stage('API PHP Lint') {
          steps {
            script {
              try {
                sh 'docker-compose run --rm --name=api_phplint api_qa parallel-lint src web app tests'
              } catch(e) {
                // Do Nothing. Let the build pass.
              }
            }
          }
        }

        stage('API PHP Security Checks') {
          steps {
            script {
              sh 'docker-compose run --rm --name=api_phpseccheck api_qa security-checker security:check'
            }
          }
        }
      } // parallel
    } // Stage

    stage('Build')  {
      parallel {
        stage('Build frontend nginx') {
          steps {
            script {
              sh "docker build --no-cache -t ${env.FRONTEND_NGINX_IMAGE_FULL} -f ./docker/Dockerfile-nginx frontend"
            }
          }
        }

        stage('Build frontend php') {
          steps {
            script {
              dir('frontend') {
                sh "docker build --no-cache -t ${env.FRONTEND_PHP_IMAGE_FULL}  -f Dockerfile-php ."
              }
            }
          }
        }

        stage('Build api nginx') {
          steps {
            script {
              sh "docker build --no-cache -t ${env.API_NGINX_IMAGE_FULL} -f ./docker/Dockerfile-nginx frontend"
            }
          }
        }

        stage('Build api php') {
          steps {
            script {
              dir('frontend') {
                sh "docker build --no-cache -t ${env.API_PHP_IMAGE_FULL}  -f Dockerfile-php ."
              }
            }
          }
        }

      } // parallel
    } // Stage('Build')

    stage('Test') {
      parallel {
        stage('behat') {
          steps {
            script {
              sh 'docker-compose up -d frontend api'
              sh 'sleep 5 && docker-compose -f docker-compose.ci.yml run --rm --user=www-data behat'
            }
          }
        }

        stage('phpunit') {
          steps {
            script {
              sh "docker-compose -f docker-compose.ci.yml run --rm --user=www-data phpunit"
            }
          }
        }
      }
    } // Stage ('Test')

  } // stages

  post {
    always {

      echo "BUILD RESULT: ${currentBuild.currentResult}"

      script {

        slackContent = getBaseSlackContent(currentBuild.currentResult)

        // If it's an aborted build, we don't wnat to push anything.
        if(currentBuild.currentResult != 'ABORTED') {

              slackContent = """ ${slackContent}
Deploy Tag: ${DIGICOP_TAG}"""

            if(enablePushDockerImages) {
              withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: githubUserPassCredentialsId, usernameVariable: 'GIT_USERNAME', passwordVariable: 'GIT_PASSWORD']]) {
                sh '''
                  if ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${FRONTEND_NGINX_IMAGE_FULL} && ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${FRONTEND_PHP_IMAGE_FULL}; then
                    echo "FOUND DOCKER IMAGES - TAGGING AND PUSHING"
                    # git config tweak is due to a limitation on the jenkins branch sources (github) plugin
                    git config url."https://${GIT_USERNAME}:${GIT_PASSWORD}@github.com/".insteadOf "https://github.com/"
                    git tag ${DIGICOP_TAG}
                    git push origin ${DIGICOP_TAG}

                    docker push ${FRONTEND_NGINX_IMAGE_FULL}
                    docker push ${FRONTEND_PHP_IMAGE_FULL}
                    docker push ${API_NGINX_IMAGE_FULL}
                    docker push ${API_PHP_IMAGE_FULL}
                  else
                    echo "DOCKER IMAGES NOT FOUND - NO IMAGES TO PUSH"
                  fi
                  '''
              }
            }

            echo "SUCESSFUL PIPELINE - REMOVING IMAGES"

            // Clean up docker images
            sh '''
            docker rmi -f ${FRONTEND_NGINX_IMAGE_FULL} || true
            docker rmi -f ${FRONTEND_PHP_IMAGE_FULL} || true

            docker rmi -f ${API_NGINX_IMAGE_FULL} || true
            docker rmi -f ${API_PHP_IMAGE_FULL} || true

            docker-compose stop || true
            docker-compose rm -fv || true
            '''

            // Clean up docker networks
            sh 'docker network prune -f'

            echo slackContent
            slackSend(message: slackContent, color: getSlackColorCodeFromBuildResult(currentBuild.currentResult), channel: slackChannel)
        }
      } // script
    } // always
  } // post
} // Pipeline
