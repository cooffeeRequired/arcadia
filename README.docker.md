# Docker Setup for Arcadia CRM

This document explains how to run Arcadia CRM using Docker.

## Prerequisites

- Docker
- Docker Compose

## Getting Started

1. Clone the repository:
   ```
   git clone <repository-url>
   cd Arcadia
   ```

2. Start the Docker containers:
   ```
   docker-compose up -d
   ```

3. Access the application:
   ```
   http://localhost:8000
   ```

## Services

The Docker setup includes the following services:

- **PHP-FPM (app)**: Runs the PHP application
- **Nginx (webserver)**: Serves the application on port 8000
- **MySQL (db)**: Database server

## Configuration

- Database credentials are defined in the `docker-compose.yml` file
- PHP configuration is in `php/local.ini`
- Nginx configuration is in `nginx/conf.d/app.conf`

## Development Workflow

1. Make changes to your code locally
2. The changes will be reflected immediately due to volume mounting

## Troubleshooting

- Check logs with `docker-compose logs`
- For specific service logs: `docker-compose logs [service]` (e.g., `docker-compose logs app`)