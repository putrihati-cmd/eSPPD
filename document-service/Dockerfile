# Dockerfile for Python Document Service
FROM python:3.11-slim

WORKDIR /app

# Install system dependencies for document generation
RUN apt-get update && apt-get install -y \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements first for caching
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application code
COPY . .

# Expose port
EXPOSE 8001

# In production use gunicorn with uvicorn workers
CMD ["gunicorn", "-c", "gunicorn_conf.py", "main:app"]
