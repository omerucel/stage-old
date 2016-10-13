Projelerin docker ile nginx üzerinden sunulmasını sağlar. 

# Gereksinimler

* php 5.6.0
* docker
* nginx
* php-fpm

# Kurulum

## Ortam ayarlarının oluşturulması

Aşağıdaki komutla örnek ayarlar ilgili ortam ayarı için tanımlanabilir:
```bash
$ cp configs/env/development.php.sample configs/env/$APPLICATION_ENV.php
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
$ chown -R www-data nginx.conf.d log tmp websites
$ chmod -R 775 nginx.conf.d log tmp websites
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
include /data/projects/stage/nginx.conf.d/*.conf;
```

## Docker ve Docker Compose kurulumu

Bazı dağıtımlarda paket yöneticisi ile eski versiyonlar kurulabiliyor. Bu yüzden aşağıdaki ilgili adreslerden
son versiyonun kurulması önerilir:

https://docs.docker.com/engine/installation/binaries/
https://github.com/docker/compose/releases/

## Docker servisinin çalıştırılması

Aşağıdaki komutla çalıştırılabilir. Kurulum yaptığınız dizine göre dockerd yolu değişebilir.
```bash
$ sudo /usr/local/bin/dockerd &
```

# Kullanım

Uygulama ile oluşturulan her bir proje Docker projesi olarak tanımlanır. Bu projeye ait **Dockerfile** ve 
**docker-compose.yml** isimli dosyalar özel dosyalardır. Bu isimleri kullanarak, ilgili özel dosyalar
(ve diğer harici dosyalar) proje ekleme/güncelleme ekranından tanımlanabilir.

Form kontrolleri güncellenene kadar proje adı tanımlarken arada boşluk olmamasına ve özel karakter kullanmamaya
özen gösterilmesi gerekmektedir.

Projeye eklenen dosyalar aynı dizinde bulunmaktadır. **Dockerfile** için **ADD**, **COPY** gibi komutlarda ve
**docker-compose.yml** için **build:** gibi ayarlarda bu göz önünde bulundurulmalıdır. 

Her bir projenin sunucu ekranında **Başlat**, **Durdur** işlemleri arkaplanda docker-compose komutunu
projeye bağlı docker-compose.yml dosyası için çalıştırmaktadır.

Sunucu ekranındaki **Sanal Sunucu Dosyası** özelliği ise projenin nginx üzerinden sunulması için gerekli 
sanal sunucu dosyasını güncellemeye yardımcı olur. Bu bilgi güncellendiğinde nginx ayarları yeniden yüklenmektedir.
Bu dosya üzerinden hangi port numarasının, hangi sunucu adı ile yayınlanacağı nginx'e bildirilir.

## Örnek Sanal Sunucu Dosyası

```
server {
    listen 80;
    listen [::]:80;
    server_name project1.stage.com;
    
    location / {
        proxy_pass http://127.0.0.1:32768;
    }
}
```

# Yapılacak İşler

[X] Phalcon gereksiniminin kaldırılması
[X] Sunucu yönetimi kısmının iyileştirilmesi.
[X] Docker konteyner logları görüntülenebilmeli
[ ] Form işlemlerinde kontroller iyileştirilmeli.
[ ] Giriş formu güvenlik önlemleri
[ ] Proje bazlı izin sistemi
[ ] İlk docker çalıştırma işlemi arkaplana atılabilir.
[ ] Tarayıcı üzerinden SSH bağlantısı yapılabilmeli.