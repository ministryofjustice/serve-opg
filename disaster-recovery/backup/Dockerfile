FROM python:3.14.7-alpine3.24@sha256:c6ead215bfd31f1e433d968853b7a769989117115b728874824e6c0a27cb96fc

RUN apk update && apk upgrade
RUN pip install --no-cache-dir --upgrade pip

COPY --from=ghcr.io/astral-sh/uv@sha256:2bb3ebca0a796a155094a27773d290c4b074572e6107f171d88d086682fd2500 /uv /uvx /bin/
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
