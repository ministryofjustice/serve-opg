package entity

import (
	"gorm.io/gorm"
)

type Client struct {
	gorm.Model
	CaseNumber string `gorm:"size:8;not null;unique"`
	ClientName string `gorm:"size:255;not null"`
}

func Migrate(db *gorm.DB) {
	// Migrate the schema
	db.AutoMigrate(&Client{})
}

func Create(db *gorm.DB, case_number string, client_name string) {
	db.Create(&Client{CaseNumber: case_number, ClientName: client_name})
}

func SelectById(db *gorm.DB, id int) *gorm.DB {
	var client Client
	return db.First(&client, id)
}

func (client *Client) TableName() string {
	return "client"
}
