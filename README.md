# tracktracejomdaftar
Track Trace Jomdaftar

## Setup Guide

### Prerequisites
- PHP >= 8.0
- Composer
- Node.js and npm

### Installation Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/Bharani028/rentb.git
   cd rentb
   ```

2. Install PHP dependencies using Composer:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies using npm:
   ```bash
   npm install
   ```

4. Set up the environment file:
   ```bash
   cp .env.example .env
   ```
   Update the `.env` file with your database and application configuration.

5. Generate the application key:
   ```bash
   php artisan key:generate
   ```

6. Run database migrations:
   ```bash
   php artisan migrate
   ```

7. Seed the database:
   ```bash
   php artisan db:seed
   ```

8. Build front-end assets:
   ```bash
   npm run dev
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

10. Access the application in your browser at `http://localhost:8000`.
