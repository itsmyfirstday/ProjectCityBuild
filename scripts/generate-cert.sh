# Check that mkcert is installed
mkcert -help > /dev/null || \
    (echo "mkcert is not installed. Please install this manually first" && \
     exit 1)

# Generate certificates
mkcert projectcitybuild.com localhost 127.0.0.1 && \
    mv projectcitybuild.com+2.pem docker/nginx/certs && \
    mv projectcitybuild.com+2-key.pem docker/nginx/certs
