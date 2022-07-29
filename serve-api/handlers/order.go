package handlers

import (
	"bytes"
	"encoding/csv"
	"fmt"
	"log"
	"net/http"
	"strconv"
	"time"
)

func (h *BaseHandler) GetOrder(w http.ResponseWriter, r *http.Request) {
	param := r.URL.Query().Get("id")
	id, err := strconv.Atoi(param)
	if err != nil {
		panic(err)
	}

	order, err := h.orderRepo.SelectOrderByID(id)
	if err != nil {
		panic(err)
	}

	w.Write([]byte(fmt.Sprintf("%+v", order)))
}

// DownloadReport will create and download a CSV file of served orders within 4 weeks of being served
func (h *BaseHandler) DownloadReport(w http.ResponseWriter, r *http.Request) {
	minus4Weeks := time.Now().AddDate(0, 0, -28).Truncate(24 * time.Hour)
	orders, err := h.orderRepo.GetServedOrders(minus4Weeks)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte(err.Error()))
		return
	}

	ordersCSV := [][]string{
		{"DateIssued", "DateMade", "DateServed", "CaseNumber", "AppointmentType", "OrderType"},
	}

	for _, order := range orders {
		ordersCSV = append(ordersCSV, []string{
			order.IssuedAt.Format("02-Jan-2006"),
			order.MadeAt.Format("02-Jan-2006"),
			order.ServedAt.Format("02-Jan-2006"),
			order.Client.CaseNumber,
			order.AppointmentType,
			order.Type,
		})
	}

	fileName := fmt.Sprintf("orders-served-%s.csv", time.Now().Format("2006-01-02"))

	a := fmt.Sprintf("attachment; filename=%s", fileName)
	w.Header().Add("Content-Disposition", a)
	w.Header().Set("Content-Type", "text/csv")

	b := &bytes.Buffer{}
	csvW := csv.NewWriter(b)
	csvW.WriteAll(ordersCSV) // calls Flush internally

	if err := csvW.Error(); err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		log.Fatalln("Error writing orders csv:", err)
	} else {
		w.Write(b.Bytes())
	}
}
