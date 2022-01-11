package entity

import (
	"time"

	"github.com/lib/pq"
	"gorm.io/gorm"
)

type User struct {
	gorm.Model
	Id                       string `gorm:"migration"`
	Email                    string `gorm:"size:255;not null;unique"`
	Password                 string `gorm:"size:255;not null"`
	ActivationToken          string `gorm:"size:40;not null"`
	ActivationTokenCreatedAt time.Time
	LastLoginAt              time.Time
	Roles                    pq.StringArray `gorm:"type:text[]"`
	FirstName                string         `gorm:"size:100;not null"`
	LastName                 string         `gorm:"size:100;not null"`
	PhoneNumber              string         `gorm:"size:20;not null"`
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
