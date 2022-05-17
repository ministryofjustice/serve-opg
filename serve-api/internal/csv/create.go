package csv

import (
	"encoding/csv"
	"log"
	"os"
	"time"
)

// Order is a struct that represents an order
type Order struct {
	IssuedAt        time.Time
	ServedAt        time.Time
	CaseNumber      string
	AppointmentType string
	OrderType       string
}

// CreateNewCSV will create a new CSV file
func CreateNewCSV() {
	myOrder := Order{
		time.Date(2020, time.November, 01, 00, 00, 00, 00, time.UTC),
		time.Date(2020, time.November, 01, 00, 00, 00, 00, time.UTC),
		"80000000",
		"Joint",
		"HW",
	}

	orders := [][]string{
		{"DateIssued", "DateServed", "CaseNumber", "AppointmentType", "OrderType"},
		{
			myOrder.IssuedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			myOrder.ServedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			myOrder.CaseNumber,
			myOrder.AppointmentType,
			myOrder.OrderType,
		},
		{
			myOrder.IssuedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			myOrder.ServedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			myOrder.CaseNumber,
			myOrder.AppointmentType,
			myOrder.OrderType,
		},
		{
			myOrder.IssuedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			myOrder.ServedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			myOrder.CaseNumber,
			myOrder.AppointmentType,
			myOrder.OrderType,
		},
	}

	f, err := os.Create("./orders.csv")
	if err != nil {
		log.Fatalln("failed to create file", err)
	}
	defer f.Close()

	w := csv.NewWriter(f)
	w.WriteAll(orders) // calls Flush internally

	if err := w.Error(); err != nil {
		log.Fatalln("error writing csv:", err)
	}
}
