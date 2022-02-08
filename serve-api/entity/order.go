package entity

import (
	"time"

	"gorm.io/gorm"
)

type OrderType string

const (
	HW   OrderType = "HW"
	PF   OrderType = "PF"
	BOTH OrderType = "BOTH"
)

// Client, Deputies, Documents need including
type Order struct {
	gorm.Model
	ID                      uint32 `gorm:"not null;"`
	ClientID                uint32
	SubType                 string   `gorm:"size:50;"`
	HasAssetsAboveThreshold string   `gorm:"size:50;"`
	Deputies                []Deputy `gorm:"many2many:ordertype_deputy;"`
	Documents               []Document
	AppointmentType         string `gorm:"size:50;"`
	CreatedAt               time.Time
	MadeAt                  time.Time `gorm:"not null;"`
	IssuedAt                time.Time
	ServedAt                time.Time
	// Drop PayloadServed and ApiResponse. Manual migration to drop these from database
	PayloadServed string
	ApiResponse   string
	OrderNumber   string
	Type          OrderType `gorm:"not null;"`
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
	})
}

func (o *Order) SelectOrderByID(db *gorm.DB, id int) *gorm.DB {
	return db.First(o, id)
}

func (o *Order) GetType() string {
	return string(o.Type)
}

func (order *Order) TableName() string {
	return "dc_order"
}
