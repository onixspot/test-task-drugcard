# Test Task DrugCard

## Deployment

```shell
docker compose up -d --build 
```

## Using

First of all we need to get into app container
```shell
docker exec -it app bash --rcfile /root/.profile
```

The option `--rcfile` gives you an opportunity to use `[TAB][TAB]` functionality for commands such as: `symfony ...` or `bin/console ...`

For grabbing products data from [Goldi](https://goldi.ua) store 3 pages execute next command in app container:
```shell
bin/console sync:products App\\Source\\GoldiUAGrabber --limit 3
```

# Endpoints

`https://localhost/api` - API Swagger Interface

`https://localhost/api/products` - To get list of products in `json` format
`https://localhost/api/products/export` - To export products in `csv` format