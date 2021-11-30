package db

import (
	"os"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestConnection(t *testing.T) {
	os.Setenv("POSTGRES_API_DB_USER", "serve-opg-api")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

	columns := Connect()

	for key, value := range columns {
		t.Log(key, value)
		assert.IsType(t, "string", value)
		assert.IsType(t, 0, key)
	}
}
