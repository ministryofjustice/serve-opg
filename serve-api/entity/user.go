package entity

import (
	"time"

	"github.com/lib/pq"
	"gorm.io/gorm"
)

type User struct {
	gorm.Model
	Id                       uint32 `gorm:"not null;"`
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

func CreateUser(db *gorm.DB, email string, password string, roles []string, firstName string, lastName string, phoneNumber string) {
	db.Create(&User{Email: email, Password: password, Roles: roles})
}

func SelectUserById(db *gorm.DB, id int) *gorm.DB {
	var user User
	return db.First(&user, id)
}

func (user *User) TableName() string {
	return "dc_user"
}
