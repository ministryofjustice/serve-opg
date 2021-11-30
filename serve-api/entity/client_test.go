package entity

import (
	"log"
	"os"
	"testing"

	"github.com/ministryofjustice/serve-opg/serve-api/internal/db"
	"github.com/stretchr/testify/assert"
)

func TestMigrate(t *testing.T) {
	os.Setenv("POSTGRES_API_DB_USER", "serve-opg-api")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

	db := db.Connect()

	originalRows, err := db.Raw("SELECT * FROM client").Rows()

	if err != nil {
		log.Fatal()
	}

	originalColumns, err := originalRows.Columns()

	if err != nil {
		log.Fatal()
	}

	assert.Equal(t, 4, len(originalColumns))
	assert.NotContains(t, originalColumns, "updated_at")
	assert.NotContains(t, originalColumns, "deleted_at")

	Migrate(db)

	migratedRows, err := db.Raw("SELECT * FROM client").Rows()

	if err != nil {
		log.Fatal()
	}

	migratedColumns, err := migratedRows.Columns()

	if err != nil {
		log.Fatal()
	}

	assert.Equal(t, 6, len(migratedColumns))
	assert.Contains(t, migratedColumns, "updated_at")
	assert.Contains(t, migratedColumns, "deleted_at")
}
