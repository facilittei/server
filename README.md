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

### Tests

Running API tests

```
docker-compose exec php-fpm php artisan test
```

Run tests using filter to specify tests to run

```
docker-compose exec php-fpm php artisan test --filter AddressTest
```

To create a test

```
docker-compose exec php-fpm php artisan make:test AddressTest
```

### Monitoring

Application monitoring is done with [Prometheus](https://prometheus.io/).

[http://localhost:9090](http://localhost:9090)

The endpoint that Prometheus scrapes is [http://localhost/metrics](http://localhost/metrics)

### Visualization

Application metrics can be visualized with [Grafana](https://grafana.com/).

[http://localhost:3001](http://localhost:3001)

Default login: admin/admin

### Email server
We need to send emails to our customers in order to notify about a successful account creation or a payment.

To avoid going to external services to read those emails we can use [MailHog](https://github.com/mailhog/MailHog) to capture these emails for development purposes.

[http://localhost:8025](http://localhost:8025)