from flask import Flask, jsonify
from flask_cors import CORS
from scraper import scrape_jobs

app = Flask(__name__)
CORS(app)

@app.route('/api/jobs', methods=['GET'])
def get_jobs():
    jobs = scrape_jobs()
    return jsonify(jobs)

if __name__ == "__main__":
    app.run(debug=True)