1. Check if we need the following items in nginx:
  - `chunked_transfer_encoding off;`
  - `add_header X-Frame-Options "SAMEORIGIN";`
  - `add_header X-XSS-Protection "1; mode=block";`
  - `add_header X-Content-Type-Options nosniff;`
  - `add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload";`
2. Can we remove `getTag` from digicop/src/AppBundle/Twig/AssetsExtension.php ?
3. ~~Behat requires database drivers to be installed:~~
  - `An exception occurred in driver: could not find driver (Doctrine\DBAL\Exception\DriverException)`
4. ~~No codeowners file~~
5. ~~App needs to be warmed up~~
6. Testing pipeline not complete. What does this look like?
7. dynamoDB required for app to startup
8. ~~PostgreSQL required for app to startup~~
9. ~~nginx config needs templating to accept different hostnames~~
10. add time based assets directory
11. ~~use arrays for cmd/entrypoint in compose files~~
