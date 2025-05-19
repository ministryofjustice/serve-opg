## Serving Court Orders

This works through the following route.

Serve -> Sirius API (via the old-login)

Serve -> Serve Event Bus OK -> Sirius Event Bus DOWN -> Passthrough lambda -> Sirius API

Changes

Serve - New endpoint that sends to event bus but with same logic as before. Copy pasta + send to event bridge

Serve - Add event bus serve side and whatever else it needs.

Sirius - Add the config to listen for it

Sirius - New API endpoint to listen for new request from passthrough.
