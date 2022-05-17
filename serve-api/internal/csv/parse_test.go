package csv

import (
	"errors"
	"github.com/stretchr/testify/assert"
	"testing"
	"time"
)

func TestParse(t *testing.T) {
	testCases := []struct {
		desc          string
		filePath      string
		expectedCases []*Case
		expectedError error
	}{
		{
			desc:          "1. Passing in a invalid csv filepath",
			filePath:      "invalid_path.csv",
			expectedCases: nil,
			expectedError: errors.New("failed to open file"),
		},
		{
			desc:     "2. Passing in a CSV that contains a single row of data",
			filePath: "single_case.csv",
			expectedCases: []*Case{
				&Case{
					CaseNumber: "12345678",
					Forename:   "John",
					Surname:    "Smith",
					OrderType:  1,
					MadeDate:   time.Date(2018, 8, 15, 0, 0, 0, 0, time.UTC),
					IssueDate:  time.Date(2022, 5, 17, 0, 0, 0, 0, time.UTC),
					OrderNo:    1000,
				},
			},
			expectedError: nil,
		},
		{
			desc:     "3. Passing in a CSV that contains a multiple rows of data",
			filePath: "multiple_cases.csv",
			expectedCases: []*Case{
				&Case{
					CaseNumber: "12345678",
					Forename:   "John",
					Surname:    "Smith",
					OrderType:  1,
					MadeDate:   time.Date(2018, 8, 15, 0, 0, 0, 0, time.UTC),
					IssueDate:  time.Date(2022, 5, 17, 0, 0, 0, 0, time.UTC),
					OrderNo:    1000,
				},
				&Case{
					CaseNumber: "87654321",
					Forename:   "Jonah",
					Surname:    "Smith",
					OrderType:  2,
					MadeDate:   time.Date(2016, 7, 14, 0, 0, 0, 0, time.UTC),
					IssueDate:  time.Date(2021, 4, 16, 0, 0, 0, 0, time.UTC),
					OrderNo:    1001,
				},
			},
			expectedError: nil,
		},
	}

	for _, tc := range testCases {
		got, err := Parse(tc.filePath)

		assert.Equal(t, tc.expectedCases, got)
		assert.Equal(t, tc.expectedError, err)
	}
}
