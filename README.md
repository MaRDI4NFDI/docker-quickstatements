MaRDI fork of quickstatements projekt 

`docker build -t ghcr.io/mardi4nfdi/docker-quickstatements:master .`

Create OAuth key and secret in the wiki
`docker exec -ti qs-test-wikibase bash /QuickStatements.sh`

Test locally
```
cd example
docker-compose up -d
bash ./run_tests.sh
```

* Wiki is on http://localhost:8081
* Quickstatements is on http://localhost:8841

More in the [Wiki](https://github.com/MaRDI4NFDI/docker-quickstatements/wiki).