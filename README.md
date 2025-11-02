# App Installation

Follow these steps to set up and run the application locally.

---

## 1. Copy `.env` file

```bash
cp .env.example .env
```

## 2. Build & start Docker containers

```bash
docker-compose up -d --build
```

## 3. Install Composer dependencies

```bash
docker-compose exec app composer install
```

## 4. Generate Laravel application key

```bash
docker-compose exec app php artisan key:generate
```

## 5. Run migrations

```bash
docker-compose exec app php artisan migrate
```

## 6. Optional: Cache configs and routes

```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
```

---

## 7. Access the application

* EstateTask App: [http://localhost:8000](http://localhost:8000)
* PhpMyAdmin: [http://localhost:8089](http://localhost:8089)

---

## 8. Create your first user (Admin) using Tinker

To create the first user, execute the following command:

```bash
docker-compose exec app php artisan tinker
```

Then in the Tinker shell (change the account detail to yours):

```php
use App\Models\User;
use App\Models\Building;

// Create an admin user
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password@123'),
]);

// Create a sample building owned by this user
$building = Building::create([
    'name' => 'Main Office Tower',
    'address' => '123 Central Street, District 1, HCMC',
    'user_id' => $user->id,
]);

$building;
```

You can now log in with the admin account and use the created building for testing.

## 9. Obtain API token

To get an API token for your user, send a POST request to the login endpoint:

```http
POST http://localhost:8000/api/v1/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password@123"
}
```

The response will include a token to use in subsequent API requests.

---

## 10. Example API Requests

### Get tasks for a building

```http
GET http://localhost:8000/api/v1/buildings/<building_id>/tasks
Authorization: Bearer <token>
```

Optional filters:

* `status` (Enum: TaskStatus)
* `assigned_to` (integer, user ID)
* `created_from` (date)
* `created_to` (date, after or equal to `created_from`)
* `timezone` (valid timezone string)

### Create a new task

```http
POST http://localhost:8000/api/v1/buildings/<building_id>/tasks
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "Fix elevator light",
  "description": "Light on floor 3 not working",
  "assigned_to": 1,
  "status": "Open",
  "due_at": "2025-11-05 10:00:00"
}
```

### Add a comment to a task

```http
POST http://localhost:8000/api/v1/tasks/<task_id>/comments
Authorization: Bearer <token>
Content-Type: application/json

{
  "body": "Comment body"
}
```

---

### Notes

* Make sure Docker and Docker Compose are installed on your system.
* Ports `8000` (Laravel) and `8089` (PhpMyAdmin) can be changed in `docker-compose.yml` if needed.
