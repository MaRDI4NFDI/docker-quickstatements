MaRDI fork of quickstatements projekt 

`docker build -t ghcr.io/mardi4nfdi/docker-quickstatements:master .`

Run locally
```
cd example
docker-compose up -d
```
* Wiki is at http://localhost:8081
* Quickstatements is at http://localhost:8841/
* API is at http://localhost:8841/api.php

Wait for the containers to start. Only then create OAuth key and secret in the wiki
```
docker exec -ti qs-test-wikibase bash /Quickstatements.sh
```

Run tests (from within ./example dir)
```
bash ./run_tests.sh
```

More information:
# About MaRDI
The mission of the Mathematical Research Data Initiative (MaRDI) is explained [here](https://www.mardi4nfdi.de/about/mission).

# About this fork
This repository contains a fork of Quickstatements. The original software written by [Magnus Manske](https://phabricator.wikimedia.org/p/Magnus/) can be found [here](https://phabricator.wikimedia.org/source/tool-quickstatements/browse/master/).

For an example of a working installation of Quickstatements, see:  https://tools.wmflabs.org/quickstatements/

The problem this fork is trying to solve is that Quickstatements does not integrate correctly with the [Wikibase Docker bundle](https://github.com/wmde/wikibase-docker/blob/1d06a628e36d1b44063ba0b829a395813fdb520a/quickstatements/README.md). Several problems are discussed [here](https://phabricator.wikimedia.org/T267812) and [here](https://phabricator.wikimedia.org/T234827).

To do
* Document the authentication process and the required configuration parameters
* Document batch import installation and usage

Patches are in the folder: docker-quickstatements/quickstatements/public_html/


# Using the image
You can use the pre-built image, e.g. using docker-compose, by pulling `ghcr.io/mardi4nfdi/docker-quickstatements:master`.

## Building the image
```
git clone git@github.com:MaRDI4NFDI/docker-quickstatements.git
cd docker-quickstatements
docker build -t ghcr.io/mardi4nfdi/docker-quickstatements:master .
```
## Example
* Open the wiki (http://localhost:8081), create a new property of type "string"
* Open the quickstatements page (http://localhost:8841)
* Click on "Login", authentify, allow
* Click on "new batch", attempt to import this
```
qid,P1,Len,#
,'zzzzzzzzz',swMATH,initial csv import 2021-12-17
```
(Note that strings are best enclosed in single quotes)
