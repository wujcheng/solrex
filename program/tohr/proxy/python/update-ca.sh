#!/bin/bash
CADIR=./ca
openssl genrsa -out $CADIR/ca.key 1024
openssl req -batch -new -key $CADIR/ca.key -out $CADIR/ca.csr -subj "/C=CN/ST=BJ/L=BJ/O=The Onion HTTP Router/OU=Tohr Certificate Authority/CN=Tohr CA"
openssl x509 -req -days 1095 -text -in $CADIR/ca.csr -signkey $CADIR/ca.key -out $CADIR/ca.crt
rm -f $CADIR/ca.csr
firefox $CADIR/ca.crt
