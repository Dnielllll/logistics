# Location API Setup

## Geoapify API Integration

The shipment creation feature now uses Geoapify API for improved location services.

### Setup Instructions:

1. **Get a Geoapify API Key:**
   - Visit [https://www.geoapify.com/](https://www.geoapify.com/)
   - Sign up for a free account
   - Go to your dashboard and create an API key

2. **Configure the API Key:**
   - ✅ **DONE**: API key has been configured in `config/api.php`
   - The system is now ready to use Geoapify location services

3. **Features:**
   - Click on the map to select a location
   - Search for locations by typing and pressing Enter
   - Automatic reverse geocoding to get location names
   - Better error handling and user feedback

### API Limits:
- Free tier: 3,000 requests/day
- Paid plans available for higher limits

### Troubleshooting:
- If location search doesn't work, check your API key
- Make sure your API key has geocoding permissions
- Check browser console for any JavaScript errors