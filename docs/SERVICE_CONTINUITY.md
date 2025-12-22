# Service Continuity for Serve OPG



## Service Accountability (Accountable / Responsible)



### Contacts
- [Service contact details](https://docs.opg.service.justice.gov.uk/documentation/support/serve_opg.html)

### Notes

## Service Scope


## Interfaces and Dependencies (Internal and External)



### External Dependencies

| Dependency | Purpose |
| ---------- | ------- |
| Amazon Web Services (AWS) | Cloud hosting platform |
| GitHub | Source control and build system |


### Software

- [Code dependency list (SBOM)](https://github.com/ministryofjustice/serve-opg/network/dependencies)


## Architecture (HLD/LLD)


## Incident Response Plans (Call Tree, Including Roles and Responsibilities)

OPG Digital has a standard incident response process across all its teams; detailed in our [technical guidance](https://docs.opg.service.justice.gov.uk/documentation/incidents/process.html)

Incidents are handled by a product's team following the "you build it you run it" approach common in Agile delivery teams. Teams are expected to pause any feature work and help the incident team swarm on solutions to live issues.

The following roles are part of the process:

Any team member can call an incident using the incident management applications [automated Slack tooling](https://docs.opg.service.justice.gov.uk/documentation/incidents/process.html#declare-an-incident). The OPG incident tool records timelines and actions from the incident channel automatically and produces a report for reference purposes. They are given the reporter role by default.

[Incident leads](https://docs.opg.service.justice.gov.uk/documentation/incidents/process.html#incident-lead) are a rotating list of Technical Architects, Lead Webops and Senior Webops members of OPG Digital. Managed in pagerduty. They are looped in to coordinate responses, this can be done via pagerduty via the incident app or via Slack.

Developers and Webops from the team will be brought into the incident channel as the team to solve the issue.

Where communication with the wider OPG is needed, Product or Delivery managers from the product in question take up the [Communications Lead](https://docs.opg.service.justice.gov.uk/documentation/incidents/process.html#communications-lead) role and hook into wider OPG business continuity processes.

Where the incident in question is security-related wider MOJ security colleagues will be brought into the incident channel or contacted via Report a Cyber Security Incident Form.

Our incident tooling automatically logs actions from incident slack channels to compile reports - these are all accessible on a dedicated [incident website](https://incident.opg.service.justice.gov.uk/).

After an incident a Root Cause Analysis is run so that lessons learned can be picked up by the team and wider OPG Digital staff. These are stored in the [OPG Digital confluence space](https://opgtransform.atlassian.net/wiki/spaces/RCAS/overview).

**Note**: The incident website requires GitHub SSO to the MOJ organisation.


## IT Continuity Plans (Resilience)

Serve is hosted in AWS. It has `development`, `pre-production` and `production` environments, each ring-fenced in its own AWS account to reduce blast radius of any incidents.

## Disaster Recovery Plans (Procedures / Runbooks)


### Recovery Time and Recovery Point Objectives

Currently TBC.

## Backup & Restore Plans (configuration and Testing)



## Supporting Information (Risk & Test Tracker Links)


## Releases

Releases are handled via GitHub Actions and use semantic versioning. [All releases and note are available within GitHub](https://github.com/ministryofjustice/serve-opg/releases).
