package db

import (
	"fmt"
	"log"
	"os"

	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

// Connect will create a connection to the database
// and return the gorm.DB connection
func Connect() *gorm.DB {
	db_host := os.Getenv("POSTGRES_HOST")
	db_user := os.Getenv("POSTGRES_USER")
	db_pswd := os.Getenv("POSTGRES_PASSWORD")
	db_name := os.Getenv("POSTGRES_DB")

	dsn := fmt.Sprintf("host=%s user=%s password=%s dbname=%s port=5432 sslmode=disable", db_host, db_user, db_pswd, db_name)
	db, err := gorm.Open(postgres.Open(dsn), &gorm.Config{})

	if err != nil {
		log.Fatal()
	}

	return db
}

// Migrate will create the tables in the database
func Migrate(db *gorm.DB, entity entity.Entity) {
	db.AutoMigrate(&entity)
}
