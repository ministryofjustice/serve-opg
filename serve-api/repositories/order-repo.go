package repositories

import (
	"time"

	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"github.com/pkg/errors"
	"gorm.io/gorm"
)

type OrderRepo struct {
	db *gorm.DB
}

func NewOrderRepo(db *gorm.DB) *OrderRepo {
	return &OrderRepo{
		db: db,
	}
}

// GetServedOrders will get all orders from the DB
func (r *OrderRepo) GetServedOrders(dateLimit ...time.Time) ([]entity.Order, error) {
	if len(dateLimit) > 1 {
		return nil, errors.Errorf("GetServedOrders allows only 1 date limit to be passed, received %d", len(dateLimit))
	}
	var orders []entity.Order
	if len(dateLimit) == 1 {
		if err := r.db.Table(r.TableName()).Where("served_at >= ? AND served_at IS NOT NULL", dateLimit).Preload("Client").Find(&orders).Error; err != nil {
			return nil, err
		}
	} else {
		if err := r.db.Table(r.TableName()).Where("served_at IS NOT NULL").Preload("Client").Find(&orders).Error; err != nil {
			return nil, err
		}
	}

	return orders, nil
}

// SelectOrderByID will select an order by their ID
func (r *OrderRepo) SelectOrderByID(id int) (*entity.Order, error) {
	var order entity.Order
	if err := r.db.First(&order, id).Error; err != nil {
		return nil, err
	}
	return &order, nil
}

// TableName refers to the table name used in the database
func (r *OrderRepo) TableName() string {
	return "dc_order"
}
