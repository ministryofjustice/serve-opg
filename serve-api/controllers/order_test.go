package controllers

import (
	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"github.com/ministryofjustice/serve-opg/serve-api/internal/db"
	"github.com/ministryofjustice/serve-opg/serve-api/repositories"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"
)

type mockOrderController struct {
	mock.Mock
}

func (m *mockOrderController) CreateNewCSV() error {
	return m.Called().Error(0)
}

func TestCreateCSVReportOfServedOrders(t *testing.T) {
	//tmpdir := t.TempDir()
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

	orders := []entity.Order{
		{
			ID:          2,
			ClientID:    3,
			MadeAt:      time.Now(),
			CreatedAt:   time.Now(),
			ServedAt:    time.Now(),
			Type:        "HW",
			OrderNumber: "345621789",
			Client:      clients[0],
		},
		{
			ID:          6,
			ClientID:    7,
			MadeAt:      time.Now(),
			CreatedAt:   time.Now(),
			ServedAt:    time.Now(),
			Type:        "PF",
			OrderNumber: "987116234",
			Client:      clients[1],
		},
	}

	orderController := &mockOrderController{}
	orderController.
		On("GetServedOrders").
		Return(orders, nil)

	r, _ := http.NewRequest(http.MethodGet, "/serve-api/csv-report", nil)
	w := httptest.NewRecorder()

	database := db.Connect()

	h := NewBaseHandler(
		repositories.NewOrderRepo(database),
	)
	h.CreateNewCSV(w, r)
	resp := w.Result()

	assert.Equal(t, http.StatusOK, resp.StatusCode)

}
