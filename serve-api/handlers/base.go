package handlers

import "github.com/ministryofjustice/serve-opg/serve-api/entity"

type BaseHandler struct {
	orderRepo entity.OrderRepository
}

func NewBaseHandler(
	orderRepo entity.OrderRepository,
) *BaseHandler {
	return &BaseHandler{
		orderRepo: orderRepo,
	}
}
