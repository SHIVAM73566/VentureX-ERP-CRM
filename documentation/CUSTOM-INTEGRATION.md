# Custom Website Integration Guide for VentureX ERP & CRM

This guide covers integrating VentureX ERP & CRM with any custom website or application via the REST API.

## REST API Overview

The VentureX API follows RESTful conventions and uses JSON for request/response bodies.

### Base URL

```
https://yourdomain.com/api/v1
```

### API Structure

```
/api/v1/
├── auth/
│   ├── login
│   ├── logout
│   └── refresh
├── contacts/
├── leads/
├── companies/
├── deals/
├── tickets/
├── invoices/
├── products/
├── reports/
└── webhooks/
```

## Authentication

### Bearer Token Authentication

All API requests require a Bearer token in the Authorization header.

#### Getting a Token

```bash
# Login to get token
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "your-password"}'
```

Response:
```json
{
    "success": true,
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_at": "2024-12-31 23:59:59"
}
```

#### Using the Token

Include the token in all subsequent requests:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### API Token Authentication

For automated integrations, use API tokens:

1. Generate token in VentureX Dashboard → Settings → API Tokens
2. Use in requests:

```
Authorization: Bearer your-api-token-here
```

## API Endpoints

### Contacts

#### List Contacts

```bash
curl -X GET https://yourdomain.com/api/v1/contacts \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Query Parameters:
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 20, max: 100)
- `search` (string): Search by name, email, or phone
- `sort` (string): Sort field (name, email, created_at)
- `order` (string): Sort order (asc, desc)

Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "company": "Acme Corp",
            "created_at": "2024-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100
    }
}
```

#### Create Contact

```bash
curl -X POST https://yourdomain.com/api/v1/contacts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+0987654321",
    "company": "XYZ Inc",
    "notes": "Met at conference"
  }'
```

Response:
```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "phone": "+0987654321",
        "company": "XYZ Inc",
        "notes": "Met at conference",
        "created_at": "2024-01-20T14:45:00Z"
    }
}
```

#### Update Contact

```bash
curl -X PUT https://yourdomain.com/api/v1/contacts/2 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+1122334455",
    "notes": "Updated after follow-up call"
  }'
```

#### Delete Contact

```bash
curl -X DELETE https://yourdomain.com/api/v1/contacts/2 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Leads

#### List Leads

```bash
curl -X GET "https://yourdomain.com/api/v1/leads?status=new&source=website" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Query Parameters:
- `status` (string): Filter by status (new, contacted, qualified, converted, lost)
- `source` (string): Filter by source (website, referral, campaign, etc.)
- `assigned_to` (int): Filter by assigned user ID
- `date_from` (string): Filter from date (YYYY-MM-DD)
- `date_to` (string): Filter to date (YYYY-MM-DD)

#### Create Lead

```bash
curl -X POST https://yourdomain.com/api/v1/leads \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Lead",
    "email": "lead@example.com",
    "phone": "+5555555555",
    "company": "New Company",
    "source": "website",
    "status": "new",
    "value": 5000,
    "notes": "Interested in premium plan"
  }'
```

#### Update Lead Status

```bash
curl -X PATCH https://yourdomain.com/api/v1/leads/15/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "contacted"}'
```

### Companies

#### List Companies

```bash
curl -X GET "https://yourdomain.com/api/v1/companies?industry=technology" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Create Company

```bash
curl -X POST https://yourdomain.com/api/v1/companies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tech Startup Inc",
    "industry": "technology",
    "website": "https://techstartup.com",
    "phone": "+1112223333",
    "email": "info@techstartup.com",
    "address": "123 Tech Street, Silicon Valley, CA"
  }'
```

### Deals

#### List Deals

```bash
curl -X GET "https://yourdomain.com/api/v1/deals?stage=negotiation" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Query Parameters:
- `stage` (string): Filter by stage (lead, qualified, proposal, negotiation, closed_won, closed_lost)
- `contact_id` (int): Filter by contact
- `company_id` (int): Filter by company
- `value_min` (decimal): Minimum deal value
- `value_max` (decimal): Maximum deal value

#### Create Deal

```bash
curl -X POST https://yourdomain.com/api/v1/deals \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Enterprise License",
    "contact_id": 1,
    "company_id": 5,
    "value": 50000,
    "stage": "proposal",
    "expected_close_date": "2024-03-31",
    "notes": "Annual enterprise license deal"
  }'
```

### Tickets

#### List Tickets

```bash
curl -X GET "https://yourdomain.com/api/v1/tickets?status=open&priority=high" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Create Ticket

```bash
curl -X POST https://yourdomain.com/api/v1/tickets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Login Issue",
    "description": "Unable to login to the system",
    "contact_id": 1,
    "priority": "high",
    "category": "technical",
    "status": "open"
  }'
```

### Invoices

#### List Invoices

```bash
curl -X GET "https://yourdomain.com/api/v1/invoices?status=unpaid" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Create Invoice

```bash
curl -X POST https://yourdomain.com/api/v1/invoices \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "contact_id": 1,
    "company_id": 5,
    "items": [
      {
        "description": "ERP License - Annual",
        "quantity": 1,
        "unit_price": 5000
      },
      {
        "description": "Implementation Service",
        "quantity": 40,
        "unit_price": 150
      }
    ],
    "tax_rate": 10,
    "notes": "Net 30 payment terms",
    "due_date": "2024-02-28"
  }'
```

## JavaScript Fetch Examples

### Basic Fetch Request

```javascript
const API_BASE = 'https://yourdomain.com/api/v1';
const TOKEN = 'your-api-token';

async function fetchContacts() {
    try {
        const response = await fetch(`${API_BASE}/contacts`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data.data;
    } catch (error) {
        console.error('Error fetching contacts:', error);
        throw error;
    }
}
```

### Create Contact with Fetch

```javascript
async function createContact(contactData) {
    try {
        const response = await fetch(`${API_BASE}/contacts`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(contactData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to create contact');
        }

        return await response.json();
    } catch (error) {
        console.error('Error creating contact:', error);
        throw error;
    }
}

// Usage
const newContact = await createContact({
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+1234567890'
});
```

### Submit Lead from Contact Form

```javascript
document.getElementById('contactForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    try {
        const response = await fetch(`${API_BASE}/leads`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: form.name.value,
                email: form.email.value,
                phone: form.phone.value,
                message: form.message.value,
                source: 'website_contact_form'
            })
        });

        if (response.ok) {
            form.reset();
            alert('Thank you! We will contact you soon.');
        } else {
            alert('Something went wrong. Please try again.');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';
    }
});
```

## PHP Examples

### Basic API Client

```php
<?php

class VentureXClient
{
    private $baseUrl;
    private $token;

    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
    }

    public function request($method, $endpoint, $data = [])
    {
        $url = $this->baseUrl . '/api/v1' . $endpoint;
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", [
                    'Authorization: Bearer ' . $this->token,
                    'Content-Type: application/json',
                    'Accept: application/json'
                ])
            ]
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return json_decode($response, true);
    }

    public function getContacts($params = [])
    {
        $query = http_build_query($params);
        return $this->request('GET', '/contacts' . ($query ? '?' . $query : ''));
    }

    public function createContact($data)
    {
        return $this->request('POST', '/contacts', $data);
    }

    public function createLead($data)
    {
        return $this->request('POST', '/leads', $data);
    }
}

// Usage
$client = new VentureXClient('https://yourdomain.com', 'your-api-token');

// Get contacts
$contacts = $client->getContacts(['per_page' => 10]);

// Create contact
$newContact = $client->createContact([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890'
]);

// Create lead
$newLead = $client->createLead([
    'name' => 'New Lead',
    'email' => 'lead@example.com',
    'source' => 'website'
]);
```

### cURL Function

```php
<?php

function venturexApi($endpoint, $data = [], $method = 'GET')
{
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://yourdomain.com/api/v1' . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer your-api-token',
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ]);

    if ($method !== 'GET' && !empty($data)) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Usage
$result = venturexApi('/contacts', [], 'GET');
$result = venturexApi('/leads', [
    'name' => 'New Lead',
    'email' => 'lead@example.com'
], 'POST');
```

## Python Examples

### Using requests library

```python
import requests

class VentureXClient:
    def __init__(self, base_url, token):
        self.base_url = base_url.rstrip('/')
        self.api_url = f"{self.base_url}/api/v1"
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }

    def get_contacts(self, params=None):
        response = requests.get(
            f"{self.api_url}/contacts",
            headers=self.headers,
            params=params
        )
        response.raise_for_status()
        return response.json()

    def create_contact(self, data):
        response = requests.post(
            f"{self.api_url}/contacts",
            headers=self.headers,
            json=data
        )
        response.raise_for_status()
        return response.json()

    def create_lead(self, data):
        response = requests.post(
            f"{self.api_url}/leads",
            headers=self.headers,
            json=data
        )
        response.raise_for_status()
        return response.json()

# Usage
client = VentureXClient('https://yourdomain.com', 'your-api-token')

# Get contacts
contacts = client.get_contacts({'per_page': 10})

# Create contact
new_contact = client.create_contact({
    'name': 'John Doe',
    'email': 'john@example.com',
    'phone': '+1234567890'
})

# Create lead
new_lead = client.create_lead({
    'name': 'New Lead',
    'email': 'lead@example.com',
    'source': 'website'
})
```

### Flask Webhook Example

```python
from flask import Flask, request, jsonify
import hmac
import hashlib

app = Flask(__name__)

WEBHOOK_SECRET = 'your-webhook-secret'

@app.route('/webhooks/venturex', methods=['POST'])
def venturex_webhook():
    # Verify webhook signature
    signature = request.headers.get('X-VentureX-Signature')
    if not signature:
        return jsonify({'error': 'Missing signature'}), 401
    
    expected = hmac.new(
        WEBHOOK_SECRET.encode(),
        request.data,
        hashlib.sha256
    ).hexdigest()
    
    if not hmac.compare_digest(signature, expected):
        return jsonify({'error': 'Invalid signature'}), 401
    
    # Process webhook
    data = request.json
    event_type = data.get('event')
    
    if event_type == 'lead.created':
        handle_new_lead(data['data'])
    elif event_type == 'contact.updated':
        handle_contact_update(data['data'])
    elif event_type == 'deal.closed':
        handle_deal_closed(data['data'])
    
    return jsonify({'status': 'success'}), 200

def handle_new_lead(lead):
    # Process new lead
    print(f"New lead: {lead['name']}")

def handle_contact_update(contact):
    # Process contact update
    print(f"Contact updated: {contact['name']}")

def handle_deal_closed(deal):
    # Process closed deal
    print(f"Deal closed: {deal['title']} - ${deal['value']}")

if __name__ == '__main__':
    app.run(debug=True)
```

## Webhook Setup

### Creating Webhooks

1. Login to VentureX ERP & CRM
2. Navigate to **Settings → Webhooks**
3. Click **Create Webhook**

### Webhook Configuration

```json
{
    "url": "https://yourdomain.com/webhooks/venturex",
    "events": [
        "lead.created",
        "lead.updated",
        "lead.converted",
        "contact.created",
        "contact.updated",
        "deal.created",
        "deal.closed_won",
        "ticket.created",
        "ticket.updated"
    ],
    "secret": "your-webhook-secret",
    "active": true
}
```

### Webhook Payload

```json
{
    "event": "lead.created",
    "timestamp": "2024-01-20T14:45:00Z",
    "data": {
        "id": 123,
        "name": "New Lead",
        "email": "lead@example.com",
        "status": "new",
        "source": "website"
    }
}
```

### Verifying Webhooks

```php
<?php

function verifyWebhook($payload, $signature, $secret)
{
    $expected = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expected, $signature);
}

// In your webhook handler
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_VENTUREX_SIGNATURE'];

if (verifyWebhook($payload, $signature, 'your-webhook-secret')) {
    $data = json_decode($payload, true);
    // Process webhook
}
```

## Error Handling

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

### Error Response Format

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "phone": ["The phone format is invalid."]
    }
}
```

### Handling Errors in JavaScript

```javascript
async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            ...options,
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Content-Type': 'application/json',
                ...options.headers
            }
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                // Validation error
                throw new ValidationError(data.errors);
            } else if (response.status === 401) {
                // Unauthorized
                throw new AuthError('Invalid or expired token');
            } else if (response.status === 429) {
                // Rate limited
                throw new RateLimitError('Too many requests');
            } else {
                throw new ApiError(data.message || 'API error', response.status);
            }
        }

        return data;
    } catch (error) {
        if (error instanceof ApiError) {
            throw error;
        }
        throw new NetworkError('Network error occurred');
    }
}

class ApiError extends Error {
    constructor(message, status) {
        super(message);
        this.status = status;
    }
}

class ValidationError extends ApiError {
    constructor(errors) {
        super('Validation failed');
        this.errors = errors;
    }
}

class AuthError extends ApiError {
    constructor(message) {
        super(message, 401);
    }
}

class RateLimitError extends ApiError {
    constructor(message) {
        super(message, 429);
    }
}

class NetworkError extends Error {
    constructor(message) {
        super(message);
    }
}
```

## Rate Limiting

### Default Limits

- **Authenticated requests**: 60 requests per minute
- **API token requests**: 120 requests per minute
- **Webhook deliveries**: 100 per minute

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1706145600
```

### Handling Rate Limits

```javascript
async function apiRequestWithRetry(endpoint, options = {}, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const response = await fetch(`${API_BASE}${endpoint}`, options);
            
            if (response.status === 429) {
                const retryAfter = response.headers.get('Retry-After') || 60;
                console.log(`Rate limited. Retrying in ${retryAfter} seconds...`);
                await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
                continue;
            }
            
            return await response.json();
        } catch (error) {
            if (i === maxRetries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
        }
    }
    throw new Error('Max retries exceeded');
}
```

## Best Practices

### Security

1. **Never expose API tokens** in client-side code for production
2. **Use HTTPS** for all API calls
3. **Validate webhooks** using signatures
4. **Implement rate limiting** on your endpoints
5. **Store tokens securely** using environment variables
6. **Rotate tokens** periodically
7. **Use least privilege** - only request necessary permissions

### Performance

1. **Cache responses** when appropriate
2. **Use pagination** for list endpoints
3. **Implement exponential backoff** for retries
4. **Batch operations** when possible
5. **Use webhooks** instead of polling

### Reliability

1. **Handle errors gracefully**
2. **Implement retry logic** with backoff
3. **Log API calls** for debugging
4. **Monitor API usage** and errors
5. **Have fallback mechanisms**

### Code Quality

1. **Use type hints** in PHP/Python
2. **Create reusable client classes**
3. **Write unit tests** for API integration
4. **Document your integration**
5. **Follow REST conventions**

## Testing

### Test Environment

Use a staging environment for testing:

```javascript
const API_BASE = process.env.NODE_ENV === 'production' 
    ? 'https://yourdomain.com'
    : 'https://staging.yourdomain.com';
```

### Postman Collection

Create a Postman collection for testing:

1. Import the API endpoints
2. Set up environment variables
3. Create test scripts
4. Share with team members

### Test Data

```javascript
const testData = {
    contact: {
        name: 'Test Contact',
        email: 'test@example.com',
        phone: '+1234567890'
    },
    lead: {
        name: 'Test Lead',
        email: 'lead@example.com',
        source: 'test'
    }
};
```

## Support

For API-specific questions:
- API Documentation: [API Reference](https://yourdomain.com/api/documentation)
- API Status: [status.yourdomain.com](https://status.yourdomain.com)
- Support Email: api-support@yourdomain.com

For integration help:
- Community Forum
- GitHub Issues
- Stack Overflow (tag: venturex-api)
