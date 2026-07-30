# AGENTS.md

Guidance for coding agents working in this repository.

## Code style

- Keep comments terse and about the code. Explain the non-obvious *why*, not your reasoning process or the alternatives you considered.

## What this project is

A starter kit for a containerized web application: a **Laravel** backend serving a **React + TypeScript** frontend (built with Vite and Tailwind). Everything runs as Docker containers, orchestrated with Docker Compose for local development and remote environments, and is deployed to cloud infrastructure provisioned with Terraform and Ansible.

## Repository layout

- `website/` — the application and its Docker Compose orchestration.
  - `website/services/` — one directory per container image (each with its own `Dockerfile`).
  - `website/compose*.yaml` — base compose file plus overlays (dev, bindmount, devcontainer).
  - `website/docker/` — per-environment `.env` files and secrets.
  - `website/run_env.sh`, `stop_env.sh`, `migrate-db.sh` — lifecycle scripts.
- `deployment/` — infrastructure as code: `terraform/` (provisioning), `ansible/` (configuration), and `scripts/`.

## Services (`website/services/<service>`)

- `laravel` — the Laravel application (PHP-FPM) and the React/TypeScript frontend. The same image also runs the `queue_worker` and `scheduler` containers.
- `nginx` — web server and reverse proxy in front of the app.
- `mysqldb` — the local MySQL database. Remote environments use a managed database instead.
- `redis` — cache, queue, and session store.
- `certbot` — Let's Encrypt certificate issuance/renewal for remote environments.
- `certlocal` — TLS certificate generation for local development.

Detailed, service-specific documentation is intended to live in a `.md` file in each service's root directory (not yet created).

## Running the environment

- `cd website && ./run_env.sh <mode>` orchestrates Docker Compose. Modes: `local-volume`, `local-bindmount`, `local-production`, `dev`, `staging`, `production`.
- `run_env.sh` requires **Bash 4.3+** (it uses namerefs).
- `./stop_env.sh` tears the environment down; `./migrate-db.sh` runs database migrations.

## Devcontainers

There is a root devcontainer (`.devcontainer/`) and per-service devcontainers (`website/services/<service>/.devcontainer/`). The root devcontainer provides general tooling for driving the repository (Docker Compose, Bash, Git, infra CLIs); tooling specific to a service belongs in that service's devcontainer.

The team uses varying editors, so the container — not the editor — is the source of truth:

- Install the actual tools (CLIs, linters, formatters, language servers) in the `Dockerfile` so every editor and CI share the same versions. Editor extensions are thin clients over tools already in the image.
- Editor customizations (`customizations.vscode`, `customizations.jetbrains`) are optional per-editor conveniences, never required for a working environment. Provide them for the editors the team uses; do not add personal-preference or AI-assistant extensions.
- Consistency is enforced by CI and repo-level config (linters run in CI), not by editor settings.
- The devcontainer CLI (`devcontainer up`/`exec`) is the editor-agnostic entry point.

Conventions when creating or editing a devcontainer:

- Build from a local `Dockerfile`, base images pinned by digest with a dated comment.
- Use a generic non-root user named `developer`, not editor-specific names like `vscode`.
- Set `workspaceMount` and `workspaceFolder` explicitly, using `${localWorkspaceFolderBasename}`.
- Provide the Docker CLI, Compose, and host-socket access via the `docker-outside-of-docker` feature.

Prebuilds: `.github/workflows/prebuild-devcontainer.yml` auto-discovers every Dockerfile-based devcontainer and builds/pushes it (tagged `devcontainer-<name>`) on `main`, so a new one needs no workflow change. To reuse a prebuilt image as a local build cache, set `DEVCONTAINER_CACHE_IMAGE` in your environment and add `"cacheFrom": "${localEnv:DEVCONTAINER_CACHE_IMAGE}"` — kept out of the committed config so no registry is hardcoded.
