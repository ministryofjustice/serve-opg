package handlers

import (
	"fmt"

	"gorm.io/gorm"
)

func DbStatusCheck(db *gorm.DB) (bool, string) {
	database, err := db.DB()
	if err != nil {
		return false, "Internal Error"
	}

	if err := database.Ping(); err != nil {
		return false, fmt.Sprintf("Database Connection Error: %s", err)
	}

	return true, ""
}

type HealthResponse struct {
	Healthy bool   `json:"healthy"`
	Errors  string `json:"errors"`
}
