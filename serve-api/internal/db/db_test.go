package db

import (
	"log"
	"os"
	"testing"

	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"gorm.io/gorm"
)

// var database *gorm.DB

func TestMain(m *testing.M) {
	os.Setenv("POSTGRES_API_DB_USER", "serve-opg-api")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

}

func TestMigrate(t *testing.T) {

	entityTests := []struct {
		e           entity.Entity
		preMigrate  int
		postMigrate int
	}{
		{&entity.Client{}, 4, 6},
		{&entity.User{}, 11, 18},
	}

	database := Connect()

	for _, tt := range entityTests {
		table := tt.e.TableName()

		originalColumns := getDBColumns(database, table)

		got := len(originalColumns)

		if got != tt.preMigrate {
			t.Errorf("got %d want %d", got, tt.preMigrate)
		}

		Migrate(database, tt.e)

		migratedColumns := getDBColumns(database, table)

		got = len(migratedColumns)

		if got != tt.postMigrate {
			t.Errorf("got %d want %d", got, tt.postMigrate)
		}
	}
}

func getDBColumns(database *gorm.DB, table string) []string {
	rows, err := database.Table(table).Rows()

	if err != nil {
		log.Fatal()
	}

	columns, err := rows.Columns()

	if err != nil {
		log.Fatal()
	}

	return columns
}
