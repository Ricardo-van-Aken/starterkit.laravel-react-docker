# Laravel React Docker Starterkit

<div align="center">

[![Test Status](https://github.com/Ricardo-van-Aken/starterkit.laravel-react-docker/actions/workflows/test-locally.yml/badge.svg)](https://github.com/Ricardo-van-Aken/starterkit.laravel-react-docker/actions/workflows/test-locally.yml)
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

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Installation & Local Development

```bash
git clone https://github.com/Ricardo-van-Aken/starterkit.laravel-react-docker.git
cd website
./run_env.sh local-bindmount
./migrate-db.sh --seed --class=RequiredDataSeeder
```

> [!IMPORTANT]
> **First Build Note**: The initial execution of `./run_env.sh` may take several minutes as it downloads base images and builds the application containers. Subsequent starts will be significantly faster.

Application will be available at: https://localhost

### Environment Modes

The `./run_env.sh` script supports several modes to suit different development and deployment needs:

| Mode | Description | Use Case |
| :--- | :--- | :--- |
| **`local-bindmount`** | Uses bind mounts to sync files between host and container in real time | Active development and rapid iteration |
| **`local-volume`** | Uses Docker volumes for application files | Improved performance on macOS and Windows, but longer build time |
| **`mock-prod`** | Runs a production-like setup locally | Verifying production behavior before deployment |
| **`staging`** | Targets a staging environment configuration | Pre-production validation |
| **`production`** | Targets the production environment | Live deployment |

### Useful Commands

Check `website/commands.sh` for a cheat sheet of common commands (migrations, seeding, testing, etc.).

## Features

- **Modern Tech Stack**: Laravel 12, React 19, and Inertia.js.
- **Multi-tenant SaaS Foundation**: Pre-configured architecture for multi-tenancy using Spatie Laravel Permissions and Laravel Cashier.
- **Full Dockerised Stack**: A complete infrastructure ready for local development, including PHP-FPM, Nginx, MySQL, Redis.
- **Optimised Docker Build**: Parallelised Docker build process for faster local startup and CI execution.
- **Environment Management**: Robust `run_env.sh` script to manage different environments (local, staging, production) with ease.
- **Quality Assurance**: Pre-defined workflows for automated testing (Pest PHP), static analysis (Larastan), and CI/CD consistency.
- **Developer Experience**: Choice between `bindmount` (instant code reflection) and `volume` based development.
- **Type Safety**: Full TypeScript support in the frontend.
- **Architecture**: Enforced architectural standards using Pest Architectural tests.
- **Security**: Pre-configured SSL support via Certbot/Let's Encrypt.

### Project Structure

The project is organised into three main areas:
- **`website/`**: Contains the application source code and the local Docker environment.
- **`deployment/`**: Contains the architecture definition and deployment configurations for various environments.
- **`.github/`**: Contains the CI/CD workflows for automated testing and deployment.

## Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | PHP 8.2+, Laravel 12, Authentication (Fortify), Authorisation (Spatie Permissions) |
| **Frontend** | React 19, Inertia.js, TypeScript, Tailwind CSS 4, shadcn/ui |
| **Infrastructure** | Docker, Docker Compose, PHP-FPM, Nginx, MySQL 8, Redis |
| **Tooling** | Vite, Pest PHP, PHPStan (Larastan), ESLint, Prettier |

## Documentation

More specific technical documentation can be found in the `docs/` folder (coming soon).