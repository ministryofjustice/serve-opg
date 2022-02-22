package db

import (
	"fmt"
	"log"
	"os"

	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

func Connect() *gorm.DB {
	db_user := os.Getenv("POSTGRES_API_DB_USER")
	db_pswd := os.Getenv("POSTGRES_PASSWORD")
	db_name := os.Getenv("POSTGRES_DB")

	dsn := fmt.Sprintf("host=localhost user=%s password=%s dbname=%s port=5432 sslmode=disable", db_user, db_pswd, db_name)
	db, err := gorm.Open(postgres.Open(dsn), &gorm.Config{})

	if err != nil {
		log.Fatal()
	}

	return db
}

func Migrate(db *gorm.DB, entity entity.Entity) {
	db.AutoMigrate(&entity)
}