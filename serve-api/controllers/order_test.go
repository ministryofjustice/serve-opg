package controllers

import (
	"bytes"
	"encoding/csv"
	"fmt"
	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"
)

// MockOrderRepository is a mock type for model.UserRepository
type MockOrderRepository struct {
	mock.Mock
}

var h = BaseHandler{
	orderRepo: mockOrderService,
}

var mockOrderService = new(MockOrderRepository)

// SelectOrderByID is mock of OrderRepository SelectOrderByID
func (m *MockOrderRepository) SelectOrderByID(id int) (*entity.Order, error) {
	args := m.Called(id)

	var order entity.Order
	if args.Get(0) != nil {
		order = args.Get(0).(entity.Order)
	}

	var e error

	if args.Get(1) != nil {
		e = args.Get(1).(error)
	}

	return &order, e
}

func (m *MockOrderRepository) GetServedOrders(dateLimit ...time.Time) ([]entity.Order, error) {
	args := m.Called()

	var orders []entity.Order
	if args.Get(0) != nil {
		orders = args.Get(0).([]entity.Order)
	}

	var e error

	if args.Get(1) != nil {
		e = args.Get(1).(error)
	}

	return orders, e
}

func (m *MockOrderRepository) TableName() string {
	return "dc_order"
}

func TestSelectOrderByID(t *testing.T) {
	order := entity.Order{
		ID:          2,
		OrderNumber: "345621789",
		Type:        "HW",
	}

	mockOrderService.On("SelectOrderByID", 4).Return(order, nil)

	r, err := http.NewRequest(http.MethodGet, "/serve-api/orders?id=4", nil)
	w := httptest.NewRecorder()

	h.GetOrder(w, r)

	orderString := fmt.Sprintf("%+v", &order)

	assert.NoError(t, err)
	assert.Equal(t, http.StatusOK, w.Code)
	assert.Equal(t, orderString, w.Body.String())
}

func TestDownloadCSVReportOfServedOrders(t *testing.T) {
	clients := []entity.Client{
		{
			ID:         3,
			CaseNumber: "445588991122",
		},
		{
			ID:         7,
			CaseNumber: "999922266366",
		},
	}

	testDate := time.Date(2022, 06, 24, 0, 0, 0, 0, time.UTC)

	orders := []entity.Order{
		{
			ID:              2,
			ClientID:        3,
			MadeAt:          testDate,
			IssuedAt:        testDate,
			ServedAt:        testDate,
			Type:            "HW",
			AppointmentType: "SOLE",
			Client:          clients[0],
		},
		{
			ID:              6,
			ClientID:        7,
			MadeAt:          testDate,
			IssuedAt:        testDate,
			ServedAt:        testDate,
			Type:            "PF",
			AppointmentType: "JOINT",
			Client:          clients[1],
		},
	}

	mockOrderService.On("GetServedOrders").Return(orders, nil)

	r, err := http.NewRequest(http.MethodGet, "/csv-report", nil)
	w := httptest.NewRecorder()

	h.DownloadReport(w, r)

	expectedCsvRecords := [][]string{
		{"DateIssued", "DateMade", "DateServed", "CaseNumber", "AppointmentType", "OrderType"},
		{"24-Jun-2022", "24-Jun-2022", "24-Jun-2022", "445588991122", "SOLE", "HW"},
		{"24-Jun-2022", "24-Jun-2022", "24-Jun-2022", "999922266366", "JOINT", "PF"},
	}

	b := &bytes.Buffer{}
	csvW := csv.NewWriter(b)
	csvW.WriteAll(expectedCsvRecords)

	fileName := fmt.Sprintf("orders-served-%s.csv", time.Now().Format("2006-01-02"))

	expectedCsvString := "DateIssued,DateMade,DateServed,CaseNumber,AppointmentType,OrderType\n" +
		"24-Jun-2022,24-Jun-2022,24-Jun-2022,445588991122,SOLE,HW\n" +
		"24-Jun-2022,24-Jun-2022,24-Jun-2022,999922266366,JOINT,PF\n"

	assert.NoError(t, err)
	assert.Equal(t, "text/csv", w.Header().Get("Content-Type"))
	assert.Equal(t, fmt.Sprintf("attachment; filename=%s", fileName), w.Header().Get("Content-Disposition"))
	assert.Equal(t, expectedCsvString, w.Body.String())

}

func TestDownloadCSVReportError(t *testing.T) {
	mockOrderService.On("GetServedOrders").
		Return(nil, fmt.Errorf("an error occured generating CSV"))

	r, _ := http.NewRequest(http.MethodGet, "/csv-report", nil)
	w := httptest.NewRecorder()
	h.DownloadReport(w, r)

	assert.Equal(t, http.StatusInternalServerError, w.Code)
	mockOrderService.AssertExpectations(t)
}
