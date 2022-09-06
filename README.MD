## To-do list
SPA-application on [Vue.js](https://vuejs.org/v2/guide/) 
with [Vue-router](https://router.vuejs.org/) and [Vuex](https://vuex.vuejs.org)
Based on [Vue-SPA-dummy](https://github.com/downtest/vue-spa-template)

## Development
```bash
docker compose up --build -d

# Run watcher
docker compose run --rm node yarn watch
```

## Upgrading Postgres
```bash
# Make the dump
docker compose exec postgres pg_dumpall -U todouser > /pg-dumps/dump.sql

# Go to postgres interface and drop the database
docker-compose exec postgres psql -U todouser -d postgres
# \l - show the list of databases in postgres
drop database todolist with (force);

# Stop container to remove volume with old data
docker-compose up postgres
# then press Ctrl+C
# ...or you can stop all containers docker-compose down

# Delete the volume
docker volume rm listodo_postgres-data

# Then change the docker image version in compose file
# ...
# Rebuild the image
docker compose up -d --build postgres
# ...or start all together docker-compose up -d --build

# Finally restore the data from the dump
docker compose exec postgres psql -U todouser -d todolist < /pg-dumps/dump.sql
```

#### Change Postgres password
```bash
# Firstly, connect to DB
docker compose exec postgres psql -U todouser todolist

# then make a request
ALTER USER todouser WITH PASSWORD 'vbi3478f';
```