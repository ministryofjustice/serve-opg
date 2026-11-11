FROM python:3.14.10-alpine3.24@sha256:eb2c62658b8d51bff0acc408504b76521d02030de56a8a3c1e830083e89b89d2

# Copy source files
COPY check_actions.sh /check_actions.sh

COPY --from=ghcr.io/astral-sh/uv@sha256:32a7bb39b496babbd997b0a39a4d66c126bdaf5661b302bf6f06abda8deca6ff /uv /uvx /bin/
COPY uv.lock .
COPY pyproject.toml .
RUN uv export --locked -o requirements.txt \
    && python -m pip install --no-cache-dir -r requirements.txt

RUN apk add actionlint
RUN chmod +x /check_actions.sh

CMD ["/check_actions.sh"]
