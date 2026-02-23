<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Role-Based Content Approval API

This project implements a role-based post approval workflow on Laravel 12.

### Roles

- `Author`
	- Create posts
	- Update only own posts
	- View only own posts
- `Manager`
	- View all posts
	- Approve posts
	- Reject posts with reason
- `Admin`
	- All manager capabilities
	- Delete any post

### Post Fields

- `title`
- `body`
- `status` (`pending`, `approved`, `rejected`)
- `approved_by`
- `rejected_reason`

### Activity Log

Actions are recorded in `post_logs` with:

- `post_id`
- `action` (`created`, `approved`, `rejected`, `deleted`)
- `performed_by`

### Authentication

All endpoints are protected by `auth:sanctum`.

### Endpoints

| Action | Method | Endpoint | Access |
|---|---|---|---|
| Login | POST | `/api/login` | Public |
| Logout | POST | `/api/logout` | Any authenticated user |
| List Posts | GET | `/api/posts` | Author/Manager/Admin |
| Create Post | POST | `/api/posts` | Author |
| Update Post | PUT | `/api/posts/{id}` | Author (own post) |
| Approve Post | POST | `/api/posts/{id}/approve` | Manager/Admin |
| Reject Post | POST | `/api/posts/{id}/reject` | Manager/Admin |
| Delete Post | DELETE | `/api/posts/{id}` | Admin |

### Request Examples

Login:

```json
{
	"email": "author@test.com",
	"password": "password",
	"device_name": "postman"
}
```

Create post:

```json
{
	"title": "Quarterly Product Update",
	"body": "Release notes and roadmap details..."
}
```

Reject post:

```json
{
	"rejected_reason": "Please add references and supporting data."
}
```

### Response Notes

- `POST /api/posts` returns `201` with created post.
- `DELETE /api/posts/{id}` returns `204`.
- Unauthorized/forbidden actions return `403`.

### Setup Notes

Seed required roles:

```bash
php artisan db:seed
```

Run tests:

```bash
php artisan test
```

### Postman Collection

Import the collection file from:

- `docs/postman/Role-Based-Approval.postman_collection.json`

Set these collection variables before sending requests:

- `base_url` (example: `http://localhost`)
- `token` (Sanctum bearer token)
- `post_id` (an existing post id)

The collection includes all workflow endpoints:

- List Posts
- Create Post
- Update Post
- Approve Post
- Reject Post
- Delete Post

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
