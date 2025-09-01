FROM localstack/localstack:4.6 as localstack
# Can't upgrade to 4.7 because of dynamodb changes that require types being set
# This is handled by dependencies that need upgrading

COPY ./local-setup/localstack/localstack-init.sh /etc/localstack/init/ready.d/init-aws.sh
COPY ./local-setup/localstack/healthcheck.sh /tmp/healthcheck.sh
COPY ./local-setup/localstack/attempts_table.json /tmp/attempts_table.json
COPY ./local-setup/localstack/sessions_table.json /tmp/sessions_table.json
RUN chmod 544 /etc/localstack/init/ready.d/init-aws.sh && chmod 544 /tmp/healthcheck.sh
RUN apt-get -y install jq
