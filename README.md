# Bookmark Service Documentation

## Overview

The Bookmark Service is a Laravel-based API that allows users to save, retrieve, and manage bookmarks. It features asynchronous metadata fetching using Redis Pub/Sub for distributed processing, ensuring a responsive user experience while metadata (title, description) is fetched in the background.

## Key Features

- RESTful API for bookmark management
- Asynchronous metadata fetching via Redis Pub/Sub
- Soft delete functionality
- Token-based authentication with Laravel Sanctum
- Comprehensive error handling and validation

## Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- Redis server
- Sqlite database
- Docker (for using Laravel Sail)

### Installation

1. **Clone the repository**

```bash
git clone https://github.com/mohaphez/bookmark-manager.git
cd bookmark-manager
```

2. **Install dependencies**

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs --no-scripts
```

3. **Set up environment**

```bash
cp .env.example .env
```

```bash
./vendor/bin/sail up -d
```

```bash
./vendor/bin/sail php artisan key:generate
```

```bash
./vendor/bin/sail artisan migrate
```

```bash
./vendor/bin/sail artisan db:seed
```

## Running the Queue Worker

To process bookmark metadata in the background:

```bash
./vendor/bin/sail php artisan queue:work --queue=bookmarks
```

## Running the Redis Subscriber

The bookmark service includes a Redis subscriber that listens for bookmark events from other services. This component is essential for the distributed processing architecture.


### Using Docker Compose (Recommended)

The service is already configured in `docker-compose.yml`, so it will start automatically when you run:

```bash
./vendor/bin/sail up -d
```

This launches a dedicated container for the bookmark subscriber that:
- Runs independently from your web server
- Automatically restarts if it crashes

### Manual Execution (Optional)

If you need to run the subscriber manually for development or debugging:

```bash
./vendor/bin/sail php artisan bookmark:subscribe
```

This command will:
- Connect to Redis
- Subscribe to the `bookmarks:new` channel
- Process incoming bookmark messages
- Dispatch jobs to fetch metadata


## API Endpoints

### Authentication

#### Login

```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

Response:
```json
{
  "status": "success",
  "message": "User logged in successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "access_token": "1|abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  }
}
```

#### Logout

```bash
curl -X POST http://localhost/api/v1/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "status": "success",
  "message": "User logged out successfully",
  "data": null
}
```

### Bookmarks

#### Get All Bookmarks

```bash
curl -X GET http://localhost/api/v1/bookmarks \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "status": "success",
  "message": "Bookmarks retrieved successfully",
  "data": [
    {
      "id": "01957235-a3b6-7073-a46e-758689f03005",
      "url": "https://example.com",
      "title": "Example Website",
      "description": "This is an example website",
      "metadata_status": "completed",
      "created_at": "2023-03-07T12:00:00.000000Z",
      "updated_at": "2023-03-07T12:01:00.000000Z"
    }
  ]
}
```

#### Create Bookmark

```bash
curl -X POST http://localhost/api/v1/bookmarks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://example.com", "title": "Example Website", "description": "This is an example website"}'
```

Response (when processed asynchronously):
```json
{
  "status": "success",
  "message": "Bookmark queued for processing",
  "data": {
    "url": "https://example.com",
    "processing": true
  }
}
```

Response (when processed directly):
```json
{
  "status": "success",
  "message": "Bookmark created successfully",
  "data": {
    "id": "01957235-a3b6-7073-a46e-758689f03005",
    "url": "https://example.com",
    "title": "Example Website",
    "description": "This is an example website",
    "metadata_status": "pending",
    "created_at": "2023-03-07T12:00:00.000000Z",
    "updated_at": "2023-03-07T12:00:00.000000Z"
  }
}
```

#### Delete Bookmark

```bash
curl -X DELETE http://localhost/api/v1/bookmarks/01957235-a3b6-7073-a46e-758689f03005 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "status": "success",
  "message": "Bookmark deleted successfully",
  "data": null
}
```

## Integrating with Redis Pub/Sub

### Publishing Bookmarks from External Services

You can publish bookmarks from other services using Redis Pub/Sub:

```php
// PHP example
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$bookmark = [
    'url' => 'https://example.com',
    'user_id' => 1,
    'title' => 'Example Website',
    'description' => 'This is an example website'
];

$redis->publish('bookmarks:new', json_encode($bookmark));
```

```bash
# Redis CLI example
redis-cli PUBLISH bookmarks:new '{"url":"https://example.com","user_id":1,"title":"Example Website","description":"This is an example website"}'
```

### Message Format

The message should be a JSON object with the following structure:

```json
{
  "url": "https://example.com",
  "user_id": 1,
  "title": "Example Website (optional)",
  "description": "This is an example website (optional)"
}
```

Required fields:
- `url`: The URL of the bookmark
- `user_id`: The ID of the user who owns the bookmark

Optional fields:
- `title`: The title of the bookmark
- `description`: The description of the bookmark


## Testing

Run the test suite to ensure everything is working correctly:

```bash
./vendor/bin/sail test
```
