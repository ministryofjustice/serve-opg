<?php

use Aws\Symfony\AwsBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use EightPoints\Bundle\GuzzleBundle\EightPointsGuzzleBundle;
use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

return [
    AwsBundle::class => ['all' => true],
    FrameworkBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    SecurityBundle::class => ['all' => true],
    DebugBundle::class => ['dev' => true, 'test' => true],
    FriendsOfBehatSymfonyExtensionBundle::class => ['all' => true],
    EightPointsGuzzleBundle::class => ['all' => true],
];
