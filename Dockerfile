FROM ubuntu:16.04
MAINTAINER Doug Banville <doug.banville@rte.ie>

VOLUME ["/var/www"]

RUN apt-get update && \
      apt-get dist-upgrade -y && \
      apt-get install -y \
      apache2 \
      php7.0 \
      php7.0-cli \
      libapache2-mod-php7.0 \
      php-apcu \
      php-xdebug \
      php7.0-gd \
      php7.0-json \
      php7.0-ldap \
      php7.0-mbstring \
      php7.0-mysql \
      php7.0-pgsql \
      php7.0-sqlite3 \
      php7.0-xml \
      php7.0-xsl \
      php7.0-zip \
      php7.0-soap \
      php7.0-opcache \
      composer \ 
      sox \
      libsox-fmt-mp3 \
      lame \
      php-curl \
      vim \
      ffmpeg \
      git make cmake gcc g++ libmad0-dev \
      libid3tag0-dev libsndfile1-dev libgd-dev libboost-filesystem-dev \
      libboost-program-options-dev \
      libboost-regex-dev \
      wget \
      make \
      cmake \
      && git clone https://github.com/bbcrd/audiowaveform.git \
      && cd audiowaveform \
      && wget https://github.com/google/googletest/archive/release-1.8.0.tar.gz \ 
      && tar xzf release-1.8.0.tar.gz \
      && ln -s googletest-release-1.8.0/googletest googletest \
      && ln -s googletest-release-1.8.0/googlemock googlemock \
      && mkdir build \
      && cd build \
      && cmake .. \
      && make install \
      && cd ..

COPY apache_default /etc/apache2/sites-available/000-default.conf
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite

EXPOSE 80
CMD ["/usr/local/bin/run"]