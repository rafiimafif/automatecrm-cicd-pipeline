// ==============================================
// automateCRM — Jenkins Pipeline
// Declarative pipeline with Docker agent
// ==============================================

pipeline {
    agent any

    environment {
        APP_NAME        = 'automatecrm'
        SONAR_TOKEN     = credentials('sonarqube-token')
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
        disableConcurrentBuilds()
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timestamps()
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
                script {
                    env.GIT_COMMIT_SHORT = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
                }
            }
        }

        stage('Install Dependencies') {
            parallel {
                stage('Composer') {
                    agent {
                        docker {
                            image 'composer:2.7'
                            reuseNode true
                            args '--entrypoint=""'
                        }
                    }
                    steps {
                        sh 'composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=ext-gd'
                    }
                }
                stage('NPM') {
                    agent {
                        docker {
                            image 'node:18-alpine'
                            reuseNode true
                        }
                    }
                    steps {
                        sh 'npm ci --no-audit'
                    }
                }
            }
        }

        stage('Code Quality') {
            parallel {
                stage('Laravel Pint') {
                    agent {
                        docker {
                            image 'php:8.2-cli'
                            reuseNode true
                        }
                    }
                    steps {
                        sh 'vendor/bin/pint --test'
                    }
                }
                stage('Build Assets') {
                    agent {
                        docker {
                            image 'node:18-alpine'
                            reuseNode true
                        }
                    }
                    steps {
                        sh 'npm run build'
                    }
                }
            }
        }

        stage('Test') {
            agent {
                docker {
                    image 'php:8.2-cli'
                    reuseNode true
                }
            }
            steps {
                sh '''
                    pecl install pcov && docker-php-ext-enable pcov
                    cp .env.example .env
                    php artisan key:generate
                    php artisan config:clear
                    mkdir -p tests/results
                    vendor/bin/phpunit \
                      --coverage-clover=coverage.xml \
                      --coverage-html=coverage \
                      --log-junit=tests/results/junit.xml \
                      -d pcov.enabled=1 \
                      -d pcov.directory=.
                '''
            }
            post {
                always {
                    junit allowEmptyResults: true, testResults: 'tests/results/*.xml'
                    publishHTML(target: [
                        allowMissing: true,
                        alwaysLinkToLastBuild: true,
                        keepAll: true,
                        reportDir: 'coverage',
                        reportFiles: 'index.html',
                        reportName: 'Code Coverage'
                    ])
                }
            }
        }

        stage('SonarCloud Analysis') {
            agent {
                docker {
                    image 'sonarsource/sonar-scanner-cli:11'
                    reuseNode true
                    args '--entrypoint="" -e SONAR_USER_HOME=/tmp/.sonar'
                }
            }
            steps {
                withSonarQubeEnv('SonarCloud') {
                    sh '''
                        sonar-scanner \
                          -Dsonar.organization=rafiimafif \
                          -Dsonar.php.coverage.reportPaths=coverage.xml \
                          -Dsonar.token=${SONAR_TOKEN}
                    '''
                }
            }
        }

        stage('Quality Gate') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Build Docker Image') {
            when {
                expression { env.GIT_BRANCH == 'master' || env.GIT_BRANCH == 'origin/master' }
            }
            steps {
                sh 'docker compose build'
            }
        }

        stage('Security Scan') {
            when {
                expression { env.GIT_BRANCH == 'master' || env.GIT_BRANCH == 'origin/master' }
            }
            steps {
                sh '''
                    docker run --rm \
                      -v /var/run/docker.sock:/var/run/docker.sock \
                      ghcr.io/aquasecurity/trivy:latest image \
                      --severity HIGH,CRITICAL \
                      --exit-code 0 \
                      automatecrm-app
                '''
            }
        }

        stage('Deploy to Staging') {
            when {
                expression { env.GIT_BRANCH == 'master' || env.GIT_BRANCH == 'origin/master' }
            }
            steps {
                sh '''
                    docker compose up -d --remove-orphans
                    docker compose exec -T app php artisan migrate --force
                    docker compose exec -T app php artisan config:cache
                    docker compose exec -T app php artisan route:cache
                    echo "Deploy completed: $(date)"
                '''
            }
        }
    }

    post {
        success {
            echo "Pipeline completed successfully for ${APP_NAME} (${GIT_COMMIT_SHORT})"
        }
        failure {
            echo "Pipeline failed for ${APP_NAME} (${GIT_COMMIT_SHORT})"
        }
        always {
            deleteDir()
        }
    }
}
