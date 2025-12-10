"""
Free Embedding Microservice
Uses Sentence Transformers (100% FREE, no API costs)
Model: all-MiniLM-L6-v2 (384 dimensions)

Installation:
pip install flask sentence-transformers torch

Run:
python app.py

The service will run on http://localhost:5001
"""

from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer
import logging

app = Flask(__name__)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load model once at startup (caches in memory)
# This takes ~5 seconds first time, then instant
logger.info("Loading embedding model...")
model = SentenceTransformer('sentence-transformers/all-MiniLM-L6-v2')
logger.info("Model loaded successfully!")

# Model info for reference
MODEL_INFO = {
    'name': 'all-MiniLM-L6-v2',
    'dimensions': 384,
    'max_tokens': 256,
    'speed': 'Fast',
    'quality': 'Good',
    'cost': 'FREE'
}


@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'model': MODEL_INFO
    })


@app.route('/embed', methods=['POST'])
def embed_single():
    """
    Generate embedding for a single text
    
    Request body:
    {
        "text": "Your text here"
    }
    
    Response:
    {
        "embedding": [0.123, -0.456, ...],
        "dimensions": 384,
        "model": "all-MiniLM-L6-v2"
    }
    """
    try:
        data = request.get_json()
        
        if not data or 'text' not in data:
            return jsonify({'error': 'Missing "text" field'}), 400
        
        text = data['text']
        
        if not text or not text.strip():
            return jsonify({'error': 'Text cannot be empty'}), 400
        
        # Generate embedding
        embedding = model.encode(text, convert_to_tensor=False)
        
        return jsonify({
            'embedding': embedding.tolist(),
            'dimensions': len(embedding),
            'model': MODEL_INFO['name']
        })
    
    except Exception as e:
        logger.error(f"Error generating embedding: {str(e)}")
        return jsonify({'error': str(e)}), 500


@app.route('/embed/batch', methods=['POST'])
def embed_batch():
    """
    Generate embeddings for multiple texts (more efficient)
    
    Request body:
    {
        "texts": ["First text", "Second text", "Third text"]
    }
    
    Response:
    {
        "embeddings": [[0.123, ...], [-0.456, ...], ...],
        "count": 3,
        "dimensions": 384,
        "model": "all-MiniLM-L6-v2"
    }
    """
    try:
        data = request.get_json()
        
        if not data or 'texts' not in data:
            return jsonify({'error': 'Missing "texts" field'}), 400
        
        texts = data['texts']
        
        if not isinstance(texts, list):
            return jsonify({'error': '"texts" must be an array'}), 400
        
        if not texts:
            return jsonify({'error': 'Texts array cannot be empty'}), 400
        
        # Filter out empty texts
        valid_texts = [t for t in texts if t and t.strip()]
        
        if not valid_texts:
            return jsonify({'error': 'All texts are empty'}), 400
        
        # Generate embeddings (batch is faster than one-by-one)
        embeddings = model.encode(valid_texts, convert_to_tensor=False, show_progress_bar=False)
        
        return jsonify({
            'embeddings': [emb.tolist() for emb in embeddings],
            'count': len(embeddings),
            'dimensions': len(embeddings[0]),
            'model': MODEL_INFO['name']
        })
    
    except Exception as e:
        logger.error(f"Error generating batch embeddings: {str(e)}")
        return jsonify({'error': str(e)}), 500


@app.route('/similarity', methods=['POST'])
def calculate_similarity():
    """
    Calculate cosine similarity between two texts
    
    Request body:
    {
        "text1": "First text",
        "text2": "Second text"
    }
    
    Response:
    {
        "similarity": 0.856,
        "model": "all-MiniLM-L6-v2"
    }
    """
    try:
        data = request.get_json()
        
        if not data or 'text1' not in data or 'text2' not in data:
            return jsonify({'error': 'Missing "text1" or "text2" field'}), 400
        
        text1 = data['text1']
        text2 = data['text2']
        
        # Generate embeddings
        embeddings = model.encode([text1, text2], convert_to_tensor=False)
        
        # Calculate cosine similarity
        from numpy import dot
        from numpy.linalg import norm
        
        similarity = dot(embeddings[0], embeddings[1]) / (norm(embeddings[0]) * norm(embeddings[1]))
        
        return jsonify({
            'similarity': float(similarity),
            'model': MODEL_INFO['name']
        })
    
    except Exception as e:
        logger.error(f"Error calculating similarity: {str(e)}")
        return jsonify({'error': str(e)}), 500


print("Top-level code ran, __name__ =", __name__)

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)




