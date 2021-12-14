package db

import (
	"log"
	"os"
	"testing"

	"github.com/ministryofjustice/serve-opg/serve-api/entity"
)

func TestMigrate(t *testing.T) {
	os.Setenv("POSTGRES_API_DB_USER", "serve-opg-api")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

	entityTests := []struct {
		e           entity.Entity
		preMigrate  int
		postMigrate int
	}{
		{&entity.Client{}, 4, 6},
		{&entity.User{}, 11, 13},
	}

	database := Connect()

	for _, tt := range entityTests {
		table := tt.e.TableName()

		originalRows, err := database.Table(table).Rows()

		if err != nil {
			log.Fatal()
		}

		originalColumns, err := originalRows.Columns()

		if err != nil {
			log.Fatal()
		}

		got := len(originalColumns)

		if got != tt.preMigrate {
			t.Errorf("got %d want %d", got, tt.preMigrate)
		}

		Migrate(database, tt.e)

		migratedRows, err := database.Table(table).Rows()

		if err != nil {
			log.Fatal()
		}

		migratedColumns, err := migratedRows.Columns()

		if err != nil {
			log.Fatal()
		}

		got = len(migratedColumns)

		if got != tt.postMigrate {
			t.Errorf("got %d want %d", got, tt.postMigrate)
		}
	}

}
