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
