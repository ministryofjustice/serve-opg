#!/usr/bin/env groovy

def dateString = (new Date()).format('YYYY-MM-dd')

def getGitHubBranchUrl() {

    if(env.CHANGE_URL != null) {
      return env.CHANGE_URL;
    }

    def githubRepo = 'https://github.com/ministryofjustice/opg-sirius/'
    def githubBranchUrl = githubRepo + 'tree/' + getSiriusBranchName()
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

  stages {

    stage('Clean') {
      steps {
        script {
            sh 'git reset --hard HEAD && git clean -fdx'
        }
      }
    }

    stage('Init')  {

      parallel {
        stage('Notify Slack') {
          steps {
            script {
              slackContent = getBaseSlackContent('STARTED')
              echo slackContent
              // @todo - make this a variable
              slackSend(message: slackContent, color: '#FFCC00', channel: '#opg-digicop-builds')
              currentBuild.description = "Tag: ${SIRIUS_NEW_TAG}"
            }
          }
        }

        stage('Composer') {
          steps {
            script {
                dir('frontend') {
                  sh 'docker-compose run composer'
                }
            }
          }
        }

        stage('Compile Assets') {
          steps {
            script {
              dir('frontend') {
                sh 'docker-compose run node /entrypoint-setup.sh'
                sh 'docker-compose run node /entrypoint-generate.sh'
              }
            }
          }
        }

        stage('Build frontend nginx') {
          steps {
            script {
                sh "docker build --no-cache -t ${env.FRONTEND_NGINX_IMAGE_FULL}  -f Dockerfile-php ./frontend"
            }
          }
        }
      } // parallel
    } // Stage('Init')

    stage('Build frontend php') {
      steps {
        script {
          sh "docker build --no-cache -t ${env.FRONTEND_PHP_IMAGE_FULL}  -f Dockerfile-php ./frontend"
        }
      }
    }

  } // stages

  // post {
  //   always {
  //       slackContent = getBaseSlackContent(currentBuild.currentResult)
  //
  //       // If it's an aborted build, we don't wnat to push anything.
  //       if(currentBuild.currentResult != 'ABORTED') {
  //
  //             slackContent = """ ${slackContent}
  //             Deploy Tag: ${SIRIUS_NEW_TAG}"""
  //
  //             if(enablePushDockerImages) {
  //               withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: githubUserPassCredentialsId, usernameVariable: 'GIT_USERNAME', passwordVariable: 'GIT_PASSWORD']]) {
  //                   sh '''
  //
  //                   if ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${FRONTEND_NGINX_IMAGE_FULL} && ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${FRONTEND_PHP_IMAGE_FULL}; then
  //
  //                       echo "FOUND DOCKER IMAGES - TAGGING AND PUSHING"
  //                       # git config tweak is due to a limitation on the jenkins branch sources (github) plugin
  //                       git config url."https://${GIT_USERNAME}:${GIT_PASSWORD}@github.com/".insteadOf "https://github.com/"
  //                       git tag ${DIGICOP_TAG}
  //                       git push origin ${DIGICOP_TAG}
  //
  //                       docker push ${FRONTEND_NGINX_IMAGE_FULL}
  //                       docker push ${FRONTEND_PHP_IMAGE_FULL}
  //                   else
  //                       echo "DOCKER IMAGES NOT FOUND - NO IMAGES TO PUSH"
  //                   fi
  //                   '''
  //               }
  //
  //             }
  //
  //           // Only when it's a successfull pipeline run, we clean it up. This means bad builds can be debugged on the CI box
  //           echo "SUCESSFUL PIPELINE - REMOVING IMAGES"
  //           dir(env.CI_WORKSPACE_DIR) {
  //               // Clean up docker images
  //               sh '''
  //               docker rmi -f ${FRONTEND_NGINX_IMAGE_FULL}
  //               docker rmi -f ${FRONTEND_PHP_IMAGE_FULL}
  //               docker-compose down || true
  //               docker-compose rm -fv || true
  //               '''
  //               // Clean up docker networks
  //               sh 'docker network prune -f'
  //               sh 'docker rmi $(docker images --filter "dangling=true" -q --no-trunc)'
  //           }
  //
  //           // SLACK STUFF
  //           // @todo - make this a variable
  //           slackChannel = '#opg-digicops-builds'
  //
  //           colorCode = 'danger'
  //           if(currentBuild.currentResult == 'SUCCESS') {
  //             colorCode = 'good'
  //           } else if(currentBuild.currentResult == 'ABORTED') {
  //             colorCode = '#808080'
  //           }
  //
  //           echo slackContent
  //           slackSend(message: slackContent, color: colorCode, channel: slackChannel)
  //
  //
  //       }
  //
  //
  //   }
  // }



} // Pipeline
