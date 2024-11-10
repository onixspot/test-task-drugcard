FROM unit:php AS app
#FROM unit:1.33.0-php8.3
LABEL authors="mrx"

RUN apt update -qq && apt install -qq -y \
  unzip \
  curl \
  git \
  jq \
  wget \
  bash-completion

ADD --chmod=0755 https://github.com/nginx/unit/releases/download/1.33.0/unitctl-1.33.0-x86_64-unknown-linux-gnu /usr/bin/unitctl

RUN mv "${PHP_INI_DIR}"/php.ini-production "${PHP_INI_DIR}"/php.ini

RUN ( curl -sSLf https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o - || echo 'return 1' ) | sh -s \
  amqp \
  apcu \
  curl \
  http \
  intl \
  json \
  memcached \
  opcache \
  pdo_mysql \
  xdebug \
  zip

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
 && apt install -qq -y symfony-cli

VOLUME [ "/app", "/var/lib/unit" ]
WORKDIR "/app"

COPY . .

EXPOSE 80 443

CMD [ "./startup.sh" ]


FROM app AS bg

RUN apt update -qq && apt install -qq -y supervisor
RUN mkdir -p /var/log/supervisor
COPY config/worker.ini /etc/supervisor/supervisor.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisor.conf"]
