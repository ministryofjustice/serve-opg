# Debugging
Login to Database
```bash
docker-compose exec postgres psql -U serve-opg
```

Clear Cache
```bash
docker-compose exec app rm -rf /var/www/var/cache /tmp/app-cache
```

# Xdebug
To enable Xdebug running via Docker in PHPStorm you will need to:

- In `Preferences > Build, Execution, Deployment > Docker` select `Docker for Mac`
- In `Preferences > Languages and Frameworks > PHP` Click the `...` button next to `CLI Interpreter`
- Click the `+` button to add a new CLI  and select `From Docker, Vagrant, VM, Remote`
- Select `Docker Compose`, for `Server` choose `Docker` and select `app` for Service. Click `OK` and `Apply`.
- Click `Run > Edit Configurations` from the top menu bar, then `+` and select `PhpUnit`
- Name this configuration `Docker`, under `Test Scope` select `Directory` add `tests` directory as the filepath. Click `OK`.
- In `Preferences > Language & Frameworks > PHP > Debug` under `Xdebug > Debug port` enter `10000` . Hit `Apply` and `OK`.

As Xdebug has a large performance hit, it is not installed as part of the Dockerfile by default. Instead it is set as a build argument in docker-compose.local.yml to ensure it will only ever be enabled for local dev. To build the app image with xdebug enabled, run:

`docker-compose -f docker-compose.local.yml -f docker-compose.yml up -d --build --remove-orphans loadbalancer`

or

`make up-dev`

Now you can add break points to any line of code by clicking in the gutter next to line numbers. Then you can either run the entire test suite by selecting `DOCKER` from the dropdown next to the test buttons in the top right of the page and click the phone icon so it turns green. Hit the debug button to run the suite.

Alternatively you can run individual tests by hitting the debug button next to the test method name in the test class. Once the code gets to a break point you can step through and run executions on the current state of the app to help with debugging.
