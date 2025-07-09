<?php

namespace App\Service;

use Aws\SecretsManager\SecretsManagerClient;

class PasswordProvider
{
    private SecretsManagerClient $secrets;

    public function __construct(
        private readonly string $environmentName,
    ) {
        if ('local' === $this->environmentName) {
            $endpoint = 'http://localstack:4566';
            $this->secrets = new SecretsManagerClient([
                'region' => 'eu-west-1',
                'version' => '2017-10-17',
                'endpoint' => $endpoint,
            ]);
        } else {
            $this->secrets = new SecretsManagerClient([
                'region' => 'eu-west-1',
                'version' => '2017-10-17',
            ]);
        }
    }

    public function getEnvPassword(string $dbPasswordEnvVar): ?string
    {
        return getenv($dbPasswordEnvVar) ?: 'FALLBACK_PASSWORD';
    }

    public function fetchFromSecretsManager(): string
    {
        $secretName = 'database_password';
        $result = $this->secrets->getSecretValue(['SecretId' => $secretName]);

        return $result['SecretString'];
    }
}
