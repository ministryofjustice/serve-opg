package entity

import (
	"time"

	"gorm.io/gorm"
)

type Deputy struct {
	gorm.Model
	Id                   uint32  `gorm:"not null;"`
	DeputyType           string  `gorm:"size:255;not null;"`
	Orders               []Order `gorm:"many2many:ordertype_deputy;"`
	Forename             string  `gorm:"size:255;not null;"`
	Surname              string  `gorm:"size:255;not null;"`
	DateOfBirth          time.Time
	EmailAddress         string `gorm:"size:255;"`
	DaytimeContactNumber string `gorm:"size:255;"`
	EveningContactNumber string `gorm:"size:255;"`
	MobileContactNumber  string `gorm:"size:255;"`
	AddressLine1         string `gorm:"size:255;"`
	AddressLine2         string `gorm:"size:255;"`
	AddressLine3         string `gorm:"size:255;"`
	AddressTown          string `gorm:"size:255;"`
	AddressCounty        string `gorm:"size:255;"`
	AddressPostcode      string `gorm:"size:255;"`
}

func CreateDeputy(
	db *gorm.DB,
	deputyType string,
	forename string,
	surname string,
	dateOfBirth time.Time,
	emailAddress string,
	daytimeContactNumber string,
	eveningContactNumber string,
	mobileContactNumber string,
	addressLine1 string,
	addressLine2 string,
	addressLine3 string,
	addressTown string,
	addressCounty string,
	addressPostcode string,
) {
	db.Create(&Deputy{
		DeputyType:           deputyType,
		Forename:             forename,
		Surname:              surname,
		DateOfBirth:          dateOfBirth,
		EmailAddress:         emailAddress,
		DaytimeContactNumber: daytimeContactNumber,
		EveningContactNumber: eveningContactNumber,
		MobileContactNumber:  mobileContactNumber,
		AddressLine1:         addressLine1,
		AddressLine2:         addressLine2,
		AddressLine3:         addressLine3,
		AddressTown:          addressTown,
		AddressCounty:        addressCounty,
		AddressPostcode:      addressPostcode,
	})
}

func SelectDeputyById(db *gorm.DB, id int) *gorm.DB {
	var deputy Deputy
	return db.First(&deputy, id)
}

func (deputy *Deputy) TableName() string {
	return "deputy"
}
