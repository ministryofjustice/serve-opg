package entity

import (
	"time"

	"gorm.io/gorm"
)

// Client, Deputies, Documents need including

type Order struct {
	gorm.Model
	Id                      string `gorm:"migration"`
	SubType                 string `gorm:"size:50"`
	HasAssetsAboveThreshold string `gorm:"size:50"`
	AppointmentType         string `gorm:"size:50"`
	CreatedAt               time.Time
	MadeAt                  time.Time
	IssuedAt                time.Time
	ServedAt                time.Time
	PayloadServed           string
	ApiResponse             string
	OrderNumber             string `gorm:"unique"`
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

func SelectOrderById(db *gorm.DB, id int) *gorm.DB {
	var order Order
	return db.First(&order, id)
}

func (order *Order) TableName() string {
	return "dc_order"
}
