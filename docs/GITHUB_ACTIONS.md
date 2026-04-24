# GitHub Actions CI/CD Setup Guide

This project now includes a full CI/CD pipeline using GitHub Actions, providing a modern alternative to the Jenkins setup. The pipeline is defined in [ci-cd.yml](file:///c:/Users/rafii/Downloads/Project/automatecrm-cicd-pipeline/.github/workflows/ci-cd.yml).

## Workflow Stages
1.  **Code Quality**: Automated linting using Laravel Pint.
2.  **Frontend Build**: Parallel compilation of Vite assets.
3.  **Testing**: Comprehensive PHPUnit tests with MySQL and Redis service containers.
4.  **Security**: Dependency audits (Composer/NPM) and Docker image scanning (Trivy).
5.  **SonarCloud**: Deep code analysis and quality gate checks.
6.  **Docker Build**: Automatic image building and pushing to GitHub Container Registry (GHCR).
7.  **Deployment**: Automated deployment to your staging server via SSH.

## Required GitHub Secrets

To make the pipeline fully functional, you must add the following secrets in your GitHub repository settings (**Settings > Secrets and variables > Actions**):

### General
| Secret | Description |
|---|---|
| `GITHUB_TOKEN` | Automatically provided by GitHub (no setup needed). |

### SonarCloud
| Secret | Description |
|---|---|
| `SONAR_TOKEN` | Your SonarCloud analysis token. |

### Staging Deployment (SSH)
| Secret | Description |
|---|---|
| `STAGING_HOST` | The IP address or hostname of your staging server. |
| `STAGING_USER` | The SSH username for the staging server (e.g., `ubuntu`). |
| `STAGING_SSH_KEY` | Your private SSH key used to access the server. |
| `STAGING_PORT` | (Optional) SSH port if not default 22. |

## How to Trigger
- **On every push/PR**: The pipeline runs Lint, Build Assets, Test, and Security Scan.
- **On merge to `master` or `main`**: The pipeline also runs SonarCloud analysis, builds the Docker image, and deploys to the staging server.

## Viewing Results
Go to the **Actions** tab in your GitHub repository to see the live progress of each build and deployment.
