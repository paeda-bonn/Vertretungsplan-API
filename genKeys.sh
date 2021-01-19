#shell
openssl genrsa  -out config/privkey.pem 4096
openssl rsa -in config/privkey.pem -pubout -out config/pubkey.pem