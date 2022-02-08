package entity

import (
	"gorm.io/gorm"
)

type Client struct {
	gorm.Model
	ID         uint32 `gorm:"not null;"`
	Orders     []Order
	CaseNumber string `gorm:"size:8;not null;unique"`
	ClientName string `gorm:"size:255;not null"`
}

func CreateClient(db *gorm.DB, case_number string, client_name string) {
	db.Create(&Client{CaseNumber: case_number, ClientName: client_name})
}

func (c *Client) SelectClientByID(db *gorm.DB, id int) *gorm.DB {
	return db.First(c, id)
}

func (client *Client) TableName() string {
	return "client"
}
