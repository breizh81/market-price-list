# Market Price List

Welcome to the Market Price List project. This document will guide you through setting up the project, running it, and using its features.

## Getting Started

To get started with the project, follow the steps below.

### Prerequisites

Make sure you have Docker and Docker Compose installed on your machine.

### Setup

1. **Create a `.env` File**

   Create a `.env` file in the root directory of the project with the following content:

   ```env
   FILE_UPLOAD_DIRECTORY=/var/www/html/uploads
   MAILER_RECIPIENT_EMAIL=recipient email
   MESSENGER_TRANSPORT_DSN=amqp://guest:guest@<host-ip>:5672/%2f

2. **Run the Following Commands**

    Use the provided Makefile to manage Docker Compose commands. Run the following command to start the project:

   ```bash
make start
make add-migrations
make add-fixtures
make npm-install
npm install sass-loader@^14.0.0 sass --save-dev
make encoredev

## Usage

   Once the setup is complete, you can access the following features:

- **Import Products**: [http://localhost:8000/import](http://localhost:8000/import)
- **Show Product List**: [http://localhost:8000/products](http://localhost:8000/products)
- **Product Validation**: [http://localhost:8000/products/validate](http://localhost:8000/products/validate)

