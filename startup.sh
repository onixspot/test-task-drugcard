#!/usr/bin/env bash

app_dir='/app'
ssl_dir="${app_dir}/config/ssl"

install_deps() {
  [ ! -d ./vendor ] &&
    symfony composer install
  symfony completion bash >/etc/bash_completion.d/symfony &&
    symfony console completion bash >/etc/bash_completion.d/console &&
    echo '. /etc/bash_completion' >>/root/.profile
}

create_ssl_certificates() {
  local crt key pem

  [ -d "${ssl_dir}" ] &&
    return

  crt="${HOSTNAME}.crt"
  key="${HOSTNAME}.key"
  pem="${HOSTNAME}.pem"

  mkdir -p "${ssl_dir}"
  cd "${ssl_dir}" || {
    echo -e "\e[32m[ERROR] '${ssl_dir}': no such file or directory\e[0m"
    exit 1
  }

  openssl req -x509 -out "$crt" -keyout "$key" \
    -newkey rsa:2048 -nodes -sha256 \
    -subj "/CN=${HOSTNAME}" -extensions EXT -config <(
      cat <<EOF
[dn]
CN=${HOSTNAME}
[req]
distinguished_name = dn
[EXT]
subjectAltName=DNS:${HOSTNAME}
keyUsage=digitalSignature
extendedKeyUsage=serverAuth
EOF
    )
  cat "$crt" "$key" >"$pem"
  cp "$crt" /usr/local/share/ca-certificates/
  update-ca-certificates
  cd - || return
}

configure_web_server() {
  cp "${ssl_dir}/${HOSTNAME}.pem" /docker-entrypoint.d/
  cat <${ssl_dir}/../unit.json |
    jq '.listeners["*:443"].tls.certificate |= "\(env.HOSTNAME).pem"' |
    cat >/docker-entrypoint.d/unit.json
  unitd --control unix:/var/run/control.unit.sock
  unitctl -s /var/run/control.unit.sock import /docker-entrypoint.d/
  kill -TERM "$(cat /var/run/unit.pid)"
  rm -rf /docker-entrypoint.d/*
}

configure_database() {
  local host=db \
    port=3306

  echo -e '\e[33mDatabase health checking ...\e[0m'

  while true; do
    if (echo 2>/dev/null >/dev/tcp/$host/$port); then
      echo -e '\n\e[32m[Ok] - Database\e[0m'
      break
    fi
    echo -en .
    sleep 1
  done

  bin/console doctrine:database:create --if-not-exists
  bin/console doctrine:migrations:migrate --allow-no-migration --no-interaction
}

configure_xdebug() {
  local ini_path

  ini_path="$(php --ini | jq -rR 'capture("(?<path>.+xdebug[^,]+),"; "g") | .path')"

  [[ -n "${XDEBUG_CONFIG}" ]] &&
    [[ $(cat <"$ini_path" | wc -l) -eq 1 ]] &&
    {
      echo -e "\e[33mInstalling xdebug extension\e[0m"
      echo "${XDEBUG_CONFIG}" |
        jq -rR 'split("\\s"; null) | .[] | . = "xdebug." + .' |
        cat >>"$ini_path"
      echo -e "\e[32mXdebug extension was plugged!\e[0m"
    }

}

install_deps
create_ssl_certificates
configure_web_server
configure_database

configure_xdebug

unitd --no-daemon --control unix:/var/run/control.unit.sock
