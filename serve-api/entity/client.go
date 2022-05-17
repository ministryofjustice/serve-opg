package entity

import (
	"gorm.io/gorm"
)

// Client defines the information a client holds
type Client struct {
	gorm.Model
	ID         uint32 `gorm:"not null;type:bigint;autoIncrement"`
	Orders     []Order
	CaseNumber string `gorm:"size:8;not null;unique"`
	ClientName string `gorm:"size:255;not null"`
}

// CreateClient will create a client in the database with the passed in values
func CreateClient(db *gorm.DB, case_number string, client_name string) {
	db.Create(&Client{CaseNumber: case_number, ClientName: client_name})
}

// SelectClientByID will select a client by their ID
func (c *Client) SelectClientByID(db *gorm.DB, id int) *gorm.DB {
	return db.First(c, id)
}

// TableName refers to the table name used in the database
func (client *Client) TableName() string {
	return "client"
}
