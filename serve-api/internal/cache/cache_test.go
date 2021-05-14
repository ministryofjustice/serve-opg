package cache

import (
	"errors"
	"os"
	"testing"

	"github.com/aws/aws-sdk-go/service/secretsmanager"
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

	for _, tc := range testCases {
		m := new(MockAWSSecretsCache)
		m.On("GetSecretString", tc.env+"/"+tc.secretKey).Return(tc.returnSecret, tc.returnErr).Times(1)
		sc := SecretsCache{
			env:   tc.env,
			cache: m,
		}

		secret, err := sc.GetSecretString(tc.secretKey)
		assert.Equal(t, tc.returnSecret, secret, tc.scenario)
		assert.Equal(t, tc.returnErr, err, tc.scenario)
	}
}

func TestApplyAWSConfig(t *testing.T) {
	testCases := []struct {
		scenario   string
		endpoint   string
		region     string
		wantRegion string
		role       string
	}{
		{
			scenario:   "blank aws region",
			endpoint:   "test_endpoint",
			region:     "",
			wantRegion: "eu-west-1",
			role:       "test-role",
		},
		{
			scenario:   "custom aws region",
			endpoint:   "test_endpoint",
			region:     "eu-west-2",
			wantRegion: "eu-west-2",
			role:       "test-role",
		},
		{
			scenario:   "",
			endpoint:   "test_endpoint",
			region:     "eu-west-2",
			wantRegion: "eu-west-2",
			role:       "",
		},
	}

	for _, tc := range testCases {
		oldEndpoint := os.Getenv("SECRETS_MANAGER_ENDPOINT")
		oldRegion := os.Getenv("AWS_REGION")
		oldRole := os.Getenv("AWS_IAM_ROLE")

		_ = os.Setenv("SECRETS_MANAGER_ENDPOINT", tc.endpoint)
		if tc.region == "" {
			_ = os.Unsetenv("AWS_REGION")
		} else {
			_ = os.Setenv("AWS_REGION", tc.region)
		}

		if tc.role == "" {
			_ = os.Unsetenv("AWS_IAM_ROLE")
		} else {
			_ = os.Setenv("AWS_IAM_ROLE", tc.role)
		}

		c := new(secretcache.Cache)
		applyAwsConfig(c)

		cl := c.Client.(*secretsmanager.SecretsManager)

		assert.Equal(t, "https://"+tc.endpoint, cl.Endpoint, tc.scenario)
		assert.Equal(t, tc.wantRegion, *cl.Config.Region, tc.scenario)

		_ = os.Setenv("SECRETS_MANAGER_ENDPOINT", oldEndpoint)
		_ = os.Setenv("AWS_REGION", oldRegion)

		if oldRole == "" {
			_ = os.Unsetenv("AWS_IAM_ROLE")
		} else {
			_ = os.Setenv("AWS_IAM_ROLE", oldRole)
		}
	}
}
