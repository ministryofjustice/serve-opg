package csv

import (
	"testing"
	"time"

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
		{
			desc:     "2. Passing in a CSV that contains a single row",
			filePath: "cases.csv",
			expectedCase: Case{
				CaseNumber: "12345678",
				Forename:   "John",
				Surname:    "Smith",
				OrderType:  1,
				MadeDate:   time.Date(2022, 5, 17, 0, 0, 0, 0, time.UTC),
				IssueDate:  time.Date(2022, 5, 17, 0, 0, 0, 0, time.UTC),
				OrderNo:    1000,
			},
			expectedError: nil,
		},
	}

	for _, tc := range testCases {
		got, err := Parse(tc.filePath)

		assert.Equal(t, tc.expectedCase, got)
		assert.Equal(t, tc.expectedError, err)
	}
}
