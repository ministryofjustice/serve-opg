package cache

import (
	"os"
	"testing"

	"github.com/aws/aws-secretsmanager-caching-go/secretscache"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
)

type MockAWSSecretsCache struct {
	mock.Mock
}

func (m *MockAWSSecretsCache) GetSecretString(secretID string) (string, error) {
	args := m.Called(secretID)
	return args.Get(0).(string), args.Error(1)
}

func TestNew(t *testing.T) {
	oldEnv := os.Getenv("ENVIRONMENT")
	_ = os.Setenv("ENVIRONMENT", "test_env")

	sc := New()
	assert.IsType(t, new(SecretsCache), sc)
	assert.Equal(t, "test_env", sc.env)
	assert.IsType(t, new(secretscache.Cache), sc.cache)

	_ = os.Setenv("ENVIRONMENT", oldEnv)
}
