FROM alpine:3.7

RUN apk add --no-cache \
	apache2 \
	php7 \
	php7-apache2 \
	php7-mysqlnd \
	php7-pdo_mysql \
	php7-opcache \
	php7-xml \
	php7-simplexml \
	php7-session \
	php7-iconv \
	php7-json \
	php7-curl \
	php7-dom

COPY entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/bin/sh", "/entrypoint.sh"]
