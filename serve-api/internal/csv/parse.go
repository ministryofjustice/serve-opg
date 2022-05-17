package csv

import (
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

func Parse(filePath string) (Case, error) {
	return Case{}, nil
}
