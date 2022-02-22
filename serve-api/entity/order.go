package entity

import (
	"time"

	"gorm.io/gorm"
)

const (
	OrderTypeHW   string = "HW"
	OrderTypePF   string = "PF"
	OrderTypeBOTH string = "BOTH"
)

// Client, Deputies, Documents need including
type Order struct {
	gorm.Model
	ID                      uint32 `gorm:"not null;migration"`
	ClientID                uint32
	SubType                 string `gorm:"size:50;"`
	HasAssetsAboveThreshold string `gorm:"size:50;"`
	// Need to rename order_type_id column in ordertype_deputy table to order_id. Manual migration to rename
	Deputies        []Deputy `gorm:"many2many:ordertype_deputy;"`
	Documents       []Document
	AppointmentType string `gorm:"size:50;"`
	CreatedAt       time.Time
	MadeAt          time.Time `gorm:"not null;"`
	IssuedAt        time.Time
	ServedAt        time.Time
	OrderNumber     string
	Type            string `gorm:"not null;"`
	// Drop PayloadServed and ApiResponse. Manual migration to drop these from database
	PayloadServed string
	ApiResponse   string
}

func CreateOrder(
	db *gorm.DB,
	subType string,
	hasAssetsAboveThreshold string,
	appointmentType string,
	createdAt time.Time,
	madeAt time.Time,
	issuedAt time.Time,
	servedAt time.Time,
	payloadServed string,
	apiResponse string,
	orderNumber string,
	orderType string,
) {
	db.Create(&Order{
		SubType:                 subType,
		HasAssetsAboveThreshold: hasAssetsAboveThreshold,
		AppointmentType:         appointmentType,
		CreatedAt:               createdAt,
		MadeAt:                  madeAt,
		IssuedAt:                issuedAt,
		ServedAt:                servedAt,
		PayloadServed:           payloadServed,
		ApiResponse:             apiResponse,
		OrderNumber:             orderNumber,
		Type:                    orderType,
	})
}

func (o *Order) SelectOrderByID(db *gorm.DB, id int) *gorm.DB {
	return db.First(o, id)
}

func (o *Order) GetType() string {
	return o.Type
}

func (*Order) TableName() string {
	return "dc_order"
}
