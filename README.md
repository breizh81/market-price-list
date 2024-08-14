# Market Price List

Welcome to the Market Price List project. This document will guide you through setting up the project, running it, and using its features.

## Getting Started

To get started with the project, follow the steps below.

### Prerequisites

Make sure you have Docker and Docker Compose installed on your machine.

### Setup

1. **Create a `.env` File**

   Create a `.env.local` file in the root directory of the project if needed override the following:

   ```env
   FILE_UPLOAD_DIRECTORY=/var/www/html/uploads
   MESSENGER_TRANSPORT_DSN=amqp://guest:guest@rabbitmq:5672/%2f
   MESSENGER_TRANSPORT_VHOST=/
   MAILER_DSN=smtp://mailhog:8025
   MAILER_SENDER_EMAIL=mailhog@example.com
   MAILER_RECIPIENT_EMAIL=s.kontomarkou@gmail.com

### If you've changed the env values you should update the docker-compose.yml file

2. **Run the Following Commands**

    Use the provided Makefile to manage Docker Compose commands. Run the following command to start the project:
    The commands are with docker compose so if needed you must change

   ```bash
   make start
   make add-migrations
   make add-fixtures
   make npm-install
   npm install sass-loader@^14.0.0 sass --save-dev
   make encoredev

## Usage

   Once the setup is complete, you can access the following features:

- **Home page**: [http://localhost:8000] (http://localhost:8000)

