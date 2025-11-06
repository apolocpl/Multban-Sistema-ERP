# Project Overview

This is a modular and multi-tenancy ERP system built with Laravel 12. It provides features for managing clients, companies, products, billing, scheduling, and reporting. The application follows a modular architecture, with different functionalities organized into separate modules.

**Main Technologies:**

*   **Backend:** Laravel 12, PHP 8.2
*   **Frontend:** Vite, Tailwind CSS, Bootstrap, Sass
*   **Database:** MySQL
*   **Key Libraries:**
    *   `spatie/laravel-permission`: For handling permissions and roles.
    *   `yajra/laravel-datatables`: For creating DataTables.
    *   `intervention/image-laravel`: for image manipulation.

# Building and Running

**1. Install Dependencies:**

*   **PHP Dependencies:**
    ```bash
    composer install
    ```
*   **Frontend Dependencies:**
    ```bash
    npm install
    ```

**2. Set up Environment:**

*   Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```
*   Generate an application key:
    ```bash
    php artisan key:generate
    ```
*   Configure your database credentials in the `.env` file.

**3. Run Migrations and Seeders:**

*   Run the database migrations:
    ```bash
    php artisan migrate
    ```
*   Run the database seeders:
    ```bash
    php artisan db:seed
    ```

**4. Run the Application:**

*   **Development Server:**
    ```bash
    npm run dev
    ```
    This will start the Vite development server and the Laravel development server concurrently.

*   **Production Build:**
    ```bash
    npm run build
    ```

**5. Running Tests:**

*   To run the test suite, use the following command:
    ```bash
    ./vendor/bin/phpunit
    ```

# Development Conventions

*   **Modular Structure:** The application is organized into modules, with routes, controllers, and other components separated by functionality.
*   **Routing:** Routes are defined in separate files within the `app/Http/Routes` directory and grouped by module.
*   **Permissions:** The `spatie/laravel-permission` package is used for role-based access control. Permissions are defined in the route files and checked using middleware.
*   **Coding Style:** The project uses `laravel/pint` for code style. To check for style issues, run:
    ```bash
    composer lint
    ```
    To automatically fix style issues, run:
    ```bash
    composer format
    ```
