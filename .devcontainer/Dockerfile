# Docker Official Image. debian:bookworm-slim - Jul 12, 2026
FROM debian@sha256:60eac759739651111db372c07be67863818726f754804b8707c90979bda511df

# Editor-agnostic tooling. These binaries back the editor extensions and also run in CI, so every
# editor (VS Code, PhpStorm, Neovim, plain CLI) and CI share the same tools. Docker CLI and the
# Compose plugin come from the docker-outside-of-docker devcontainer feature.
RUN apt-get update && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        git \
        gnupg \
        sudo \
        shellcheck \
        yamllint \
        ansible-lint \
    && rm -rf /var/lib/apt/lists/*

# Terraform, from the HashiCorp apt repository.
RUN install -m 0755 -d /etc/apt/keyrings \
    && curl -fsSL https://apt.releases.hashicorp.com/gpg -o /etc/apt/keyrings/hashicorp.asc \
    && chmod a+r /etc/apt/keyrings/hashicorp.asc \
    && echo "deb [signed-by=/etc/apt/keyrings/hashicorp.asc] https://apt.releases.hashicorp.com bookworm main" > /etc/apt/sources.list.d/hashicorp.list \
    && apt-get update && apt-get install -y --no-install-recommends terraform \
    && rm -rf /var/lib/apt/lists/*

# actionlint, the GitHub Actions workflow linter.
ARG ACTIONLINT_VERSION=1.7.7
RUN curl -fsSL "https://github.com/rhysd/actionlint/releases/download/v${ACTIONLINT_VERSION}/actionlint_${ACTIONLINT_VERSION}_linux_$(dpkg --print-architecture).tar.gz" \
      | tar -xz -C /usr/local/bin actionlint

# Non-root user for the devcontainer.
ARG USERNAME=developer
ARG USER_UID=1000
ARG USER_GID=$USER_UID
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m -s /bin/bash $USERNAME \
    && echo "$USERNAME ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

# Default non-root for all entry paths; remoteUser only covers editor attach, not bare docker exec.
USER $USERNAME
