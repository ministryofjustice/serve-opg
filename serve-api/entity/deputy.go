package entity

import (
	"time"

	"gorm.io/gorm"
)

type Deputy struct {
	gorm.Model
	DeputyType           string `gorm:"size:255;not null"`
	Forename             string `gorm:"size:255;not null"`
	Surname              string `gorm:"size:255;not null"`
	DateOfBirth          time.Time
	EmailAddress         string `gorm:"size:255;not null"`
	DaytimeContactNumber string `gorm:"size:255;not null"`
	EveningContactNumber string `gorm:"size:255;not null"`
	MobileContactNumber  string `gorm:"size:255;not null"`
	AddressLine1         string `gorm:"size:255;not null"`
	AddressLine2         string `gorm:"size:255;not null"`
	AddressLine3         string `gorm:"size:255;not null"`
	AddressTown          string `gorm:"size:255;not null"`
	AddressCounty        string `gorm:"size:255;not null"`
	AddressPostcode      string `gorm:"size:255;not null"`
	AddressCountry       string `gorm:"size:255;not null"`
}

func CreateDeputy(db *gorm.DB, email string, password string, roles []string, firstName string, lastName string, phoneNumber string) {
	db.Create(&User{Email: email, Password: password, Roles: roles})
}

func SelectDeputyById(db *gorm.DB, id int) *gorm.DB {
	var user User
	return db.First(&user, id)
}

func (deputy *Deputy) TableName() string {
	return "deputy"
}
