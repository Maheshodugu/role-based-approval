ROLE-BASED CONTENT APPROVAL SYSTEM (Laravel 12)
================================================

Project Overview
----------------
This is a RESTful API built using Laravel 12 that implements a complete
role-based content approval workflow.

The system demonstrates:

- Role-based authorization (Author, Manager, Admin)
- Post submission and lifecycle management
- Approval / Rejection workflow
- Activity logging
- Sanctum API authentication
- Feature and unit testing


TECH STACK
----------
- Laravel 12
- PHP 8.2+
- MySQL
- Laravel Sanctum (API authentication)
- Spatie Laravel Permission
- PHPUnit


FEATURES
--------
Roles:

Author
- Create posts
- Update own posts
- View own posts

Manager
- View all posts
- Approve posts
- Reject posts

Admin
- Full access
- Approve
- Reject
- Delete posts


POST WORKFLOW
-------------
1) Author creates post -> Status: draft
2) Manager/Admin approves -> Status: approved
3) Manager/Admin rejects -> Status: rejected
4) Admin can delete post
5) All actions are logged in post_logs table


AUTHENTICATION
--------------
API authentication is handled using Laravel Sanctum.

Login returns a Bearer token.

All protected endpoints require header:

Authorization: Bearer <TOKEN>


LOCAL SETUP GUIDE
=================

1) Clone Repository

git clone https://github.com/Maheshodugu/role-based-approval.git
cd role-based-approval


2) Install Dependencies

composer install


3) Setup Environment File

Windows:
copy .env.example .env

Mac/Linux:
cp .env.example .env

Generate app key:
php artisan key:generate


4) Database Setup

Create database:

CREATE DATABASE role_based_approval CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

Update .env:

DB_DATABASE=role_based_approval
DB_USERNAME=root
DB_PASSWORD=

Run migrations and seeders:

php artisan optimize:clear
php artisan migrate --seed

Seeder creates:
- Roles (Author, Manager, Admin)
- Required permission tables


5) Run Application

php artisan serve

Application runs at:
http://127.0.0.1:8000


TEST USERS
----------
Author
email: author@test.com
password: password

Manager
email: manager@test.com
password: password

Admin
email: admin@test.com
password: password


API ENDPOINTS
=============

Base URL:
http://127.0.0.1:8000


PUBLIC ENDPOINT
---------------

Login
POST /api/login

Body:
{
  "email": "author@test.com",
  "password": "password",
  "device_name": "postman"
}


PROTECTED ENDPOINTS
-------------------

Logout
POST /api/logout

List Posts
GET /api/posts

Create Post (Author)
POST /api/posts

Update Post (Author - Own)
PUT /api/posts/{id}

Approve Post (Manager/Admin)
POST /api/posts/{id}/approve

Reject Post (Manager/Admin)
POST /api/posts/{id}/reject

Delete Post (Admin Only)
DELETE /api/posts/{id}


ACTIVITY LOGS
-------------
All actions are stored in post_logs table:

- created
- approved
- rejected
- deleted

Check using:

php artisan tinker

App\Models\PostLog::latest()->take(10)->get();


RUNNING TESTS
-------------
php artisan test

Includes:
- Feature tests for API endpoints
- Role-based authorization validation
- Post workflow validation


PROJECT PURPOSE
---------------
This project demonstrates:

- Clean Laravel architecture
- Role-based access control implementation
- Secure API design using Sanctum
- Test-driven backend development
- Production-ready backend structure


AUTHOR
------
Mahesh Odugu
GitHub: https://github.com/Maheshodugu

END
