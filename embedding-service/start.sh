#!/bin/bash

# Navigate to embedding service directory
cd "$(dirname "$0")"

# Activate virtual environment
source venv/bin/activate

# Start the Flask service
python app.py
