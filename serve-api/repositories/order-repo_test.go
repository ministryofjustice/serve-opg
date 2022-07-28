package repositories

import (
	"fmt"
	"github.com/DATA-DOG/go-sqlmock"
	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"github.com/stretchr/testify/assert"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
	"testing"
	"time"
)

func TestShouldGetOrderByID(t *testing.T) {
	testDB, mock, err := sqlmock.New(sqlmock.QueryMatcherOption(sqlmock.QueryMatcherEqual))
	defer testDB.Close()

	postgresDB := postgres.New(postgres.Config{
		DSN:                  "sqlmock_db_0",
		DriverName:           "postgres",
		Conn:                 testDB,
		PreferSimpleProtocol: true,
	})
	gormDB, err := gorm.Open(postgresDB, &gorm.Config{})

	if err != nil {
		panic("Cannot open mock database")
	}

	orders := []*entity.Order{
		{
			ID:          2,
			ClientID:    3,
			MadeAt:      time.Now(),
			CreatedAt:   time.Now(),
			Type:        "HW",
			OrderNumber: "345621789",
		},
	}

	mock.ExpectQuery("SELECT * FROM \"dc_order\" WHERE \"dc_order\".\"id\" = $1 AND \"dc_order\".\"deleted_at\" IS NULL ORDER BY \"dc_order\".\"id\" LIMIT 1").
		WithArgs(1).
		WillReturnRows(
			sqlmock.NewRows([]string{
				"id",
				"client_id",
				"created_at",
				"made_at",
				"type",
				"order_number",
			}).AddRow(
				orders[0].ID,
				orders[0].ClientID,
				orders[0].CreatedAt,
				orders[0].MadeAt,
				orders[0].Type,
				orders[0].OrderNumber,
			))

	repo := NewOrderRepo(gormDB)
	result, err := repo.SelectOrderByID(1)
	if err != nil {
		t.Errorf("SelectOrderByID error: %s", err)
	}

	assert.Equal(t, orders[0], result)
	if err := mock.ExpectationsWereMet(); err != nil {
		t.Errorf("there were unfulfilled expectations: %s", err)
	}
}

func TestShouldGetServedOrders(t *testing.T) {
	testDB, mock, err := sqlmock.New(sqlmock.QueryMatcherOption(sqlmock.QueryMatcherEqual))
	defer testDB.Close()

	postgresDB := postgres.New(postgres.Config{
		DSN:                  "sqlmock_db_0",
		DriverName:           "postgres",
		Conn:                 testDB,
		PreferSimpleProtocol: true,
	})
	gormDB, err := gorm.Open(postgresDB, &gorm.Config{})

	if err != nil {
		panic("Cannot open mock database")
	}

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

	minus4Weeks := time.Now().AddDate(0, 0, -28).Truncate(24 * time.Hour)
	over4Weeks := time.Now().AddDate(0, 0, -28).Truncate(24 * time.Hour)
	within4Weeks := time.Now().AddDate(0, 0, -27).Truncate(24 * time.Hour)

	orders := []entity.Order{
		{
			ID:          2,
			ClientID:    3,
			MadeAt:      over4Weeks,
			CreatedAt:   over4Weeks,
			ServedAt:    over4Weeks,
			Type:        "HW",
			OrderNumber: "345621789",
			Client:      clients[0],
		},
		{
			ID:          6,
			ClientID:    7,
			MadeAt:      over4Weeks,
			CreatedAt:   over4Weeks,
			ServedAt:    within4Weeks,
			Type:        "PF",
			OrderNumber: "987116234",
			Client:      clients[1],
		},
	}

	t.Run("Success get all", func(t *testing.T) {
		mock.ExpectQuery("SELECT * FROM \"dc_order\" WHERE served_at IS NOT NULL AND \"dc_order\".\"deleted_at\" IS NULL").
			WillReturnRows(sqlmock.NewRows([]string{
				"id",
				"client_id",
				"created_at",
				"made_at",
				"served_at",
				"type",
				"order_number",
			}).AddRow(
				orders[0].ID,
				orders[0].ClientID,
				orders[0].CreatedAt,
				orders[0].MadeAt,
				orders[0].ServedAt,
				orders[0].Type,
				orders[0].OrderNumber,
			).AddRow(
				orders[1].ID,
				orders[1].ClientID,
				orders[1].CreatedAt,
				orders[1].MadeAt,
				orders[1].ServedAt,
				orders[1].Type,
				orders[1].OrderNumber))

		mock.ExpectQuery("SELECT * FROM \"client\" WHERE \"client\".\"id\" IN ($1,$2) AND \"client\".\"deleted_at\" IS NULL").
			WithArgs(clients[0].ID, clients[1].ID).
			WillReturnRows(sqlmock.NewRows([]string{
				"id",
				"case_number",
			}).AddRow(
				clients[0].ID,
				clients[0].CaseNumber,
			).AddRow(
				clients[1].ID,
				clients[1].CaseNumber,
			))

		repo := NewOrderRepo(gormDB)
		result, err := repo.GetServedOrders()
		if err != nil {
			t.Errorf("GetServedOrders error: %s", err)
		}

		fmt.Println(result[0])

		assert.Equal(t, orders, result)

		if err := mock.ExpectationsWereMet(); err != nil {
			t.Errorf("there were unfulfilled expectations: %s", err)
		}
	})

	t.Run("Success get within 4 weeks", func(t *testing.T) {
		mock.ExpectQuery("SELECT * FROM \"dc_order\" WHERE (served_at >= ($1) AND served_at IS NOT NULL) AND \"dc_order\".\"deleted_at\" IS NULL").
			WithArgs(minus4Weeks).
			WillReturnRows(sqlmock.NewRows([]string{
				"id",
				"client_id",
				"created_at",
				"made_at",
				"served_at",
				"type",
				"order_number",
			}).AddRow(
				orders[1].ID,
				orders[1].ClientID,
				orders[1].CreatedAt,
				orders[1].MadeAt,
				orders[1].ServedAt,
				orders[1].Type,
				orders[1].OrderNumber,
			),
			)

		mock.ExpectQuery("SELECT * FROM \"client\" WHERE \"client\".\"id\" = $1 AND \"client\".\"deleted_at\" IS NULL").
			WithArgs(clients[1].ID).
			WillReturnRows(sqlmock.NewRows([]string{
				"id",
				"case_number",
			}).AddRow(
				clients[1].ID,
				clients[1].CaseNumber,
			))

		repo := NewOrderRepo(gormDB)
		result, err := repo.GetServedOrders(minus4Weeks)
		if err != nil {
			t.Errorf("GetServedOrders error: %s", err)
		}

		assert.Equal(t, []entity.Order{orders[1]}, result)

		if err := mock.ExpectationsWereMet(); err != nil {
			t.Errorf("there were unfulfilled expectations: %s", err)
		}
	})

}
