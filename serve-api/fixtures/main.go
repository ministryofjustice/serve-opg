package main

import (
	"database/sql"
	"fmt"
	"log"

	"github.com/go-testfixtures/testfixtures/v3"
)

/*  POSTGRES_HOST: localhost
    POSTGRES_DB: serve-opg
    POSTGRES_USER: serve-opg
    POSTGRES_PASSWORD: dcdb2018! */

var (
	db       *sql.DB
	fixtures *testfixtures.Loader
)

const (
	host     = "localhost"
	port     = 5432
	user     = "serve-opg"
	password = "dcdb2018!"
	dbname   = "serve-opg"
)

func main() {
	var err error

	// Open connection to the test database.
	// Do NOT import fixtures in a production database!
	// Existing data would be deleted.
	psqlconn := fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s sslmode=disable", host, port, user, password, dbname)
	db, err = sql.Open("postgres", psqlconn)
	if err != nil {
		log.Fatal(err)
	}

	fixtures, err = testfixtures.New(
		testfixtures.Database(db),        // You database connection
		testfixtures.Dialect("postgres"), // Available: "postgresql", "timescaledb", "mysql", "mariadb", "sqlite" and "sqlserver"
		testfixtures.Directory("data/"),  // The directory containing the YAML files
	)

	if err != nil {
		log.Fatal(err)
	}

	prepareTestDatabase()
}

func prepareTestDatabase() {
	if err := fixtures.Load(); err != nil {
		log.Fatal()
	}
}
