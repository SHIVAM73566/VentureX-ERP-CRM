# Wix Integration Guide for VentureX ERP & CRM

## Important: Wix Cannot Run Laravel Directly

Wix is a proprietary website builder with its own ecosystem. It cannot execute PHP/Laravel code. However, you can integrate Wix with VentureX ERP & CRM through the REST API.

## Integration Approach

```
Wix Website → Wix Velo (Backend) → VentureX REST API → VentureX ERP & CRM
```

### What You Can Do

1. **Submit forms** from Wix to VentureX API
2. **Fetch data** from VentureX to display in Wix
3. **Sync contacts** between Wix and VentureX
4. **Create leads** from Wix contact forms

### What You Cannot Do

1. Run Laravel/PHP on Wix
2. Direct database access from Wix
3. Full server-side rendering
4. Complex business logic execution on Wix

## Prerequisites

1. VentureX ERP & CRM installed and running with API enabled
2. Wix website with **Velo** enabled (paid plan)
3. API token or authentication configured in VentureX
4. Domain with SSL certificate

## Step 1: Configure VentureX API

### Generate API Token

1. Login to VentureX ERP & CRM
2. Navigate to **Settings → API Tokens**
3. Create a new token
4. Copy and save the token securely

### Enable CORS

Add your Wix domain to allowed origins:

```php
// In config/cors.php or middleware
'allowed_origins' => [
    'https://yourdomain.wixsite.com',
    'https://www.yourdomain.com',
],
```

## Step 2: Wix Velo Backend Setup

### Enable Velo

1. In Wix Editor, click **Dev Mode** → **Turn on Dev Mode**
2. This enables the Velo API for custom code

### Create Backend Module

1. In Wix Dashboard, go to **CMS** → **Backend Collections**
2. Create a collection for API logs (optional)
3. Add a **web module** for API communication

## Step 3: Lead Submission Integration

### Wix Velo Code (Frontend)

```javascript
// Page code for contact form
import wixWindow from 'wix-window';

$w.onReady(function () {
    // Form submission handler
    $w('#submitButton').onClick(() => {
        const formData = {
            name: $w('#nameInput').value,
            email: $w('#emailInput').value,
            phone: $w('#phoneInput').value,
            company: $w('#companyInput').value,
            message: $w('#messageInput').value
        };

        submitToVentureX(formData);
    });
});

async function submitToVentureX(data) {
    try {
        const response = await fetch('https://yourdomain.com/api/v1/leads', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer YOUR_API_TOKEN',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            wixWindow.openLightbox("Success Message");
            clearForm();
        } else {
            console.error('API Error:', result);
            wixWindow.openLightbox("Error Message");
        }
    } catch (error) {
        console.error('Network Error:', error);
        wixWindow.openLightbox("Network Error");
    }
}

function clearForm() {
    $w('#nameInput').value = '';
    $w('#emailInput').value = '';
    $w('#phoneInput').value = '';
    $w('#companyInput').value = '';
    $w('#messageInput').value = '';
}
```

### Backend Web Module (Recommended for Security)

Create a backend module at `src/backend/api.js`:

```javascript
// Backend module for API calls
import { apiSecret } from 'wix-secrets';

export async function submitLead(leadData) {
    const apiKey = await apiSecret.get("VENTUREX_API_KEY");
    
    const response = await fetch('https://yourdomain.com/api/v1/leads', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiKey}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify(leadData)
    });

    if (!response.ok) {
        throw new Error(`API error: ${response.status}`);
    }

    return await response.json();
}
```

### Frontend Code Using Backend Module

```javascript
import { submitLead } from 'src/backend/api';

$w.onReady(function () {
    $w('#submitButton').onClick(async () => {
        try {
            await submitLead({
                name: $w('#nameInput').value,
                email: $w('#emailInput').value,
                phone: $w('#phoneInput').value,
                source: 'wix_website'
            });
            
            // Show success message
            $w('#successText').text = "Thank you! We'll contact you soon.";
            $w('#successMessage').show();
            $w('#contactForm').reset();
        } catch (error) {
            $w('#errorText').text = "Something went wrong. Please try again.";
            $w('#errorMessage').show();
        }
    });
});
```

## Step 4: Data Synchronization

### Pull Data from VentureX to Wix

```javascript
// Fetch contacts from VentureX
async function fetchContacts() {
    try {
        const response = await fetch('https://yourdomain.com/api/v1/contacts?limit=100', {
            headers: {
                'Authorization': 'Bearer YOUR_API_TOKEN',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        return data.data; // Return contacts array
    } catch (error) {
        console.error('Failed to fetch contacts:', error);
        return [];
    }
}

// Display contacts in repeater
async function displayContacts() {
    const contacts = await fetchContacts();
    
    $w('#contactsRepeater').data = contacts.map(contact => ({
        _id: contact.id,
        name: contact.name,
        email: contact.email,
        phone: contact.phone
    }));
}
```

### Sync Wix Members to VentureX

```javascript
import wixUsers from 'wix-users';

async function syncUserToVentureX() {
    const user = await wixUsers.getCurrentUser();
    
    if (user) {
        const profile = await user.getMember();
        
        await fetch('https://yourdomain.com/api/v1/contacts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer YOUR_API_TOKEN'
            },
            body: JSON.stringify({
                name: profile.name,
                email: profile.email,
                phone: profile.phone,
                source: 'wix_member'
            })
        });
    }
}
```

## Step 5: Webhook Configuration

### Receive Webhooks from VentureX

Set up a webhook endpoint in Wix:

```javascript
// This would require Wix's webhook functionality
// or a third-party service like Zapier/Make

// Alternative: Use Wix Automations
// 1. Create automation triggered by form submission
// 2. Use HTTP action to call VentureX API
// 3. Log response in Wix collection
```

### Using Wix Automations

1. Go to **Automations** in Wix Dashboard
2. Create new automation
3. Trigger: Form submitted
4. Action: Send webhook to `https://yourdomain.com/api/v1/webhooks/wix`
5. Include form data in payload

## Step 6: Product Inquiry Form

```javascript
// Product inquiry page
$w.onReady(function () {
    $w('#inquiryButton').onClick(async () => {
        const inquiry = {
            name: $w('#customerName').value,
            email: $w('#customerEmail').value,
            product_id: $w('#productDropdown').value,
            quantity: $w('#quantityInput').value,
            message: $w('#inquiryMessage').value,
            type: 'product_inquiry'
        };

        try {
            const response = await fetch('https://yourdomain.com/api/v1/inquiries', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer YOUR_API_TOKEN'
                },
                body: JSON.stringify(inquiry)
            });

            if (response.ok) {
                $w('#successBox').show();
                $w('#inquiryForm').reset();
            }
        } catch (error) {
            $w('#errorBox').show();
        }
    });
});
```

## Limitations

### Technical Limitations

1. **No PHP execution**: Wix cannot run Laravel/PHP code
2. **Limited backend logic**: Velo has restricted server-side capabilities
3. **API rate limits**: Both Wix and VentureX have rate limits
4. **CORS restrictions**: Must be configured on VentureX side
5. **No direct database access**: All data must go through API

### Security Considerations

1. **Never expose API tokens in frontend code**
2. **Use Wix Secrets Manager** for sensitive data
3. **Implement rate limiting** on VentureX API
4. **Validate all input** on server side
5. **Use HTTPS** for all API calls

### Performance Considerations

1. **API response time**: Network latency affects user experience
2. **Caching**: Implement client-side caching for frequently accessed data
3. **Pagination**: Handle large datasets with pagination
4. **Error handling**: Graceful degradation when API is unavailable

## Alternatives to Consider

### If You Need Full Laravel Integration

1. **Separate Domain**: Host VentureX on a subdomain (erp.yourdomain.com)
2. **Iframe Embed**: Embed VentureX forms/pages in Wix via iframe
3. **Redirect Flow**: Wix redirects to VentureX for complex forms

### If You Need Rich Functionality

1. **Headless CMS**: Use Wix as frontend, VentureX as backend
2. **Progressive Web App**: Build PWA with VentureX, link from Wix
3. **Custom Solution**: Use a different frontend framework

## Testing

### Test API Connection

```javascript
// Test endpoint
async function testConnection() {
    try {
        const response = await fetch('https://yourdomain.com/api/v1/health', {
            headers: {
                'Authorization': 'Bearer YOUR_API_TOKEN'
            }
        });
        
        const result = await response.json();
        console.log('API Status:', result.status);
        return result.status === 'ok';
    } catch (error) {
        console.error('Connection failed:', error);
        return false;
    }
}
```

### Test Form Submission

```javascript
// Test with sample data
const testData = {
    name: "Test User",
    email: "test@example.com",
    phone: "+1234567890",
    source: "wix_test"
};

submitToVentureX(testData);
```

## Support

For Wix-specific issues, refer to:
- [Wix Velo Documentation](https://www.wix.com/velo)
- [Wix Corvid by Wix](https://support.wix.com/en/corvid-by-wix)

For VentureX API issues, refer to:
- VentureX API Documentation
- VentureX Support Team
