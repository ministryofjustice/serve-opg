package entity

import (
	"time"

	"github.com/lib/pq"
	"gorm.io/gorm"
)

// User defines the information a user holds
type User struct {
	gorm.Model
	ID                       uint32 `gorm:"not null;type:bigint;autoIncrement"`
	Email                    string `gorm:"size:255;not null;unique;"`
	Password                 string `gorm:"size:255;not null;"`
	ActivationTokenCreatedAt time.Time
	ActivationToken          string `gorm:"size:40;"`
	LastLoginAt              time.Time
	Roles                    pq.StringArray `gorm:"type:text[];not null;"`
	FirstName                string         `gorm:"size:100;"`
	LastName                 string         `gorm:"size:100;"`
	PhoneNumber              string         `gorm:"size:20;"`
}

// CreateUser will create a user in the database with the passed in values
func CreateUser(db *gorm.DB, email string, password string, roles []string, firstName string, lastName string, phoneNumber string) {
	db.Create(&User{Email: email, Password: password, Roles: roles})
}

// SelectUserByID will select a user by their ID
func (u *User) SelectUserByID(db *gorm.DB, id int) *gorm.DB {
	return db.First(u, id)
}

// TableName refers to the table name used in the database
func (u *User) TableName() string {
	return "dc_user"
}
