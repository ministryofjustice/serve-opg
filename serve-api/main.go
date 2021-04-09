package main

import (
	"context"
	"log"
	"net/http"
	"os"
	"os/signal"
	"time"

	"github.com/gorilla/mux"
)

func main() {
	//logger
	l := log.New(os.Stdout, "serve-api ", log.LstdFlags)

	//creating the serve mux
	sm := mux.NewRouter().PathPrefix("/serve-api").Subrouter()

	sm.HandleFunc("/health-check", func(w http.ResponseWriter, r *http.Request) {
		log.Println("Running the health check handler")
		w.WriteHeader(http.StatusOK)
	})

	//setting up the http server
	s := &http.Server{
		Addr:         ":9000",
		Handler:      sm,
		ErrorLog:     l,
		IdleTimeout:  120 * time.Second,
		ReadTimeout:  1 * time.Second,
		WriteTimeout: 15 * time.Minute,
	}

	// start the server
	go func() {
		err := s.ListenAndServe()
		if err != nil {
			l.Fatal(err)
		}
	}()

	//catching signal to gracefully shutdown
	c := make(chan os.Signal)
	signal.Notify(c, os.Interrupt, os.Kill)
	sig := <-c
	l.Println("Recieve terminate, gracefully shutting down", sig)
	tc, _ := context.WithTimeout(context.Background(), 30*time.Second)
	s.Shutdown(tc)
}
