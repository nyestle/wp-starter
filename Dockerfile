FROM wordpress:latest

RUN apt-get update && apt-get install -y \
    less \
    nano \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

EXPOSE 80
