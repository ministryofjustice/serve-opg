package authentication

import (
	"encoding/json"
	"github.com/gorilla/mux"
	"net/http"
)

func Auth(route *mux.Router) {
	route.HandleFunc("/auth", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		p := map[string]bool{"connection": true}
		json.NewEncoder(w).Encode(p)
	})
}
