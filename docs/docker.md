# Docker Architecture

The application uses a multi-container Docker architecture with specialized services.

## Services

| Service           | Role                     | Description                                                                 |
|-------------------|--------------------------|-----------------------------------------------------------------------------|
| `api`             | Web Server               | Serves the Laravel application via Apache                                   |
| `scheduler`       | Task Scheduler           | Runs Laravel's scheduled tasks                                              |
| `queue`           | Default Queue Worker     | Processes jobs from the `default` queue, game data import, comm-links, etc. |
| `queue_expensive` | Specialized Queue Worker | Processes `expensive` queue jobs, image hash calculation                    |
| `db`              | Database                 | PostgreSQL database server                                                  |

## Container Roles

The `CONTAINER_ROLE` environment variable determines what each container runs:

- **`app`**: Starts Apache to serve web requests
- **`scheduler`**: Runs `php artisan schedule:work` to execute scheduled commands
- **`queue`**: Runs `php artisan queue:work` to process queued jobs
  - Customize with: `QUEUE_NAME`, `QUEUE_TRIES`, `QUEUE_MAX_JOBS`, `QUEUE_SLEEP`

## Queue Architecture

The application uses multiple queue workers for specialized workloads:

- **Default Queue** (`queue` service): General background jobs (imports, syncs, translations)
- **Expensive Queue** (`queue_expensive` service): Heavy tasks like image hashing and similarity computation

## Volumes

| Mount          | Purpose                                                            |
|----------------|--------------------------------------------------------------------|
| `./storage`    | Laravel storage (logs, cache, uploaded files, API data submodules) |
| `./var/lib/db` | PostgreSQL data directory                                          |

## Traefik Integration (Optional)

For production deployments with automatic HTTPS:

```bash
docker compose -f compose.yaml -f compose.traefik.yaml up -d
```

This enables automatic SSL certificate management and reverse proxy capabilities.
