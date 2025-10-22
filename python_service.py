#!/usr/bin/env python3
"""
Simple Python service for Smart Invoice extraction
This is a fallback service when the main Python service is not available
"""

from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import os
import sys
from urllib.parse import urlparse, parse_qs

class SmartInvoiceHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/health':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            response = {'status': 'healthy', 'service': 'smart-invoice-python'}
            self.wfile.write(json.dumps(response).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_POST(self):
        if self.path == '/extract':
            try:
                # Read the request body
                content_length = int(self.headers['Content-Length'])
                post_data = self.rfile.read(content_length)
                
                # Parse the request
                request_data = json.loads(post_data.decode('utf-8'))
                
                # Create a mock response
                response = {
                    'success': True,
                    'data': {
                        'invoice_number': 'MOCK-001',
                        'amount': 500000,
                        'vendor': 'Mock Vendor',
                        'date': '2024-01-01',
                        'confidence': 0.85,
                        'line_items': [
                            {
                                'description': 'Test Item 1',
                                'quantity': 1,
                                'price': 250000
                            },
                            {
                                'description': 'Test Item 2', 
                                'quantity': 1,
                                'price': 250000
                            }
                        ]
                    },
                    'message': 'Mock extraction completed'
                }
                
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps(response).encode())
                
            except Exception as e:
                error_response = {
                    'success': False,
                    'error': str(e),
                    'message': 'Extraction failed'
                }
                self.send_response(500)
                self.send_header('Content-type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps(error_response).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def log_message(self, format, *args):
        # Suppress default logging
        pass

def run_server(port=8001):
    server_address = ('', port)
    httpd = HTTPServer(server_address, SmartInvoiceHandler)
    print(f"Smart Invoice Python Service running on port {port}")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nShutting down server...")
        httpd.shutdown()

if __name__ == '__main__':
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 8001
    run_server(port)
