# Register

Event registration platform with configurable forms and payment tracking.

## Feature overview

- Event-based registrations with configurable form sections and fields
- Registration states, including waitlist invites and expiry handling
- Mollie payment status syncing for registrations
- CSV export of registration data
- Filament admin panel for managing events, forms, and registrations

## Installation
### Requirements
- PHP 8.4 or higher (should work on 8.3 too, but may need to downgrade some dependencies)
- Composer
- Database: any database supported by Eloquent which supports pessimistic row locking. If row locking is not supported, it is possible that more registrations than the available capacity will be accepted. The following databases do support all required features:
    - MariaDB with InnoDB engine
    - MySQL with InnoDB engine
    - PostgreSQL
- Git (for cloning the repository)
- Mollie account and API key (for payment processing)
- SMTP server or service for sending emails
- Web server (e.g., Apache, Nginx) or Laravel's built-in server for local development
- Optional: Queue worker setup for handling email notifications and other background tasks
- Optional: NPM and Node.js for compiling frontend assets (not required if using the precompiled assets)

### Steps
1. Clone the repository: `git clone https://github.com/ArcoVoets/inscribo.git`
2. Navigate to the project directory: `cd inscribo`
3. Install dependencies: `composer install`
4. Copy the example environment file and configure it: `cp .env.example .env`
    - Set the following environment variables in the `.env` file:
        - App variables:
            - `APP_URL`, `APP_ENV` (set to production), `APP_DEBUG` (set to false), `APP_KEY` (set using `php artisan key:generate`), `APP_LOCALE` (e.g., `en` or `nl`), `APP_DISPLAY_TIMEZONE` (e.g., `Europe/Amsterdam`)
        - Database:
            - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
        - Mollie API key:
            - `MOLLIE_KEY`
        - Queue connection
            - `QUEUE_CONNECTION` (`database` if a queue worker will be set up (preferably), otherwise use `background` or `deffered`)
        - Mail configuration:
            - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
   - Set up your database connection in the `.env` file
   - Set up your Mollie API key in the `.env` file
   - Set up your mail configuration in the `.env` file
5. Build the frontend assets (or copy the precompiled assets):
    - To build assets: `npm install && npm run build`
    - Or copy the precompiled assets from the `precompiled-assets` directory to the `public` directory
6. Run database migrations: `php artisan migrate`
7. Optionally: Create an admin user: `php artisan app:create-admin`
8. Optionally: Create more admins and managers from the admin panel
