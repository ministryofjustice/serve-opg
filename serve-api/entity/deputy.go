package entity

import (
	"time"

	"gorm.io/gorm"
)

// Deputy defines the information a deputy holds
type Deputy struct {
	gorm.Model
	ID                   int     `gorm:"not null;type:bigint;autoIncrement"`
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

// CreateDeputy will create a deputy in the database with the passed in values
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

// SelectDeputyByID will select a deputy by their ID
func (d *Deputy) SelectDeputyByID(db *gorm.DB, id int) *gorm.DB {
	return db.First(d, id)
}

// TableName refers to the table name used in the database
func (d *Deputy) TableName() string {
	return "deputy"
}
