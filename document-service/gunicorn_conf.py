import multiprocessing

# gunicorn_conf.py
bind = "0.0.0.0:8001"
workers = multiprocessing.cpu_count() * 2 + 1
worker_class = "uvicorn.workers.UvicornWorker"
keepalive = 120
timeout = 120
accesslog = "-"
errorlog = "-"
loglevel = "info"
