package db

import (
	"fmt"
	"log"
	"os"

	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

func Connect() []string {
	db_user := os.Getenv("POSTGRES_API_DB_USER")
	db_pswd := os.Getenv("POSTGRES_PASSWORD")
	db_name := os.Getenv("POSTGRES_DB")

	dsn := fmt.Sprintf("host=localhost user=%s password=%s dbname=%s port=5432 sslmode=disable", db_user, db_pswd, db_name)
	db, err := gorm.Open(postgres.Open(dsn), &gorm.Config{})

	if err != nil {
		log.Fatal()
	}

	rows, err := db.Raw("SELECT * FROM dc_user").Rows()

	if err != nil {
		log.Fatal()
	}

	columns, err := rows.Columns()

	if err != nil {
		log.Fatal()
	}

	return columns
}
