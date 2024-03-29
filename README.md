Projelerin docker ile nginx üzerinden sunulmasını sağlar. 

# Gereksinimler

* php 5.6.0+
* docker
* docker-compose 1.8.0+
* nginx
* php-fpm
* mysql

# Kurulum

## Ortam ayarlarının oluşturulması

Aşağıdaki komutla örnek ayarlar ilgili ortam ayarı için tanımlanabilir:
```bash
$ cp configs/env/development.php configs/env/$APPLICATION_ENV.php
```

Örnek ayar dosyası:
```php
<?php

$configs = include(__DIR__ . '/../global.php');
$configs['nginx_bin'] = '/usr/sbin/nginx';
$configs['docker_bin'] = '/usr/local/bin/docker';
$configs['docker_compose_bin'] = '/usr/local/bin/docker-compose';

/**
 * PDO Service Configs
 */
$configs['pdo'] = array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=stage;charset=utf8',
    'username' => 'user',
    'password' => 'password'
);

return $configs;

```

## Yazma izinleri

İlgili klasörlere aşağıdaki gibi php-fpm kullanıcısı için haklar tanımlanmalı, gerektiği durumlarda yazma izni verilmeli.
```
$ chown -R www-data lock nginx.conf.d log tmp websites
$ chmod -R 775 lock nginx.conf.d log tmp websites
```

## Bağımlı kütüphanelerin kurulması

Bağımlı PHP kütüphaneleri aşağıdaki komut ile kurulabilir:
```bash
$ composer update
```

## Veritabanının hazırlanması

Veritabanı senkronizasyonu için aşağıdaki komut çalıştırılır:
```bash
$ php bin/console.php migrations:migrate
```

## Docker ve Nginx izinleri

sudoers dosyasına **php-fpm** servisinin çalıştığı kullanıcı için aşağıdaki tanımlama eklenmeli:
```bash
www-data ALL=NOPASSWD: /usr/sbin/nginx, /usr/local/bin/docker, /usr/local/bin/docker-compose
```

## Sanal sunucu ayarlarının tanıtılması

Nginx ayar dosyasına proje dizinindeki nginx.conf.d dizini tanıtılmalı. 
```
include PROJECT_DIR/nginx.conf.d/*.conf;
```

## Projenin çalışması için gerekli nginx ayarları

```
server {
        listen 80;
        listen [::]:80;

        client_max_body_size 8M;
        root PROJECT_DIR/public;
        index index.html index.php;
        server_name stage.example.com;

        location / {
                try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_param APPLICATION_ENV production;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
        }
}
```


## Docker ve Docker Compose kurulumu

Bazı dağıtımlarda paket yöneticisi ile eski versiyonlar kurulabiliyor. Bu yüzden aşağıdaki ilgili adreslerden
son versiyonun kurulması önerilir:

* https://docs.docker.com/engine/installation/binaries/
* https://github.com/docker/compose/releases/

## Docker servisinin çalıştırılması

Aşağıdaki komutla çalıştırılabilir. Kurulum yaptığınız dizine göre dockerd yolu değişebilir.
```bash
$ sudo /usr/local/bin/dockerd &
```

# Kullanım

İlk kurulum sonrası panel kullanıcı adı **admin@admin.com** şifre ise **admin** şeklindedir.

Uygulama ile oluşturulan her bir proje Docker projesi olarak tanımlanır. Bu projeye ait **Dockerfile** ve 
**docker-compose.yml** isimli dosyalar özel dosyalardır. Bu isimleri kullanarak, ilgili özel dosyalar
(ve diğer harici dosyalar) proje ekleme/güncelleme ekranından tanımlanabilir.

Projeye eklenen dosyalar aynı dizinde bulunmaktadır. **Dockerfile** için **ADD**, **COPY** gibi komutlarda ve
**docker-compose.yml** için **build:** gibi ayarlarda bu göz önünde bulundurulmalıdır. 

Her bir projenin sunucu ekranında **Yeniden Kur**, **Başlat**, **Durdur** işlemleri arkaplanda docker-compose komutunu
projeye bağlı docker-compose.yml dosyası için çalıştırmaktadır.

# Yapılacak İşler

* [X] Phalcon gereksiniminin kaldırılması
* [X] Sunucu yönetimi kısmının iyileştirilmesi.
* [X] Docker konteyner logları görüntülenebilmeli
* [X] Proje bazlı izin sistemi
* [X] İlk docker çalıştırma işlemi arkaplana atılabilir.
* [X] Tema iyileştirilmeli.
* [X] nginx sanal sunucu portu otomatik olarak atanmalı.
* [X] Arkaplanda çalışan komut durumu panelden takip edilebilmeli.
* [ ] Arkaplanda çalışan komut bilgisi panelden takip edilebilmeli.
* [ ] Projeye ait çalışan komut geçmişine ulaşılabilmeli.
* [ ] Slack bildirimleri eklenmeli.
* [ ] Hipchat bildirimleri eklenmeli.
* [ ] Giriş formu güvenlik önlemleri
* [ ] Tarayıcı üzerinden SSH bağlantısı yapılabilmeli.