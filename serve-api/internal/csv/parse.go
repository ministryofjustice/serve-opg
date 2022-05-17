package csv

import (
	"encoding/csv"
	"os"
	"strconv"
	"time"
)

type Case struct {
	CaseNumber string
	Forename   string
	Surname    string
	OrderType  int
	MadeDate   time.Time
	IssueDate  time.Time
	OrderNo    int
}

func Parse(filePath string) (*Case, error) {
	file, err := os.Open(filePath)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	csvReader := csv.NewReader(file)

	record, err := csvReader.Read()
	if err != nil {
		return nil, err
	}

	record, err = csvReader.Read()
	if err != nil {
		return nil, err
	}

	importedCase := Case{
		CaseNumber: record[0],
		Forename:   record[1],
		Surname:    record[2],
		OrderType:  parseStringAsInt(record[3]),
		MadeDate:   parseStringAsDate(record[4]),
		IssueDate:  parseStringAsDate(record[5]),
		OrderNo:    parseStringAsInt(record[6]),
	}

	return &importedCase, nil
}

func parseStringAsInt(str string) int {
	integer, err := strconv.Atoi(str)
	if err != nil {
		panic(err)
	}
	return integer
}

func parseStringAsDate(str string) time.Time {
	stringLayout := "01-Jan-2022"
	date, err := time.Parse(stringLayout, str)

	if err != nil {
		panic(err)
	}

	return date
}
