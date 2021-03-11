# Facilittei API

## Docker

```
docker-compose up -d
```

### Setup

```
docker-compose exec app composer install
```

### Database

It will create and populate the tables

```
docker-compose exec app php artisan migrate:fresh --seed
```
