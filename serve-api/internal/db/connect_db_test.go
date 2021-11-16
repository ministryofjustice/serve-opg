package db

import (
	"os"
	"testing"
)

func TestConnection(t *testing.T) {
	os.Setenv("POSTGRES_API_DB_USER", "serve-opg-api")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

	result := Connect()
	t.Log(result)
}
