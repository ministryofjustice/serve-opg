# Testing
Serve OPG uses PHPUnit and Behat to test the application

## Unit and Functional Testing
Run php unit
```bash
docker-compose run --rm app bin/phpunit --verbose tests

# specific test (if unique)
docker-compose run --rm app bin/phpunit --verbose tests --filter testHomePage

# specific test (if not unique)
docker-compose run --rm app bin/phpunit --verbose tests --filter testHomePage tests/Controller/IndexControllerTest.php

# specific test using groups

Add a @group notation above the test method or class:

/**
  * @group failing
  */
public function testSomething()
{
...
}

# Then run:

docker-compose run --rm app bin/phpunit --verbose tests --group failing
```

Or using the Makefile:

`make phpunit-tests` - Runs the full suite of PHPUnit functional and unit tests - requires the app to be built and up before running

## Integration Testing
```bash
# Load Fixtures
docker-compose run --rm app php bin/console doctrine:fixtures:load --append

# Load Fixtures truncating existing data (users, client, orders, deputies)
docker-compose run --rm app php bin/console doctrine:fixtures:load --purge-with-truncate

# Run Behat
docker-compose run --rm behat --suite=local

# Launch specific behat feature
docker-compose run --rm behat features/00-security.feature:18
```

Or using the Makefile:

`make behat-tests` - Runs behat tests - requires the app to be built and up before running

### Notify mocking
Notify is mocked via a custom script.
Requests to the service can be seen at

`http://localhost:8081/mock-data`

Behat `NotifyTrait` takes care of resetting / accessing those emails from steps in the behat context.

# Quality Analysis Tools
The Docker image `jakzal/phpqa` contains many useful QA tools
To list the available tools run:
```shell
docker-compose run --rm qa
```

A recommended set of checks is as follows:
-   phpcs
    ```bash
    docker-compose run --rm qa phpcs src
    ```
-   phpstan
    ```bash
    docker-compose run --rm qa phpstan analyse -l 4 src
    ```
-   lint
    ```bash
    docker-compose run --rm qa parallel-lint src web app tests
    ```
-   security-checker
    ```bash
    docker-compose run --rm qa security-checker security:check
    ```

A convenience script is provided for the above set:
```bash
docker-compose run --rm qa ./default_qa_checks.sh
```
