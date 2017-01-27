FROM lsiobase/alpine
MAINTAINER jrowland

# install runtime packages
RUN \
 apk add --no-cache \
	fcgi \
  curl \
  curl-dev \
	php5 \
	php5-cli \
	php5-common \
	php5-cgi \
	php5-iconv \
	php5-json \
  php5-gd \
  php5-curl \
  php5-xml \
  php5-phar \
  php5-imap \
  php5-openssl \
  php5-pdo \
  php5-pdo_pgsql \
  php5-soap \
  php5-xmlrpc \
  php5-posix \
  php5-mcrypt \
  php5-gettext \
  php5-ldap \
  php5-ctype \
  php5-dom


# copy local files
COPY root/ /
COPY bin/ /plex-vera-app/bin

# ports and volumes
VOLUME /config

EXPOSE 32400 3480
