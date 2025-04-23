web: php artisan serve --host=0.0.0.0 --port=$PORT
# ... outras instruções do seu Dockerfile ...

COPY . /app/.

RUN timeout 60s sh -c 'until nc -z $MYSQLHOST $MYSQLPORT; do echo "Waiting for MySQL..."; sleep 5; done' && php artisan migrate --force && echo 'POST DEPLOY CONCLUÍDO'

# ... outras instruções do seu Dockerfile ...
