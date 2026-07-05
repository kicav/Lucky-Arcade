FROM mcr.microsoft.com/devcontainers/php:1-8.3-bookworm

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        sqlite3 \
        libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

USER vscode
