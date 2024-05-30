# Smooth Bills

Smooth Bills is a web application developed in Symfony for managing quotations and invoices efficiently.

## Requirements

| Name                                                               | Version |
| ------------------------------------------------------------------ | ------- |
| <a name="requirement_php"></a> [php](#requirement_php)             | >=8.3.2 |
| <a name="requirement_symfony"></a> [Symfony](#requirement_symfony) | >=6.4   |

## Getting Started

If not already done, install Docker Compose (v2.10+).

1. Run `docker compose build --no-cache` or `make build` to build fresh images.
2. Run `docker compose up --pull always -d --wait` or `make up` to set up and start a fresh Symfony project.
3. Run `docker compose exec php composer install` or `make install` to install required dependancies.
4. Run `npm install` to install required packages for TailwindCSS.
5. Run `npm run watch` to start the tailwind build process.
6. Open `https://localhost` in your favorite web browser and accept the auto-generated TLS certificate.
7. If `https://localhost` don't work as expected, run `php -S localhost:8000 -t public` and open `https://localhost:8000` or `make run`.
8. Run `docker compose down --remove-orphans` or `make down` to stop the Docker containers.

For more shortcuts, take a look at the [Makefile](./Makefile).

## Features

-   Manage quotations and invoices seamlessly.
-   User-friendly interface for easy navigation and interaction.
-   Secure authentication and authorization mechanisms.
-   Customizable settings and configurations to adapt to your business needs.

## Documentation

1. [Build options](docs/build.md).
2. [Symfony Console Command](docs/symfony_command.md).
3. [Automatic management of `created_at` and `updated_at` fields](docs/timestampable_trait.md).

## License

This project is licensed under the [MIT License](LICENSE).

## Credit

Smooth Bills is developed and maintained by [Omer DOTCHAMOU](https://www.omerdotchamou.com), [Saidou IBRAHIM](https://github.com/isaidou), [Johnny CHEN](https://github.com/johnnyhelloworld) and [Faez BACAR ZOUBEIRI](https://github.com/FAEZ10).
