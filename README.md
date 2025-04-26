Laravel API AppThis is a backend API built using Laravel.

Requirements:

*   PHP >= 8.1
    
*   Composer
    
*   MySQL or compatible database
    

Installation:

1.  Install PHP dependencies:composer install
    
2.  Copy the environment file and configure database settings:cp .env.example .env
    
3.  Generate the application key:php artisan key:generate
    
4.  Link the storage folder (if needed for file uploads):php artisan storage:link
    
5.  Run database migrations and seed the database:php artisan migrate:fresh --seed
    
6.  Start the development server:php artisan serve
    

The API will be running at: [http://localhost:8000](http://localhost:8000)