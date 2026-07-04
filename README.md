# Laravel React Docker Starterkit

<div align="center">

[![Test Status](https://github.com/Ricardo-van-Aken/starterkit.laravel-react-docker/actions/workflows/test-locally.yml/badge.svg?branch=main)](https://github.com/Ricardo-van-Aken/starterkit.laravel-react-docker/actions/workflows/test-locally.yml)
[![Laravel 12](https://img.shields.io/badge/laravel-12.x-ff2d20?logo=laravel)](https://packagist.org/packages/laravel/framework)
[![React 19](https://img.shields.io/badge/react-19-61dafb?logo=react)](https://react.dev)
[![shadcn/ui](https://img.shields.io/badge/shadcn%2Fui-000000?logo=shadcnui&logoColor=white)](https://ui.shadcn.com)
[![Spatie Permissions](https://img.shields.io/badge/spatie-permissions-%23ec5975.svg?logo=spatie&logoColor=white)](https://spatie.be/docs/laravel-permission)
[![Pest](https://img.shields.io/badge/Pest-Testing-000000?logo=pest&logoColor=white)](https://pestphp.com)
[![Larastan](https://img.shields.io/badge/larastan-level%2010-4f5b93.svg?logo=phpstan&logoColor=white)](https://github.com/larastan/larastan)
<br>
[![PHP 8.2+](https://img.shields.io/badge/php-%3E%3D%208.2-8892bf?logo=php)](https://www.php.net)
[![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?logo=docker&logoColor=white)](https://www.docker.com)
[![Terraform](https://img.shields.io/badge/terraform-%23623CE4.svg?logo=terraform&logoColor=white)](https://www.terraform.io)
[![Ansible](https://img.shields.io/badge/ansible-%23EE0000.svg?logo=ansible&logoColor=white)](https://www.ansible.com)
[![License](https://img.shields.io/github/license/Ricardo-van-Aken/starterkit.laravel-react-docker)](LICENSE)
<!-- [![Spatie Activitylog](https://img.shields.io/badge/spatie-activitylog-%23ec5975.svg?logo=spatie&logoColor=white)](https://spatie.be/docs/laravel-activitylog)
[![Cashier Mollie](https://img.shields.io/badge/cashier-mollie-%23ff5e13.svg?logo=mollie&logoColor=white)](https://github.com/mollie/laravel-cashier-mollie) -->
</div>

A production-ready starter kit for building multi-tenant SaaS web applications with Laravel, Inertia and React. This starterkit focuses on delivering a consistent developer experience across local and production environments by combining containerised development with infrastructure-as-code deployment.

## Quick Start

### Prerequisites

- Linux, macOS, or Windows with WSL2
- Bash 4.3 or newer (the scripts use namerefs, which older Bash — notably the 3.2 shipped by default on some systems — does not support)
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose V2](https://docs.docker.com/compose/install/)

### Installation & Local Development

```bash
git clone https://github.com/Ricardo-van-Aken/starterkit.laravel-react-docker.git starterkit
cd starterkit/website
./run_env.sh local-volume
./migrate-db.sh --seed --class=RequiredDataSeeder
```

> [!IMPORTANT]
> **First Build Note**: The initial execution of `./run_env.sh` may take several minutes as it downloads base images and builds the application containers. Subsequent starts will be significantly faster.

Application will be available at: https://localhost

### Environment Modes

The `./run_env.sh` script supports several modes to suit different development and deployment needs:

| Mode | Description | Use Case |
| :--- | :--- | :--- |
| **`local-bindmount`** | Uses dev image with host bind mounts for real-time code syncing | Rapid local development (Frontend & Backend) |
| **`local-volume`** | Uses dev image with Docker volumes | CI/CD runners and local automated testing |
| **`local-production`** | Runs production image (no dev dependencies) locally with dummy data | Pre-flight check for production image behavior |
| **`dev`** | Targets remote dev environment with dev image | Remote development and testing on real infra |
| **`staging`** | Targets remote staging environment with production image | QA and pre-production validation |
| **`production`** | Targets production environment | Final deployment |

### Useful Commands

Check `website/commands.sh` for a cheat sheet of common commands (migrations, seeding, testing, etc.).

## Features

- **Modern Tech Stack**: Laravel 12, React 19, and Inertia.js.
- **Multi-tenant SaaS Foundation**: Pre-configured architecture for multi-tenancy using Spatie Laravel Permissions and Laravel Cashier.
- **Full Dockerised Stack**: A complete infrastructure ready for local development, consisting of PHP-FPM, Nginx, MySQL, Redis, and a Certificate generator.
- **High-Performance Docker Architecture**: All images are optimized for minimal size and maximum build speed. This includes surgical file pruning (e.g., removing JS/CSS sources after compilation), multi-stage builds to reduce dependencies in final images, and optimised build order to maximise Docker layer caching.
- **Environment Management**: Robust `run_env.sh` script to manage different environments (local, dev, staging, production) with ease.
- **Quality Assurance**: Pre-defined workflows for automated testing (Pest PHP), static analysis (Larastan), and CI/CD consistency.
- **Architecture**: Enforced architectural standards using Pest Architectural tests.
- **Security**: Pre-configured SSL support via Certbot/Let's Encrypt. Configuration of the infrastructure to run docker as non-root user. Small docker images reducing the attack surface of the application.

### Project Structure

The project is organised into three main areas:
- **`website/`**: Contains the application source code and the local Docker environment.
    - `services/`: Dockerfile and service-specific configurations (Laravel, Nginx, Redis).
        - `laravel/laravel_root/`: The core Laravel application source code.
    - `docker/`: Environment files (`.env.*`) and secrets needed for docker compose (V2).
- **`deployment/`**: Infrastructure-as-code (Terraform/Ansible) for cloud deployment.
- **`.github/`**: CI/CD workflows for automated testing and image building.

## Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | PHP 8.2+, Laravel 12, Authentication (Fortify), Authorisation (Spatie Permissions) |
| **Frontend** | React 19, Inertia.js, TypeScript, Tailwind CSS 4, shadcn/ui |
| **Infrastructure** | Docker, Docker Compose V2, PHP-FPM, Nginx, MySQL 8, Redis |
| **Tooling** | Vite, Pest PHP, PHPStan (Larastan), ESLint, Prettier |

## Documentation

More specific technical documentation can be found in the `docs/` folder (coming soon).