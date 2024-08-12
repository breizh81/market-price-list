# market-price-list
create a .env file and add
FILE_UPLOAD_DIRECTORY=/var/www/html/uploads
MAILER_RECIPIENT_EMAIL=recipient email
for linux:
MESSENGER_TRANSPORT_DSN=amqp://guest:guest@<host-ip>:5672/%2f

Makefile is using docker compose command please adapt file if needed 
make start
make add-migrations
make add-fixtures
make npm-install
npm install sass-loader@^14.0.0 sass --save-dev
make encoredev

