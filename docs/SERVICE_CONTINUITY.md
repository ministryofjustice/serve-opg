# Service Continuity for Serve OPG

The Serve OPG Online product is a web-based service created to allow the Court of Protection (Part of HMCTS) to send Court Orders around the Supervision of Deputies to the OPG. This process is called "serving an order", hence Serve OPG.


## Service Accountability (Accountable / Responsible)

The service is developed and operated by OPG Digital. A product team within OPG Digital owns the delivery and maintenance of the service. The multi-disciplinary team is responsible for the service. We follow an agile software development lifecycle as mandated by the GDS Service Standard. The team currently looks after Complete the Deputy Report and Serve OPG as they have similar codebases.

OPG Digital uses an embedded WebOps, which means infrastructure engineers are embedded within the team to support the product’s pipelines and infrastructure, as well as developers who maintain the application code

OPG Digital follows a continuous delivery model, where we constantly deploy small chunks of change as soon as they are merged into the codebase. Comprehensive test automation ensures this is possible.


### Contacts
- [Service contact details](https://docs.opg.service.justice.gov.uk/documentation/support/serve_opg.html)

## Service Scope

Serve OPG exists for the small number of users in the Court of Protection (~8-10 at last count) to serve OPG with court orders for new Deputies appointed to look after the affairs of Clients with reduced mental capacity.

The service operates at [https://serve.opg.service.justice.gov.uk](https://serve.opg.service.justice.gov.uk)

There are a limited number of deputies and if the service is down, alternatives methods of serving orders are possible via email.

The products application and infrastructure source code is available as open source via GitHub [https://github.com/ministryofjustice/serve-opg](https://github.com/ministryofjustice/serve-opg).

## Interfaces and Dependencies (Internal and External)

Serve OPG is dependent on the Sirius Case Management system, which it talks to via an API connection.

### External Dependencies

| Dependency | Purpose |
| ---------- | ------- |
| Amazon Web Services (AWS) | Cloud hosting platform |
| GitHub | Source control and build system |
| Court of Protection | Data ingestion via CSV upload |

### Software

- [Code dependency list (SBOM)](https://github.com/ministryofjustice/serve-opg/network/dependencies)
- Application code built with `PHP >= 8.4`
- Docker containers based on `Alpine Linux >= 3.21`.
    - [App](https://github.com/ministryofjustice/serve-opg/tree/main/serve-web/docker/app)
    - [Web](https://github.com/ministryofjustice/serve-opg/tree/main/serve-web/docker/web)

## Architecture (HLD/LLD)

**Description to be added**

Diagram:

<img src="">


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

Serve OPG is hosted in AWS. It has `development`, `pre-production` and `production` environments, each ring-fenced in its own AWS account to reduce blast radius of any incidents. Only `production` contains real user data. Deployment is via automated promotion of releases through the `development` > `pre-production` > `production` pipeline. New work is tested on isolated ephemeral environments in development before merge to main.

The application runs on [Elastic Container Service (ECS)](https://aws.amazon.com/ecs/). It has minimal scaling as the service has a limited number of users. The application is only available to users originating from the MOJ/HMCTS networks. The app works across 3 availability zones for resilience.

All application data is hosted in highly available services provided by AWS. The main data store is an AWS Aurora  serverless V2 Postgres cluster. With documents stored in S3.

[AWS Web Application Firewall](https://aws.amazon.com/waf/) is configured on the service to block known PHP issues, known bad inputs and common attacks(ie. CSRF, XSS and SQL injection attempts).

The service uses AWS application load balancers that only accept HTTPS/TLS connections, with their standard DDOS prevention.

All infrastructure is managed and provisioned by Terraform Infrastructure as Code (IAC) for reproducibility, environments differ only in service scaling and data content.


## Disaster Recovery Plans (Procedures / Runbooks)

Serve OPG mainly acts as a staging post for data into Sirius. As such it can be down for a number of days before it is majorly impactful to the OPG.

### Recovery Time and Recovery Point Objectives

Currently TBC.

## Backup & Restore Plans (Configuration and Testing)

Serve OPG has DB has rolling point in time backups and which are kept for 14 days.

Backups are also transferred out of account in case of account compromise.

It is worth noting that Serve OPG’s nature as a staging point means that once an order is Served the data is then in Sirius’ database and document store. This means that in a worst case scenario the service can be rebuilt and reseeded from the court of protection CSV upload.

**Restore process was last tested fully on TBC.**


## Supporting Information (Risk & Test Tracker Links)

Related service information can be seen on our [technical docs site](https://docs.opg.service.justice.gov.uk/documentation/support/serve_opg.html).

## Releases

Releases are handled via GitHub Actions and use semantic versioning. [All releases and note are available within GitHub](https://github.com/ministryofjustice/serve-opg/releases).
