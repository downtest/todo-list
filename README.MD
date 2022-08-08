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

# Then change the docker image version in compose file
# ...
# Rebuild the image
docker compose up -d --build postgres

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