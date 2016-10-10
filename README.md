Docker konteynerlerinin nginx üzerinden sunulmasını sağlar. Kişisel sunucularda ya da şirket içi
projeleri test etmek için kullanılabilir.

# Gereksinimler

* php 5.6.0
* docker
* nginx
* php-fpm

# Kurulum

MySQL üzerinde veritabanı oluşturup ilgili ortam ayarında veritabanı bağlantı bilgileri tanımlanmalıdır.
Ortam ayarları **configs/env/{APPLICATION_ENV}.php** dosyası ile belirtilir.

Veritabanı senkronizasyonu için aşağıdaki komut çalıştırılır:
```bash
$ php bin/console.php migrations:migrate
```

docker, docker-compose ve nginx uygulamalarının yolu ortam ayarlarında **docker_compose_bin**, **docker_bin**
ve **nginx_bin** anahtarları üzerinden belirtilmelidir.

# Kullanım

Bu uygulama içinde bir projeyi docker projesi olarak isimlendirebiliriz. Bu projeye ait **Dockerfile** ve 
**docker-compose.yml** isimli dosyalar özel dosyalardır. Bu isimleri kullanarak, ilgili özel dosyalar
(ve diğer harici dosyalar) proje ekleme/güncelleme ekranından tanımlanabilir.

Projeye eklenen dosyalar aynı dizinde bulunmaktadır. **Dockerfile** için **ADD**, **COPY** gibi komutlarda ve
**docker-compose.yml** için **build:** gibi ayarlarda bu göz önünde bulundurulmalıdır.

Her bir projenin sunucu ekranında **Başlat**, **Durdur** işlemleri arkaplanda docker-compose komutunu
projeye bağlı docker-compose.yml dosyası için çalıştırmaktadır.

Sunucu ekranındaki **Sanal Sunucu Dosyası** özelliği ise projenin nginx üzerinden sunulması için gerekli 
sanal sunucu dosyasını güncellemeye yardımcı olur. Bu bilgi güncellendiğinde nginx ayarları yeniden yüklenmektedir.
Bu dosya üzerinden hangi port numarasının, hangi sunucu adı ile yayınlanacağı nginx'e bildirilir.

# Yapılacak İşler

* [X] Phalcon gereksiniminin kaldırılması
* [X] Sunucu yönetimi kısmının iyileştirilmesi.
* [ ] Docker konteyner logları görüntülenebilmeli