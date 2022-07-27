package controllers

import (
	"encoding/csv"
	"fmt"
	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"log"
	"net/http"
	"os"
	"time"
)

// BaseHandler will hold everything that controller needs
type BaseHandler struct {
	orderRepo entity.OrderRepository
}

// NewBaseHandler returns a new BaseHandler
func NewBaseHandler(
	orderRepo entity.OrderRepository,
) *BaseHandler {
	return &BaseHandler{
		orderRepo: orderRepo,
	}
}

// CreateNewCSV will create a new CSV file
func (h *BaseHandler) CreateNewCSV(w http.ResponseWriter, r *http.Request) {
	minus4Weeks := time.Now().AddDate(0, 0, -28).Truncate(24 * time.Hour)
	orders, err := h.orderRepo.GetServedOrders(minus4Weeks)
	if err != nil {
		panic(err)
	}

	ordersCSV := [][]string{
		{"DateIssued", "DateMade", "DateServed", "CaseNumber", "AppointmentType", "OrderType"},
	}

	for _, order := range orders {
		ordersCSV = append(ordersCSV, []string{
			order.IssuedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			order.MadeAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			order.ServedAt.Format("Mon Jan 2 15:04:05 -0700 MST 2006"),
			order.Client.CaseNumber,
			order.AppointmentType,
			order.Type,
		})
	}

	f, err := os.Create(fmt.Sprintf("./orders-served-%s.csv", time.Now().Format("2006-01-02")))
	if err != nil {
		log.Fatalln("failed to create file", err)
	}
	defer f.Close()

	csvW := csv.NewWriter(f)
	csvW.WriteAll(ordersCSV) // calls Flush internally

	if err := csvW.Error(); err != nil {
		log.Fatalln("error writing csv:", err)
	}
}
