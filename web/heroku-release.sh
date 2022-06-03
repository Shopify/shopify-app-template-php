echo "Releasing Shopify app"

if [[ -z "${DB_DATABASE}" ]]; then
  echo "Database not set up yet, skipping"
else
  php artisan migrate --force
fi

cd frontend
npm install
npm run build

cd ..
composer build-frontend-links

echo "Shopify release done!"
