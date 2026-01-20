#!/usr/bin/env bash
set -e

role="${CONTAINER_ROLE:-app}"
env="${APP_ENV:-production}"

if [ "$env" != "local" ]; then
  echo "Caching configuration..."
  php artisan config:cache

  if [ "$role" = "app" ]; then
    php artisan view:cache
  fi
fi

case "$role" in
  app)
    exec apache2-foreground
    ;;

  queue)
    echo "Running the queue worker..."
    queue_conn="${QUEUE_CONNECTION_NAME:-}"     # optional, e.g. "redis"
    queue_name="${QUEUE_NAME:-default}"
    tries="${QUEUE_TRIES:-3}"
    timeout="${QUEUE_TIMEOUT:-90}"
    sleep_secs="${QUEUE_SLEEP:-3}"
    memory="${QUEUE_MEMORY:-512}"
    max_jobs="${QUEUE_MAX_JOBS:-0}"
    max_time="${QUEUE_MAX_TIME:-0}"

    args=(artisan queue:work --verbose --tries="$tries" --timeout="$timeout" --sleep="$sleep_secs" --memory="$memory" --queue="$queue_name")

    if [ -n "$queue_conn" ]; then
      args=(artisan queue:work "$queue_conn" --verbose --tries="$tries" --timeout="$timeout" --sleep="$sleep_secs" --memory="$memory" --queue="$queue_name")
    fi

    if [ "$max_jobs" -gt 0 ]; then
      args+=("--max-jobs=$max_jobs")
    fi

    if [ "$max_time" -gt 0 ]; then
      args+=("--max-time=$max_time")
    fi

    exec php "${args[@]}"
    ;;

  scheduler)
    echo "Running the scheduler..."
    exec php artisan schedule:work --verbose --no-interaction
    ;;

  *)
    echo "Could not match the container role \"$role\""
    exit 1
    ;;
esac
