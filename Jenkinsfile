// ==============================================
// automateCRM — Jenkins Pipeline
// Declarative pipeline with Docker agent
// ==============================================

pipeline {
    agent any

    environment {
        APP_NAME        = 'automatecrm'
        REGISTRY        = 'ghcr.io'
        IMAGE_NAME      = "${REGISTRY}/rafiimafif/automateCRM"
        DOCKER_CREDS    = credentials('docker-registry-creds')
        SONAR_TOKEN     = credentials('sonarqube-token')
        DEPLOY_SSH_KEY  = credentials('deploy-ssh-key')
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
                    env.IMAGE_TAG = "${IMAGE_NAME}:${GIT_COMMIT_SHORT}"
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
                        sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
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
                      --log-junit=tests/results/junit.xml \
                      -d pcov.enabled=1
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
                branch 'main'
            }
            steps {
                sh """
                    docker build -t ${IMAGE_TAG} -t ${IMAGE_NAME}:latest .
                """
            }
        }

        stage('Security Scan') {
            when {
                branch 'main'
            }
            steps {
                sh """
                    docker run --rm \
                      -v /var/run/docker.sock:/var/run/docker.sock \
                      aquasec/trivy:latest image \
                      --severity HIGH,CRITICAL \
                      --exit-code 0 \
                      ${IMAGE_TAG}
                """
            }
        }

        stage('Push Image') {
            when {
                branch 'main'
            }
            steps {
                sh """
                    echo ${DOCKER_CREDS_PSW} | docker login ${REGISTRY} -u ${DOCKER_CREDS_USR} --password-stdin
                    docker push ${IMAGE_TAG}
                    docker push ${IMAGE_NAME}:latest
                """
            }
        }

        stage('Deploy to Staging') {
            when {
                branch 'main'
            }
            steps {
                sshagent(credentials: ['deploy-ssh-key']) {
                    sh '''
                        ssh -o StrictHostKeyChecking=no deploy@${STAGING_HOST} << 'EOF'
                            cd /opt/automatecrm
                            docker compose pull
                            docker compose up -d --remove-orphans
                            docker compose exec -T app php artisan migrate --force
                            docker compose exec -T app php artisan config:cache
                            docker compose exec -T app php artisan route:cache
                            echo "Deploy completed: $(date)"
                        EOF
                    '''
                }
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
            cleanWs()
        }
    }
}
