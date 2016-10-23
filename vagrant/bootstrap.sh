#! /bin/bash

setenforce Permissive
adduser www-data
export APPLICATION_ENV="development"
rm -f /etc/profile.d/vagrant.sh
echo 'export APPLICATION_ENV="development"' > /etc/profile.d/vagrant.sh
chmod 755 /etc/profile.d/vagrant.sh

sed -i 's/.*enabled=.*/enabled=0/g' /etc/yum/pluginconf.d/fastestmirror.conf
curl -LOsS https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
curl -LOsS http://rpms.remirepo.net/enterprise/remi-release-7.rpm
rpm -Uvh remi-release-7.rpm epel-release-latest-7.noarch.rpm
rm -rf remi-release-7.rpm epel-release-latest-7.noarch.rpm
yum --enablerepo=remi,remi-php70 install -y  --nogpgcheck \
        git-core \
        nginx \
        unzip \
        gearmand \
        memcached \
        mariadb-server \
        mariadb \
        php \
        php-opcache \
        php-apc \
        php-pear \
        php-mysql \
        php-pecl-memcached \
        php-xml \
        php-gd \
        php-mbstring \
        php-mcrypt \
        php-fpm \
        php-gearman \
        php-json \
        php-curl

TMPDIR=/tmp yum clean metadata
TMPDIR=/tmp yum clean all

curl -LsS https://getcomposer.org/installer | php
mv composer.phar /usr/bin/composer

rm -f /etc/php-fpm.d/www.conf
cp "/vagrant/files/www.conf" /etc/php-fpm.d/www.conf

rm -f /etc/php.d/php-development.ini
cp "/vagrant/files/php-development.ini" /etc/php.d/php-development.ini

rm -rf /etc/nginx/nginx.conf
cp "/vagrant/files/nginx.conf" /etc/nginx/nginx.conf

systemctl enable php-fpm
systemctl start php-fpm

systemctl enable nginx
systemctl start nginx

systemctl enable gearmand
systemctl start gearmand

systemctl enable memcached
systemctl start memcached

rm -rf /etc/my.cnf
cp /vagrant/files/my.cnf /etc/my.cnf
systemctl enable mariadb
systemctl start mariadb
mysqladmin -u root password "root"
mysql -u root -proot -e "GRANT ALL ON *.* TO root@'%' IDENTIFIED BY 'root' WITH GRANT OPTION;"
mysql -u root -proot -e "CREATE DATABASE stage CHARACTER SET 'utf8';"

curl -LOsS https://github.com/docker/compose/releases/download/1.8.1/docker-compose-Linux-x86_64
curl -LOsS https://get.docker.com/builds/Linux/x86_64/docker-latest.tgz
tar -zxf docker-latest.tgz
mv docker/* /usr/bin/
mv docker-compose-Linux-x86_64 /usr/bin/docker-compose
rm -rf docker
rm -rf docker-latest.tgz
rm -rf docker-compose-Linux-x86_64
chmod +x /usr/bin/docker*

cp /vagrant/files/docker.service /etc/systemd/system/docker.service
systemctl enable docker.service
systemctl start docker

echo "www-data ALL=NOPASSWD: /usr/sbin/nginx, /usr/bin/docker, /usr/bin/docker-compose" >> /etc/sudoers

cd /data/project
/usr/bin/composer update --prefer-source -o --no-progress
php bin/console.php migrations:migrate -n