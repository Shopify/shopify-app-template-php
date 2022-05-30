if [[ -z "${DB_DATABASE}" ]]; then
  echo "Database not set up yet, skipping"
else
  php artisan migrate --force
fi

echo "Shopify release done!"
