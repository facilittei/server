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

```
docker-compose exec app composer install
```

### Environment variables

:warning: Create `.env` file from the `.env.example`

Generate app key

```
docker-compose exec app php artisan key:generate
```

### Storage

Set permissions to `storage` and `public` folders

```
docker-compose exec app chmod -R 777 storage public
```

Create symbolic link from `storage` to `public` folders

```
docker-compose exec app php artisan storage:link
```

### Database

It will create and populate the tables

```
docker-compose exec app php artisan migrate:fresh --seed
```

### Queue

Emails are sent in background

```
docker-compose exec app php artisan queue:work
```
