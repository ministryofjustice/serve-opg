<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    private function getProjectPath() {
        return realpath(dirname(__FILE__));
    }

    // define public methods as commands
    public function logs()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose logs -f');
    }

    public function up()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose up --force-recreate -d frontend');
    }

    public function down()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose down');
    }

    public function ps()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose ps');
    }

    public function phpunit()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose run --rm phpunit');
    }

    public function behat()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose up -d frontend');
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose run --rm behat');
    }

    public function composer()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose run --rm composer');
    }

    public function frontend_setup()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose run --rm node /entrypoint-setup.sh');
    }

    public function frontend_gen()
    {
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose run --rm node /entrypoint-generate.sh');
    }

    public function shell($service)
    {
      docker-compose exec nginx bash
      docker-compose exec php bash

      docker-compose run --entrypoint="bash" node
      docker-compose run --entrypoint="bash" composer
      docker-compose run --entrypoint="bash" behat
      docker-compose run --entrypoint="bash" phpunit
        $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose run --rm node /entrypoint-generate.sh');
    }

    // public function rebuild()
    // {
    //     $this->_exec('cd ' . $this->getProjectPath() . ' && docker-compose');
    // }
}
