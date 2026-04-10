<div id="top-header" style="with:100%;height:auto;text-align:right;">
    <img src="./public/files/pr-banner-long.png">
</div>

# SOCIAL FEED - LARAVEL 12

This repository contains a basic example of a RESTful API service built with **Laravel 12**, intended for research purposes and as a demonstration of my developer profile. It implements the core features of a minimal, custom social feed application and serves as a reference project for learning, experimentation, or as a back-end development code sample.

> ⚠️ **Project Status: In Development**
>
> This repository is currently under active development and not yet ready for use. Features and APIs may change, and breaking changes can occur. Please, be aware that the codebase is actively evolving.

## Project Overview

The API supports a registry of platform "members," enabling users to create posts and voting with like or dislike other users' posts. An administrator role is provided for managing users, content, and platform statistics via a dedicated back office.

## Content of this page:

- [REST API Features](#apirest-features)
- [Infrastructure Platform](#infrastructure-platform)
- [REST API - Laravel 12](#apirest-setup)
- [API Authentication with JWT](#apirest-jwt)
- [Swagger API Documentation](#apirest-swagger)
- [Modular Monolith REST API](#apirest-structure-design)
- [Use this Platform Repository for REST API project](#platform-usage)
- [API Testing](#apirest-testing)
<br><br>

## <a id="apirest-features"></a>REST API Features

- **RESTful API** — Follows common REST patterns for resource-oriented endpoints.
- **Stateless API** — Each request is self-contained, adhering to REST principles.
- **Modular Monolith REST API** — Each module is self-contained in a single directory, except for resources specific to the framework.
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
- **PHPDocs:** [Barry vd. Heuvel - IDE Helper for Laravel](https://github.com/barryvdh/laravel-ide-helper)
- **Database:** [PostgreSQL](https://www.postgresql.org/)
<br><br>

> **Note**: This project is intended for educational and evaluation purposes only. It is not production-ready, but can be extended for more complex scenarios. Contributions and suggestions are welcome!

> **Convention:** `$` at the start of a line means "run this command in your shell."

<br>

## <a id="infrastructure-platform"></a>Infrastructure Platform

You can use your own local infrastructure to clone and run this repository. However, if you use [GNU Make](https://www.gnu.org/software/make/) installed, we recommend using the dedicated Docker repository [**NGINX 1.28, PHP 8.5 - POSTGRES 18.2**](https://github.com/pabloripoll/platforms-docker-nginx-php-8.5-pgsql-18.2-mailhog-rabbitmq)

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
│   └── postgres-18.2
│       ├── docker
│       └── Makefile
├── .env
├── Makefile
└── README.md
```

Follow the documentation to implement it:
- https://github.com/pabloripoll/platforms-docker-nginx-php-8.5-pgsql-18.2-mailhog-rabbitmq
<br><br>

## <a id="apirest-setup"></a>REST API - Laravel 12

The following steps assume you are using the recommended [NGINX-PHP with Postgres 18.2 platform repository](https://github.com/pabloripoll/platforms-docker-nginx-php-8.5-pgsql-18.2-mailhog-rabbitmq).

Clone the repository
```bash
$ cd ./apirest
$ git clone https://github.com/pabloripoll/social-rest-api-laravel-12-postgres .
```
<br>

Set up environment
- Copy `.env.example` to `.env` and adjust settings (database, JWT secret, etc.)
<br>

Access container to install the project
```bash
$ make apirest-ssh
```

Once accessed into the container, you will placed into root proyect directory at `/var/www`. Install the project
```bash
/var/www $ composer install
```
<br>

Generate Laravel app key and JWT secret
```bash
# JWT package installed: tymon/jwt-auth
/var/www $ php artisan key:generate
/var/www $ php artisan jwt:secret
```
<br>

Run database models migrations
```bash
/var/www $ php artisan migrate
```
<br>

Run base data seed
```bash
/var/www $ php artisan db:seed
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

## <a id="apirest-structure-design"></a>Modular Monolith REST API

A Modular Monolith is a software architecture approach where the application is deployed as a single unit, but internally organized into well-defined, self-contained modules. In a REST API project, this means the codebase is structured around business capabilities or features rather than only technical layers, while still remaining part of one application.

This approach combines the simplicity of a monolith with the maintainability of modular design. Each module owns the code related to its feature, such as controllers, requests, routes, application services, domain objects, repositories, and tests, helping reduce coupling and improve clarity.

This approach is a strong foundation before adopting DDD, Hexagonal Architecture, with or without CQRS, because it encourages clear boundaries, explicit dependencies, and better separation of concerns from the beginning.

### Key Characteristics of this Modular Monolith Approach

- **Modules as Functional Boundaries**: Each business area, such as Admin, Member, or Post, is organized into its own module directory, making responsibilities clearer.

- **Encapsulation by Feature**: Each module contains the logic, validation, and API endpoints needed for that feature, reducing leakage of implementation details across the application.

- **Single Deployable Unit**: Unlike a microservices architecture, the application is built, tested, and deployed as one system, which simplifies operations, local development, and debugging.

- **Clear Internal Contracts**: Modules communicate through explicit interfaces, services, or events, which reduces tight coupling and makes dependencies easier to manage.

- **Scalability and Maintainability**: New functionality can be added by introducing new modules or extending existing ones without heavily impacting unrelated parts of the codebase.

- **Better Team Organization**: Different developers or teams can work on separate modules with reduced overlap, improving ownership and parallel development.

### Project Structure Example

```
.
├── apirest (Laravel)
│   ├── app
│   │   ├── Console
│   │   ├── Modules
│   │   │   ├── User
│   │   │   │   ├── Controller
│   │   │   │   ├── Database
│   │   │   │   ├── Dto
│   │   │   │   ├── Models
│   │   │   │   ├── Requests
│   │   │   │   ├── Resources
│   │   │   │   ├── Routes
│   │   │   │   ├── Service
│   │   │   │   └── Tests
│   │   │   ├── Admin
│   │   │   ├── Member
│   │   │   └── ...
│   │   │
│   │   ├── Http
│   │   ├── Providers
│   │   └── ...
│   │
│   ├── bootstrap
│   ├── bootstrap
│   ├── config
│   ├── database
│   ├── public
│   ├── Makefile
│   ├── vendor
│   └── ...
```
<br>

## <a id="apirest-test-automated"></a>REST API Automated Tests

There are some tests on each Domain. There are some unit and integration tests. To run the tests, first you need to create the testing database. There is a GNU Make recipe to do so. The name of the testing database will be created automatically adding to the database name set on the [Platform Repository Environment file](https://github.com/pabloripoll/docker-platform-nginx-php-8.3-pgsql-16.4/blob/main/.env.example): *[DATABASE_NAME]_testing*

Remember to have set the [./.env.testing](./.env.testing) file to perform the tests
```bash
$ make db-test-up
```

Once created, access to container terminal an run the tests
```bash
$ make apirest-ssh

/var/www $ php artisan test
```
<br>

Also you can run specific tests
```bash
/var/www $ php artisan test ./app/Modules/User/Tests/UserMemberAuthTest.php
```
<br>

## <a id="apirest-test-static"></a>REST API Static Tests

Access to REST API container terminal an run the static analisys test with PHPStan
```bash
/var/www $ composer phpstan ./app/Modules/User/
```

***It's recommended to run static tests on specific locations intead of the whole framework***
<br>

Also you can run static test on a specific script
```bash
/var/www $ composer phpstan ./app/Modules/User/Tests/UserAdminAuthTest.php
```
<br>

## <a id="apirest-phpdocs"></a>PHPDocs

Keep updated PHPDocs for models that will help static tests
```bash
/var/www $ php artisan ide-helper:models
```
<br><br>

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