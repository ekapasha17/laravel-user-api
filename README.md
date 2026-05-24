# Laravel User Management API

REST API built with Laravel that allows you to create users and retrieve a paginated, searchable list of users, with role based access control.

---

## What This Project Does

| Endpoint          | What it does                                                     |
| ----------------- | ---------------------------------------------------------------- |
| `POST /api/users` | Creates a new user and sends two confirmation emails             |
| `GET /api/users`  | Returns a list of active users with search, sort, and pagination |

---

## Requirements

Before running this project, make sure you have the following installed on your computer:

| Tool     | Version            | How to check              |
| -------- | ------------------ | ------------------------- |
| PHP      | 8.2 or higher      | `php -v` in terminal      |
| Composer | Any recent version | `composer -v` in terminal |

---

## Installation

Open your terminal, then run these commands one by one:

### 1. Clone the project

```bash
git clone https://github.com/ekapasha17/laravel-user-api.git
cd laravel-user-api
```

### 2. Install dependencies

```bash
composer install
```

### 3. Copy the environment file

```bash
cp .env.example .env
```

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Create the database file

```bash
touch database/database.sqlite
```

### 6. Run the database migrations and seed sample data

```bash
php artisan migrate:fresh --seed
```

### 7. Start the server

```bash
php artisan serve
```

The API is now running at **http://localhost:8000**. Keep this terminal window open while testing.

---

## Sample Users (Created by the Seeder)

The seed command above creates these users automatically:

| Name          | Email                | Role          | Password | Active |
| ------------- | -------------------- | ------------- | -------- | ------ |
| Admin User    | admin@example.com    | administrator | password | ✅     |
| Manager User  | manager@example.com  | manager       | password | ✅     |
| John Doe      | john@example.com     | user          | password | ✅     |
| Jane Smith    | jane@example.com     | user          | password | ✅     |
| Alice Brown   | alice@example.com    | user          | password | ✅     |
| Inactive User | inactive@example.com | user          | password | ❌     |

> The inactive user exists in the database but will never appear in API responses, this tests the active filter.

---

## Testing with Postman

[Postman](https://www.postman.com/downloads/) is a free tool for sending requests to APIs without writing any code.

### Step 1 Install Postman

Download and install from [https://www.postman.com/downloads](https://www.postman.com/downloads)

### Step 2 Create an Environment

Environments let you store values (like tokens) and reuse them across requests.

1. Open Postman
2. Click **Environments** in the left sidebar to create new
3. Name it: `Laravel User API`
4. Add these variables:

| Variable        | Value                   |
| --------------- | ----------------------- |
| `base_url`      | `http://localhost:8000` |
| `admin_token`   | _(leave empty for now)_ |
| `manager_token` | _(leave empty for now)_ |
| `user_token`    | _(leave empty for now)_ |

5. Click **Save**, then select this environment from the dropdown in the top-right corner of Postman

### Step 3 - Generate Auth Tokens

The `GET /api/users` endpoint requires a login token. Run this in your terminal to generate one for each role:

```bash
php artisan tinker
```

Then paste this block and press Enter:

```php
$admin   = App\Models\User::where('email', 'admin@example.com')->first();
$manager = App\Models\User::where('email', 'manager@example.com')->first();
$user    = App\Models\User::where('email', 'john@example.com')->first();

echo "ADMIN:   " . $admin->createToken('postman')->plainTextToken . "\n";
echo "MANAGER: " . $manager->createToken('postman')->plainTextToken . "\n";
echo "USER:    " . $user->createToken('postman')->plainTextToken . "\n";
```

Copy each token value into the matching Postman environment variable from Step 2. Then type `exit` to leave tinker.

---

## API Reference

### POST /api/users - Create a User

Creates a new user account and sends two emails:

- A welcome email to the new user
- A notification email to the system administrator

**No authentication required.**

#### Request

| Field      | Type   | Required | Rules                              |
| ---------- | ------ | -------- | ---------------------------------- |
| `email`    | string | ✅       | Valid email format, must be unique |
| `password` | string | ✅       | Minimum 8 characters               |
| `name`     | string | ✅       | Between 3 and 50 characters        |

#### Postman Setup

| Setting              | Value                    |
| -------------------- | ------------------------ |
| Method               | `POST`                   |
| URL                  | `{{base_url}}/api/users` |
| Header: Content-Type | `application/json`       |
| Header: Accept       | `application/json`       |
| Body                 | raw → JSON               |

#### Example Request Body

```json
{
    "email": "newuser@example.com",
    "password": "secret123",
    "name": "New User"
}
```

#### Example Success Response - `201 Created`

```json
{
    "id": 7,
    "email": "newuser@example.com",
    "name": "New User",
    "created_at": "2024-11-25T12:34:56+00:00"
}
```

#### Example Validation Error Response - `422 Unprocessable Content`

```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

> **Emails in local development:** Emails are not actually sent, they are written to `storage/logs/laravel.log`. After creating a user, open that file and search for `UserWelcomeMail` to confirm both emails were dispatched.

---

### GET /api/users - List Users

Returns a paginated list of active users. Supports search, sort, and pagination.

**Authentication required** - include a Bearer token in the Authorization header.

#### Query Parameters

| Parameter | Type    | Required | Default      | Description                                  |
| --------- | ------- | -------- | ------------ | -------------------------------------------- |
| `search`  | string  | ❌       | -            | Filter by name or email (partial match)      |
| `sortBy`  | string  | ❌       | `created_at` | Sort field: `name`, `email`, or `created_at` |
| `page`    | integer | ❌       | `1`          | Page number                                  |

#### Postman Setup

| Setting               | Value                    |
| --------------------- | ------------------------ |
| Method                | `GET`                    |
| URL                   | `{{base_url}}/api/users` |
| Header: Accept        | `application/json`       |
| Header: Authorization | `Bearer {{admin_token}}` |

#### Example Success Response - `200 OK`

```json
{
    "page": 1,
    "users": [
        {
            "id": 1,
            "email": "admin@example.com",
            "name": "Admin User",
            "role": "administrator",
            "created_at": "2024-11-25T12:34:56+00:00",
            "orders_count": 1,
            "can_edit": true
        },
        {
            "id": 3,
            "email": "john@example.com",
            "name": "John Doe",
            "role": "user",
            "created_at": "2024-11-25T12:34:56+00:00",
            "orders_count": 5,
            "can_edit": true
        }
    ]
}
```

#### The `can_edit` Field

This field tells you whether the currently logged-in user is allowed to edit each listed user. The rules are:

| Logged-in role  | Who they can edit           |
| --------------- | --------------------------- |
| `administrator` | Everyone                    |
| `manager`       | Only users with role `user` |
| `user`          | Only themselves             |

To see this in action, send the same request with different tokens (`{{admin_token}}`, `{{manager_token}}`, `{{user_token}}`) and compare the `can_edit` values.

#### Example: Search and Sort

```
GET {{base_url}}/api/users?search=john&sortBy=name&page=1
```

---

## Verifying Emails

Since this is a local development setup, emails are logged to a file instead of being sent.

After calling `POST /api/users`, open `storage/logs/laravel.log` in any text editor and scroll to the bottom. You should see two log entries, one for the welcome email and one for the admin notification, containing the email subject, recipient, and body.

---

## Running Automated Tests

The project includes automated feature tests that verify every scenario without Postman.

```bash
php artisan test
```

passing run :

```
PASS  Tests\Feature\CreateUserTest
✓ creates user and returns 201 with correct shape
✓ dispatches welcome email to new user
✓ dispatches notification email to admin
✓ rejects missing email
✓ rejects invalid email format
✓ rejects duplicate email
✓ rejects password shorter than 8 characters
✓ rejects name shorter than 3 characters
✓ rejects name longer than 50 characters

PASS  Tests\Feature\GetUsersTest
✓ unauthenticated request returns 401
✓ returns paginated list with correct structure
✓ excludes inactive users
✓ search filters by name
✓ search filters by email
✓ orders count reflects actual orders
✓ administrator can edit any user
✓ manager can edit users but not other managers
✓ user can only edit themselves

Tests: 18 passed
```

---

## Project Structure

For developers reviewing the structure, here is where the key logic lives:

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   └── UserController.php        # Thin — delegates to service and query
│   ├── Requests/
│   │   ├── CreateUserRequest.php     # Validation rules for POST /api/users
│   │   └── GetUsersRequest.php       # Validation rules for GET /api/users params
│   └── Resources/
│       ├── UserResource.php          # Response shape for create
│       └── UserListResource.php      # Response shape for list (includes can_edit)
├── Mail/
│   ├── UserWelcomeMail.php           # Email to new user
│   └── AdminNewUserNotificationMail.php  # Email to admin
├── Models/
│   ├── User.php
│   └── Order.php
├── Policies/
│   └── UserPolicy.php                # can_edit rules per role
├── Queries/
│   └── UserListQuery.php             # Filtering, sorting, pagination logic
└── Services/
    └── UserService.php               # User creation + email dispatch
```

---

## Troubleshooting

**`SQLSTATE: unable to open database file`**

```bash
touch database/database.sqlite
php artisan migrate:fresh --seed
```

**No emails appearing in the log file**

- Confirm `MAIL_MAILER=log` is set in your `.env` file
- Run `php artisan config:clear` then try the request again
