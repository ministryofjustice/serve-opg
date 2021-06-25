package cache

import (
	"os"

	"github.com/aws/aws-sdk-go/service/secretsmanager"
	"github.com/aws/aws-secretsmanager-caching-go/secretcache"
	"github.com/ministryofjustice/serve-opg/serve-api/internal/session"
)

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

func New() *SecretsCache {
	env := os.Getenv("ENVIRONMENT")
	cache, _ := secretcache.New(applyAwsConfig)
	return &SecretsCache{env, cache}
}

func (c *SecretsCache) GetSecretString(key string) (string, error) {
	return c.cache.GetSecretString(c.env + "/" + key)
}
