package db

import (
	"fmt"
	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
	"log"
	"os"
)

// Connect will create a connection to the database and return the gorm.DB connection
func Connect() *gorm.DB {
	dbHost := os.Getenv("POSTGRES_HOST")
	dbUser := os.Getenv("POSTGRES_USER")
	dbPswd := os.Getenv("POSTGRES_PASSWORD")
	dbName := os.Getenv("POSTGRES_DB")

	dsn := fmt.Sprintf("host=%s user=%s password=%s dbname=%s port=5432 sslmode=disable options=--cluster=13/main", dbHost, dbUser, dbPswd, dbName)
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
