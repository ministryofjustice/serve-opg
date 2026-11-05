FROM python:3.15.0-alpine3.24@sha256:2699b90f4d562a22e2dd076c82f964032f64814204bff36d271a54ba4cc2c7e2

COPY --from=ghcr.io/astral-sh/uv@sha256:32a7bb39b496babbd997b0a39a4d66c126bdaf5661b302bf6f06abda8deca6ff /uv /uvx /bin/
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
