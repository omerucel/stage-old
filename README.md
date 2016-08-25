Docker konteynerlerinin nginx üzerinden sunulmasını sağlar.

# Gereksinimler

* php 7.0.0
* phalcon 3.0.0
* docker
* nginx

# Kurulum

MySQL üzerinde veritabanı oluşturup ilgili ortam ayarında
veritabanı bağlantı bilgileri tanımlanmalıdır. Ortam ayarları **configs/env/{APPLICATION_ENV}.php** dosyası
ile belirtilir.

Veritabanı senkronizasyonu için aşağıdaki komut çalıştırılır:
```bash
$ php bin/console.php migrations:migrate
```

docker, docker-compose ve nginx uygulamalarının yolu ortam ayarlarında **docker_compose_bin**, **docker_bin** ve **nginx_bin**
anahtarları üzerinden belirtilmelidir.

# Demo

[![Demo](http://img.youtube.com/vi/pX1DBrwaHJU/0.jpg)](http://www.youtube.com/watch?v=pX1DBrwaHJU)

# Yapılacak İşler

* Phalcon gereksiniminin kaldırılması
* Sunucu yönetimi kısmının iyileştirilmesi.
* ..