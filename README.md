# line_event

```
#docker build
docker-compose build --no-cache

#docker立ち上げ
docker-compose up -d

#composer install(build時に実行されなかった場合)
docker-compose exec php-fpm sh -c "composer install"

#.env
docker-compose exec php-fpm sh -c "cp .env.example .env"

#key generate
docker-compose exec php-fpm sh -c "php artisan key:generate"

```

# 備考
こちらの手順はソースに反映済み
## LineBot開発に必要な追加手順
```dockerfile
#Dockerfileに追記
docker-php-ext-install sockets
```

### SDKの追加
```
docker-compose exec php-fpm sh -c "composer require linecorp/line-bot-sdk"
```
