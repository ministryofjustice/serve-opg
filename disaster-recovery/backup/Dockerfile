FROM python:3.14.9-alpine3.24@sha256:dbeb11d16bb95c3a7e3c25c842cb9f20cbc8c18f8be0461a4a0dcc861ad386a0

RUN apk update && apk upgrade
RUN pip install --no-cache-dir --upgrade pip

# TODO Remove this once alpine upgrade available as it patches a vulnerability
RUN apk update && apk add --upgrade "zlib==1.3.2-r1"

COPY --from=ghcr.io/astral-sh/uv@sha256:61d393e44e249f2e4b526b6c7ddcecce245946826e608e11c93ad4f5bba55b21 /uv /uvx /bin/
COPY uv.lock .
COPY pyproject.toml .
RUN uv export --locked -o requirements.txt \
    && python -m pip install --no-cache-dir -r requirements.txt

RUN pip uninstall -y wheel setuptools

RUN addgroup -S backup && adduser -S -G backup backup

COPY --chown=backup:backup ./cross_account_backup.py /cross_account_backup.py
RUN chmod 755 /cross_account_backup.py

USER backup

CMD ["python3", "/cross_account_backup.py"]
