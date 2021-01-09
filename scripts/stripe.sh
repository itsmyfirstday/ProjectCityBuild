#!/bin/bash

if [ -z `docker ps -q --no-trunc | grep $(docker-compose ps -q stripe)` ]; then
  docker-compose up -d
fi

if [ $1 == 'payment' ]; then
#    docker-compose exec stripe stripe trigger checkout.session.completed

    # Use fixtures because it's the only way to send custom data
    docker-compose exec stripe stripe fixtures /usr/local/share/stripe-fixtures/payment-fixture.json
else
    echo "Unrecognized argument. Did you mean 'stripe payment'?"
fi

