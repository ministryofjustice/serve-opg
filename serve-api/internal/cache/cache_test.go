package cache

import (
	"os"
	"testing"

	"github.com/aws/aws-secretsmanager-caching-go/secretcache"
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
	assert.IsType(t, new(secretcache.Cache), sc.cache)

	_ = os.Setenv("ENVIRONMENT", oldEnv)
}

/*
func TestSecretCache_GetSecretString(t *testing.T) {
	testCases := []struct {
		scenario     string
		env          string
		secretKey    string
		returnSecret string
		returnErr    error
	}{
		{
			scenario:     "Secret retrieved successfully",
			env:          "test_env",
			secretKey:    "test_key",
			returnSecret: "test_secret",
			returnErr:    nil,
		},
		{
			scenario:     "Secret retrieved unsuccessfully",
			env:          "test_env",
			secretKey:    "test_key",
			returnSecret: "",
			returnErr:    errors.New("Test Error"),
		},
		{
			scenario:     "No environmnent set",
			env:          "",
			secretKey:    "test_key",
			returnSecret: "",
			returnErr:    errors.New("Test Error"),
		},
	}

}*/
