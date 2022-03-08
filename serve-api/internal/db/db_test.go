package db

import (
	"log"
	"os"
	"testing"

	"github.com/go-testfixtures/testfixtures/v3"
	"github.com/ministryofjustice/serve-opg/serve-api/entity"
	"github.com/stretchr/testify/assert"
	"gorm.io/gorm"
)

var (
	database *gorm.DB
	fixtures *testfixtures.Loader
)

func setUpTest() {
	os.Setenv("POSTGRES_HOST", "localhost")
	os.Setenv("POSTGRES_USER", "serve-opg")
	os.Setenv("POSTGRES_PASSWORD", "dcdb2018!")
	os.Setenv("POSTGRES_DB", "serve-opg")

	database = Connect()

	entites := []struct {
		entity entity.Entity
	}{
		{&entity.Client{}},
		{&entity.User{}},
		{&entity.Deputy{}},
		{&entity.Order{}},
		{&entity.Document{}},
	}

	for _, tt := range entites {
		Migrate(database, tt.entity)
	}
}

func setUpFixtures() {
	standardDB, _ := database.DB()
	fixtures, _ = testfixtures.New(
		testfixtures.Database(standardDB),  // You database connection
		testfixtures.Dialect("postgresql"), // Available: "postgresql", "timescaledb", "mysql", "mariadb", "sqlite" and "sqlserver"
		testfixtures.Directory("data"),     // The directory containing the YAML files
		testfixtures.DangerousSkipTestDatabaseCheck(),
	)

	if err := fixtures.Load(); err != nil {
		log.Fatal(err)
	}
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

	expectedColumns := []string{"deputy_id", "order_type_id", "order_id"}

	for _, col := range expectedColumns {
		if !database.Migrator().HasColumn("ordertype_deputy", col) {
			t.Errorf("Failed to migrate column: %s!", col)
		}
	}

	rows, _ := database.Table("ordertype_deputy").Rows()
	cols, _ := rows.Columns()
	if !(len(cols) == 3) {
		t.Errorf("Wrong number of columns in table. Expected: 3, got %d!", len(cols))
	}
}

func TestOrderEntity(t *testing.T) {
	setUpTest()
	setUpFixtures()

	Migrate(database, &entity.Order{})

	orderTypeTests := []struct {
		id        int
		orderType string
	}{
		{id: 1, orderType: entity.OrderTypePF},
		{id: 2, orderType: entity.OrderTypeHW},
	}

	for _, tt := range orderTypeTests {
		order := &entity.Order{}
		order.SelectOrderByID(database, tt.id)
		assert.Equal(t, tt.orderType, order.GetType())
	}
}
