package db

import (
	"os"
	"testing"

	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"gorm.io/gorm"
)

var database *gorm.DB

func setUpTest() {
	os.Setenv("POSTGRES_API_DB_USER", "serve-opg-api")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

	database = Connect()
}

func removeDBColumns(e entity.Entity) {
	columnNames := []string{"updated_at", "deleted_at", "created_at"}

	for _, name := range columnNames {
		if database.Migrator().HasColumn(e, name) {
			database.Migrator().DropColumn(e, name)
		}
	}
}

func TestMigrate(t *testing.T) {
	setUpTest()
	entityTests := []struct {
		e               entity.Entity
		migratedColumns []string
	}{
		{&entity.Client{}, []string{"updated_at", "deleted_at"}},
		{&entity.User{}, []string{"updated_at", "deleted_at"}},
		{&entity.Deputy{}, []string{"updated_at", "deleted_at", "created_at"}},
		{&entity.Order{}, []string{"updated_at", "deleted_at"}},
		{&entity.Document{}, []string{"updated_at", "deleted_at", "created_at"}},
	}

	for _, tt := range entityTests {
		removeDBColumns(tt.e)

		for _, colName := range tt.migratedColumns {
			if database.Migrator().HasColumn(tt.e, colName) {
				t.Errorf("database column %s already exist for table %s!", colName, tt.e.TableName())
			}
		}

		Migrate(database, tt.e)

		for _, colName := range tt.migratedColumns {
			if !database.Migrator().HasColumn(tt.e, colName) {
				t.Errorf("database column %s have not been migrated for table %s!", colName, tt.e.TableName())
			}
		}
	}
}

func TestJoinTableMigration(t *testing.T) {
	setUpTest()

	Migrate(database, &entity.Order{})

	if !database.Migrator().HasTable("ordertype_deputy") {
		t.Error("Failed to migrate join table: ordertype_deputy!")
	}

	expectedColumns := []string{"deputy_id", "order_type_id"}

	for _, col := range expectedColumns {
		if !database.Migrator().HasColumn("ordertype_deputy", col) {
			t.Errorf("Failed to migrate column: %s!", col)
		}
	}

	rows, _ := database.Table("ordertype_deputy").Rows()
	cols, _ := rows.Columns()
	if !(len(cols) == 2) {
		t.Errorf("Wrong number of columns in table. Expected: 2, got %d!", len(cols))
	}
}

func TestOrderEntity(t *testing.T) {
	setUpTest()

	Migrate(database, &entity.Order{})

	order := &entity.Order{}
	order.SelectOrderById(database, 2)
	t.Log(order.GetType())
}
