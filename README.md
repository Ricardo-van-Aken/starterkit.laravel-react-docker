# Laravel React Docker Starter Kit

A production-ready starter kit for building multi-tenant SaaS web applications with Laravel, Inertia and React. Fully containerised and designed for a smooth path from local development to production.

## Description

This starter kit provides a pre-configured environment for Laravel 12 applications using Inertia and React 19. It focuses on delivering a consistent developer experience across local and production environments by combining containerised development with infrastructure-as-code deployment.

The goal is to reduce setup time, enforce best practices, and provide a clear path from development to production.

The project is organised into three main areas:
- **`website/`**: Contains the application source code and the local Docker environment.
- **`deployment/`**: Contains the architecture definition and deployment configurations for various environments.
- **`.github/`**: Contains the CI/CD workflows for automated testing and deployment

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

## Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | PHP 8.2+, Laravel 12, Authentication (Fortify), Authorisation (Spatie Permissions) |
| **Frontend** | React 19, Inertia.js, TypeScript, Tailwind CSS 4, shadcn/ui |
| **Infrastructure** | Docker, Docker Compose, PHP-FPM, Nginx, MySQL 8, Redis |
| **Tooling** | Vite, Pest PHP, PHPStan (Larastan), ESLint, Prettier |

## Documentation

More specific technical documentation can be found in the `docs/` folder (coming soon).