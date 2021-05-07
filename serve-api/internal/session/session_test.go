package session

import (
	"os"
	"testing"

	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/stretchr/testify/assert"
)

func TestNewSession(t *testing.T) {
	testCases := []struct {
		region     *string
		role       *string
		wantRegion string
		wantError  bool
	}{
		{nil, nil, "eu-west-1", false},
		{aws.String(""), nil, "eu-west-1", false},
	}

	for _, tc := range testCases {
		os.Unsetenv("AWS_REGION")
		os.Unsetenv("AWS_IAM_ROLE")

		if tc.region != nil {
			os.Setenv("AWS_REGION", *tc.region)
		}

		if tc.role != nil {
			os.Setenv("AWS_IAM_ROLE", *tc.role)
		}

		got, err := NewSession()

		if tc.wantError {
			assert.Error(t, err)
		} else {
			assert.Nil(t, err)
		}

		assert.IsType(t, new(session.Session), got.AwsSession)
		assert.Equal(t, tc.wantRegion, *got.AwsSession.Config.Region)
	}

}
