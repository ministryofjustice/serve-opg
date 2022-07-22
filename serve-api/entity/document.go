package entity

import (
	"gorm.io/gorm"
)

// Document defines the information a document holds
type Document struct {
	gorm.Model
	ID                     uint32 `gorm:"not null;type:bigint;autoIncrement"`
	OrderID                uint32
	Type                   string `gorm:"size:100;not null;"`
	FileName               string `gorm:"size:255"`
	StorageReference       string `gorm:"size:255;not null;"`
	RemoteStorageReference string `gorm:"size:255"`
}

// CreateDocument will create a document in the database with the passed in values
func CreateDocument(
	db *gorm.DB,
	orderId uint32,
	documentType string,
	fileName string,
	storageReference string,
	remoteStorageReference string,
) {
	db.Create(&Document{
		OrderID:                orderId,
		Type:                   documentType,
		FileName:               fileName,
		StorageReference:       storageReference,
		RemoteStorageReference: remoteStorageReference,
	})
}

// SelectDocumentByID will select a document by their ID
func (d *Document) SelectDocumentByID(db *gorm.DB, id int) *gorm.DB {
	var document Document
	return db.First(&document, id)
}

// TableName refers to the table name used in the database
func (d *Document) TableName() string {
	return "document"
}
