package csv

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestParse(t *testing.T) {
	testCases := []struct {
		desc          string
		filePath      string
		expectedCase  Case
		expectedError error
	}{
		{
			desc:          "1. Passing in a valid csv filepath",
			filePath:      "cases.csv",
			expectedCase:  Case{},
			expectedError: nil,
		},
	}

	for _, tc := range testCases {
		got, err := Parse(tc.filePath)

		assert.Equal(t, tc.expectedCase, got)
		assert.Equal(t, tc.expectedError, err)
	}
}
