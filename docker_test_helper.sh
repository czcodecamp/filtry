#!/bin/sh

echo DOCKER TEST HELPER

echo ...Remove container
docker rm cc-filters

echo ...Remove image
docker rmi ppohoda/cc-filters

echo ...Build image
docker build -t ppohoda/cc-filters .

echo ...Run container
docker run -it -p 80:80 --name cc-filters ppohoda/cc-filters