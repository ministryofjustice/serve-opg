package cache

import (
	"os"

	"github.com/aws/aws-sdk-go/service/secretsmanager"
	"github.com/aws/aws-secretsmanager-caching-go/secretcache"
	"github.com/ministryofjustice/serve-opg/serve-api/internal/session"
)

// SecretCache stores the environment we are on
// and a cache client for AWS Secrets Manager secrets
type SecretsCache struct {
	env   string
	cache AwsSecretsCache
}

type AwsSecretsCache interface {
	GetSecretString(secretId string) (string, error)
}

func applyAwsConfig(c *secretcache.Cache) {
	sess, _ := session.NewSession()
	endpoint := os.Getenv("SECRETS_MANAGER_ENDPOINT")
	sess.AWSSession.Config.Endpoint = &endpoint
	c.Client = secretsmanager.New(sess.AWSSession)
}

// New constructs a new SecretsCache with the environment we are on
// and a cache client for AWS Secrets Manager secrets
func New() *SecretsCache {
	env := os.Getenv("ENVIRONMENT")
	cache, _ := secretcache.New(applyAwsConfig)
	return &SecretsCache{env, cache}
}

// GetSecretString gets the secret string value from the cache for given secret id and a default version stage.
// Returns the secret string and an error if operation failed.
func (c *SecretsCache) GetSecretString(key string) (string, error) {
	return c.cache.GetSecretString(c.env + "/" + key)
}
