#!/bin/bash

 openssl genrsa -out api.local.key 2048
 openssl req -new -key api.local.key -out api.local.csr -subj '/CN=api.local' -extensions EXT -config <( \
 printf "[dn]\nCN=default\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:default\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")
 openssl x509 -req  -days 1024 -in api.local.csr -signkey api.local.key -out api.local.crt
 chmod 644 api.local.key
