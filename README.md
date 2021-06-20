# Facilittei API

## Docker

#### To start

```
docker-compose up -d
```

#### To stop

```
docker-compose down
```

## API 

### Setup

### :warning: Alternative
Access the running container

```
docker exec -it php-fpm bash
```

Then you can run all the other steps sequentially without **docker-compose exec php-fpm**

### Other steps

```
docker-compose exec php-fpm composer install
```

### Environment variables

:warning: Create `.env` file from the `.env.example`

Generate app key

```
docker-compose exec php-fpm php artisan key:generate
```

### Storage

Set permissions to `storage` and `public` folders

```
docker-compose exec php-fpm chmod -R 777 storage public
```

Create symbolic link from `storage` to `public` folders

```
docker-compose exec php-fpm php artisan storage:link
```

### Database

It will create and populate the tables

```
docker-compose exec php-fpm php artisan migrate:fresh --seed
```

### Queue

Emails are sent in background

```
docker-compose exec php-fpm php artisan queue:work
```
