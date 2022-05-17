package csv

import (
	"encoding/csv"
	"errors"
	"os"
	"strconv"
	"time"
)

// Case respresents a single case
type Case struct {
	CaseNumber string
	Forename   string
	Surname    string
	OrderType  int
	MadeDate   time.Time
	IssueDate  time.Time
	OrderNo    int
}

// Prase will prase an CSV file located in the filePath that is passed.
// This will then return a list of Cases. If there is an error, then we return an empty list
// of cases with an error
func Parse(filePath string) ([]Case, error) {
	file, err := os.Open(filePath)
	if err != nil {
		return []Case{}, errors.New("failed to open file")
	}
	defer file.Close()

	records, err := csv.NewReader(file).ReadAll()
	if err != nil {
		return []Case{}, errors.New("failed to read csv")
	}

	var importedCases []Case

	for i, record := range records {
		if i == 0 {
			continue
		}

		singleCase := Case{
			CaseNumber: record[0],
			Forename:   record[1],
			Surname:    record[2],
			OrderType:  parseStringAsInt(record[3]),
			MadeDate:   parseStringAsDate(record[4]),
			IssueDate:  parseStringAsDate(record[5]),
			OrderNo:    parseStringAsInt(record[6]),
		}

		importedCases = append(importedCases, singleCase)
	}

	return importedCases, nil
}

func parseStringAsInt(str string) int {
	integer, err := strconv.Atoi(str)
	if err != nil {
		panic(err)
	}
	return integer
}

func parseStringAsDate(str string) time.Time {
	stringLayout := "2-Jan-2006"
	date, err := time.Parse(stringLayout, str)

	if err != nil {
		panic(err)
	}

	return date
}
