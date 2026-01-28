# eSPPD Document Service
FROM python:3.11

# Set environment variables
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1
ENV TEMPLATES_DIR=/app/templates
ENV GENERATED_DIR=/app/generated

# Set work directory
WORKDIR /app

# Install system dependencies (curl for healthcheck + build deps for lxml)
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl gcc libxml2-dev libxslt-dev zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*



# Install Python dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application code
COPY . .

# Create directories
RUN mkdir -p /app/templates /app/generated

# Expose port
EXPOSE 8001

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8001/health || exit 1

# Run the application
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001"]
