# ArtSpace - Personal Artist Freelance Platform

A web-based platform for freelance artists to showcase their work, manage commissions, and interact with clients.
[LIHAT LIVE WEBNYA](https://ayamgeprek.up.railway.app/profile)

## Tech Stack

- PHP 8.3 / Laravel 12
- MySQL
- Tailwind CSS (CDN)
- Alpine.js
- Lucide Icons

## Features

- Artist profile with avatar, bio, social media links, and commission status
- Portfolio management with masonry grid layout, categories, and view tracking
- Commission system with multi-tier pricing (Basic, Standard, Premium) and slot management
- Order workflow: Pending > Confirmed > Waiting Payment > Paid > Completed
- Real-time chat with file upload support
- Review system with star rating, quick tags, and artist approval workflow
- FAQ management with search and category filter
- Dark/light mode toggle
- Client registration and session-based authentication

## Requirements

- PHP >= 8.2
- Composer
- MySQL
- Node.js (optional, for asset management)

## Installation

Clone the repository:

```
git clone https://github.com/Hilal5/app-personal-artist-freelance-v2.git
cd app-personal-artist-freelance-v2
```

Install dependencies:

```
composer install
```

Copy environment file and configure:

```
cp .env.example .env
php artisan key:generate
```

Set up database in `.env`:

```
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations and seeders:

```
php artisan migrate
php artisan db:seed
```

Create storage symlink:

```
php artisan storage:link
```

Start the development server:

```
php artisan serve
```

## Default Credentials

Artist (admin):
- Email: admin@artspace.com
- Password: password

## License

This project is for personal and educational use.
