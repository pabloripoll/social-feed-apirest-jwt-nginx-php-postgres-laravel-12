<div id="top-header" style="with:100%;height:auto;text-align:right;">
    <img src="./public/files/pr-banner-long.png">
</div>

# SOCIAL FEED - LARAVEL 12

This repository contains a basic example of a RESTful API service built with **Laravel 12**, intended for research purposes and as a demonstration of my developer profile. It implements the core features of a minimal, custom social feed application and serves as a reference project for learning, experimentation, or as a back-end development code sample.
<br><br>

## Project Overview

The API supports a registry of platform "members," enabling users to create posts and voting with like or dislike other users' posts. An administrator role is provided for managing users, content, and platform statistics via a dedicated back office.

## Content of this page:

- [REST API Features](#apirest-features)
- [Infrastructure Platform](#infrastructure-platform)
- [REST API - Laravel 12](#apirest-laravel)
- [API Authentication with JWT](#apirest-jwt)
- [Swagger API Documentation](#apirest-swagger)
- [Domain Driven Design](#apirest-ddd)
- [Use this Platform Repository for REST API project](#platform-usage)
<br><br>

## <a id="apirest-features"></a>REST API Features

- **RESTful API** — Follows common REST patterns for resource-oriented endpoints.
- **Stateless API** — Each request is self-contained, adhering to REST principles.
- **Domain-Driven Design** — Each domain is self-contained in a single directory, except for resources specific to the framework.
- **JWT Role-Based Access** — Authentication and authorization flows support both regular users and administrators, using JWTs with role-based access control.
- **User Registration and Login** — Secure registration and login for members with JWT-based authentication.
- **CRUD Operations** — Users can create, update, and delete their own content.
- **SOLID Principles** — Applies best practices in code structure, validation, error handling, and response formats.
- **Member and Admin Endpoints** — Dedicated endpoints for user/content management, statistics, and moderation tools.
- **Comprehensive API Error Handling** — Standardized, consistent responses for errors and validation.
- **Integration Testing & Static Analysis** — Includes scripts and tools for automated endpoint testing and static code analysis to ensure quality.
- **OpenAPI/Swagger Documentation** — Interactive API documentation generated from code annotations, accessible via a web interface.

#### Tech Stack

- **Framework:** [Laravel 12](./LARAVEL.md)
- **Authentication:** [Tymon JWT](https://packagist.org/packages/tymon/jwt-auth)
- **Testing:** [PEST PHP](https://pestphp.com/docs/installation)
- **Static Analysis:** [PHPStan](https://phpstan.org/) / [Larastan](https://laravel-news.com/package/nunomaduro-larastan)
- **Database:** [PostgreSQL](https://www.postgresql.org/)
<br><br>

> **Note**: This project is intended for educational and evaluation purposes only. It is not production-ready, but can be extended for more complex scenarios. Contributions and suggestions are welcome!

> **Convention:** `$` at the start of a line means "run this command in your shell."

<br>

## <a id="infrastructure-platform"></a>Infrastructure Platform

You can use your own local infrastructure to clone and run this repository. However, if you use [GNU Make](https://www.gnu.org/software/make/) installed, we recommend using the dedicated Docker repository [**NGINX 1.28, PHP 8.3 - POSTGRES 17.5**](https://github.com/pabloripoll/docker-platform-nginx-php-8.3-pgsql-17.5)

With just a few configuration steps, you can quickly set up this project—or any other—with this same required stack.

**Repository directories structure overview:**
```
.
├── apirest (Laravel)
│   ├── app
│   ├── bootstrap
│   ├── vendor
│   └── ...
│
├── platform
│   ├── nginx-php
│   │   ├── docker
│   │   │   ├── config
│   │   │   │   ├── php
│   │   │   │   ├── nginx
│   │   │   │   └── supervisor
│   │   │   ├── .env
│   │   │   ├── docker-compose.yml
│   │   │   └── Dockerfile
│   │   │
│   │   └── Makefile
│   └── postgres-17.5
│       ├── docker
│       └── Makefile
├── .env
├── Makefile
└── README.md
```

Follow the documentation to implement it:
- https://github.com/pabloripoll/docker-platform-nginx-php-8.3-pgsql-17.5?tab=readme-ov-file#platform--usage
<br><br>

## <a id="apirest-laravel"></a>REST API - Laravel 12

The following steps assume you are using the recommended [NGINX-PHP with Postgres 17.5 platform repository](https://github.com/pabloripoll/docker-platform-nginx-php-8.3-pgsql-17.5).

Clone the repository
```bash
$ cd ./apirest
$ git clone https://github.com/your-username/social-feed-laravel.git .
```
<br>

Set up environment
- Copy `.env.example` to `.env` and adjust settings (database, JWT secret, etc.)
<br>

Access container to install the project
```bash
$ make apirest-ssh

/var/www $
```

Once accessed into the container, you will placed into root proyect directory at `/var/www`
```bash
/var/www $ composer install
```
<br>

Generate app key and JWT secret
```bash
/var/www $ php artisan key:generate
/var/www $ php artisan jwt:secret
```
<br>

Run database models migrations
```bash
/var/www $ php artisan migrate
```
<br>

<font color="orange"><b>IMPORTANT:</b></font> Editing project scripts and source code can be done directly `./apirest` on your local machine. Enter the container only when you need to run ***Composer*** or ***Laravel CLI (Artisan)*** commands.
<br><br>

## <a id="apirest-jwt"></a>API Authentication with JWT

This application uses JWT for stateless authentication:

- **Token lifecycle:**
  - Access tokens are valid for 90 minutes (JWT TTL), but the access token registry expiration is set to 60 minutes.
  - Tokens can only be refreshed if their expiration is recorded in the `members_access_logs` or `admins_access_logs` table.
  - When a token expires but is still eligible for refresh, the API responds with:
    ```bash
    HTTP CODE 403
    ```
    ```json
    {
        "message": "Token is expired.",
        "error": "token_expired"
    }
    ```
  - If a token is invalidated (e.g., via logout), or has expired beyond both the registry and JWT TTL, it cannot be refreshed.
<br><br>

## <a id="apirest-swagger"></a>Swagger API Documentation

The Swagger API documentation is available at:
`http://127.0.0.1:[selected-port]/api/documentation`

Before accessing it, you need to prepare the environment inside the container:

1. **Create a symlink for the API docs JSON and copy the Swagger UI assets:**
    ```bash
    /var/www $ ln -sf ./storage/api-docs/api-docs.json ./public/docs/api-docs.json
    /var/www $ cp -r vendor/swagger-api/swagger-ui/dist public/docs/asset
    ```

2. **Update the Swagger view:**
    The `./resources/views/vendor/l5-swagger/index.blade.php` file has been updated to properly serve the UI as follows:
    ```html
    <script>
        window.onload = function() {
            const urls = [{name: "API", url: "/docs/api-docs.json"}];

            /* @foreach($urlsToDocs as $title => $url)
                urls.push({name: "{{ $title }}", url: "{{ $url }}"});
            @endforeach */

            // ...
    ```

3. **Clear cache and generate documentation:**
    Run the following inside the container:
    ```bash
    /var/www $ php artisan view:clear && php artisan config:clear && php artisan cache:clear && php artisan l5-swagger:generate
    ```

Now you should be able to access the interactive API documentation on your local environment.

---

**Tip:** Replace `[selected-port]` with the actual port mapped to your container if it's not the default 80.
<br><br>

## <a id="apirest-ddd"></a>Domain Driven Design

Domain Driven Design (DDD) is a software development approach that emphasizes modeling software to match a business domain as closely as possible. In a DDD project, code is organized around the core business concepts, rules, and processes, rather than technical layers (like "Controllers" or "Models" globally).

There are several approaches to structuring a DDD project. In this project, each **Domain** is implemented as a modularized Service Provider within Laravel. This design promotes separation of concerns, encapsulation, and reusability.

### Key Characteristics of this DDD Approach

- **Domains as Modules:**
  Each business domain (such as "Admin", "Member", or "Post") is contained within its own directory under `app/Domain/config`, following a modular structure. This means each domain encapsulates its own controllers, models, requests, routes, services, and tests.

- **Service Providers:**
  Each domain registers a Laravel Service Provider (e.g., `MemberServiceProvider.php`), which is responsible for bootstrapping domain-specific bindings, event listeners, and routes. This makes domain logic easy to plug in or remove from the application.

- **Encapsulation:**
  By grouping all logic, data models, and services related to a domain together, each domain remains independent, preventing unintended coupling between features.

- **Scalability & Maintainability:**
  New domains or features can be added with minimal impact on existing code, and cross-domain interactions remain explicit and manageable.

### Project Structure Example

```
.
├── apirest (Laravel)
│   ├── app
│   │   ├── Domain
│   │   │   ├── config
│   │   │   │   ├── Admin
│   │   │   │   ├── Member
│   │   │   │   │   ├── Controller
│   │   │   │   │   ├── Database
│   │   │   │   │   ├── Models
│   │   │   │   │   ├── Requests
│   │   │   │   │   ├── Routes
│   │   │   │   │   ├── Service
│   │   │   │   │   ├── Tests
│   │   │   │   │   └── MemberServiceProvider.php
│   │   │   │   ├── Post
│   │   │   │   └── Shared
│   │   │   ├── Http
│   │   │   ├── Models
│   │   │   └── Providers
│   │   │
│   │   └── Makefile
│   ├── bootstrap
│   ├── config
│   ├── database
│   ├── public
```
<br>

## Contributing

Contributions are very welcome! Please open issues or submit PRs for improvements, new features, or bug fixes.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -am 'feat: Add new feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Create a new Pull Request
<br><br>

## License

This project is open-sourced under the [MIT license](LICENSE).

<!-- FOOTER -->
<br>

---

<br>

- [GO TOP ⮙](#top-header)

<div style="with:100%;height:auto;text-align:right;">
    <img src="./public/files/pr-banner-long.png">
</div>