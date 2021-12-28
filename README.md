MaRDI fork of quickstatements projekt 

`docker build -t ghcr.io/mardi4nfdi/docker-quickstatements:master .`

Run the example
---------------
```
cd example
docker-compose up -d
```
* Wiki is at http://localhost:8081
* Quickstatements is at http://localhost:8841/
* API is at http://localhost:8841/api.php

Run tests
---------
From with the example directory, after having started the containers, call `bash ./run_tests.sh`.

More in the [Wiki](https://github.com/MaRDI4NFDI/docker-quickstatements/wiki).