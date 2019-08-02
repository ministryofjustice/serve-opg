<?php declare(strict_types=1);


namespace App\Tests\Helpers;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTestHelper extends WebTestCase
{
    public static function deleteMigrationVersion(string $version)
    {
        $application = self::createApplication();
        $command = $application->find('doctrine:migrations:version');
        $commandTester = new CommandTester($command);
        // Answer yes to migration - --no-interaction doesn't work for CommandTester
        $commandTester->setInputs(['y']);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--delete' => ' ',
            'version' => $version,
        ]);
    }

    public static function migrateUpToVersion(string $version)
    {
        $application = self::createApplication();
        $command = $application->find('doctrine:migrations:execute');
        $commandTester = new CommandTester($command);
        // Answer yes to migration - --no-interaction doesn't work for CommandTester
        $commandTester->setInputs(['y']);
        $commandTester->execute([
            'command'  => $command->getName(),
            'version' => $version,
            '--up' => '',
        ]);
    }

    public static function runMigrations()
    {
        $application = self::createApplication();
        $command = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        // Answer yes to migration - --no-interaction doesn't work for CommandTester
        $commandTester->setInputs(['y']);
        $commandTester->execute([
            'command'  => $command->getName(),
        ]);
    }

    protected static function createApplication()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        return $application;
    }
}
