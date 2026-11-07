# Questionnaire

A Symfony web application for creating and managing questionnaires. Organizers can
create questionnaires with steps and questions, publish them for people to browse,
and collect responses from participants.

## Technology

- PHP 8.4.27
- Symfony 7.4 LTS
- Doctrine ORM and Doctrine Migrations
- MySQL-compatible database (the migrations use MySQL syntax)
- Twig templates

## Requirements

- PHP and the extensions required by Composer
- Composer
- MySQL or MariaDB

## Local setup

1. Create a `.env.local` file in the project root. This file is ignored by Git.
   Set the application secret and a database connection string for your local
   database:

   ```dotenv
   APP_ENV=dev
   APP_SECRET=replace-with-a-random-secret
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/questionnaire?serverVersion=8.0.32&charset=utf8mb4"
   ```

   Replace the database credentials and `serverVersion` with values for your
   local MySQL or MariaDB server. URL-encode special characters in the database
   credentials. You can generate a secret with:

   ```sh
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

2. Install PHP dependencies:

   ```sh
   composer install
   ```

3. Create the `questionnaire` database (or create a database matching the name
   in `DATABASE_URL`), then apply the migrations:

   ```sh
   php bin/console doctrine:migrations:migrate
   ```

4. Start the PHP development server:

   ```sh
   php -S 127.0.0.1:8000 -t public public/index.php
   ```

   Open <http://127.0.0.1:8000> in your browser.

## Tests

Run the PHPUnit test suite with:

```sh
vendor/bin/phpunit
```

## Project layout

- `src/Controller/` — page, user, questionnaire, step, question, and response controllers
- `src/Entity/` — Doctrine entities
- `templates/` — Twig views
- `migrations/` — database schema migrations
- `config/` — Symfony and Doctrine configuration
- `public/` — web document root and static assets
