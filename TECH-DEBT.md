# Snags
- Check if we need the following items in nginx:
  - `chunked_transfer_encoding off;`
  - `add_header X-Frame-Options "SAMEORIGIN";`
  - `add_header X-XSS-Protection "1; mode=block";`
  - `add_header X-Content-Type-Options nosniff;`
  - `add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload";`
- dynamoDB required for app to startup
- smoke test for behat controller in production
- move create buckets step

# Solved
- ~~add time based assets directory~~
- ~~Testing pipeline not complete. What does this look like?~~
- ~~Can we remove `getTag` from serve-opg/src/AppBundle/Twig/AssetsExtension.php ?~~
- ~~Behat requires database drivers to be installed:~~
  - `An exception occurred in driver: could not find driver (Doctrine\DBAL\Exception\DriverException)`
- ~~No codeowners file~~
- ~~App needs to be warmed up~~
- ~~PostgreSQL required for app to startup~~
- ~~nginx config needs templating to accept different hostnames~~
- ~~use arrays for cmd/entrypoint in compose files~~
- ~~push version back to git~~
- ~~make version available to app~~
- ~~move fakes3 license key to env var~~

