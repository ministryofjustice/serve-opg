package entity

import (
	"gorm.io/gorm"
)

type Document struct {
	gorm.Model
	Id                     uint32 `gorm:"not null;"`
	OrderID                uint32
	Type                   string `gorm:"size:100;not null;"`
	FileName               string `gorm:"size:255"`
	StorageReference       string `gorm:"size:255;not null;"`
	RemoteStorageReference string `gorm:"size:255"`
}

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

func SelectDocumentById(db *gorm.DB, id int) *gorm.DB {
	var document Document
	return db.First(&document, id)
}

func (document *Document) TableName() string {
	return "document"
}
