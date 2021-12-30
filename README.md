MaRDI fork of quickstatements projekt 

`docker build -t ghcr.io/mardi4nfdi/docker-quickstatements:master .`


Run locally
```
cd example
docker-compose up -d
```

Wait for the containers to start. Only then create OAuth key and secret in the wiki
`docker exec -ti qs-test-wikibase bash /Quickstatements.sh`

* Wiki is on http://localhost:8081
* Quickstatements is on http://localhost:8841

Run tests (from within ./example dir)
```
bash ./run_tests.sh
```

More in the [Wiki](https://github.com/MaRDI4NFDI/docker-quickstatements/wiki).