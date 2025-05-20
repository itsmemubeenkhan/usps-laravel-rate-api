<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    // Show the form
    public function showForm()
    {
        return view('shipping-form');
    }

    // Get OAuth token from USPS
    private function getAccessToken()
    {
        $consumerKey = 'Yh50H3LUvHWnKWu5ZNQXkAjPlqlWA1qDPDqfLS1mvEweWCcd';
        $consumerSecret = 'qgDhAdr9GzkZgh6AFT2mwk4WY361A6OEGyjhw8GW0rrdbUWUr8pJMqj6P4zLqu7C';
    
        $response = Http::asForm()->post('https://apis.usps.com/oauth2/v3/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $consumerKey,
            'client_secret' => $consumerSecret,
            'scope' => 'shipments',
        ]);
    
        if ($response->successful()) {
            return $response['access_token'];
        }
    
        return $response;
    }

    // Get USPS shipping rates
    public function getRates(Request $request)
    {
        // Retrieve input values from the request
        $originZip = $request->input('origin_zip', 73301);
        $destZip = $request->input('destination_zip', 10009);
        $weight = $request->input('weight', 10);
        $length = $request->input('length', 5); // Default to 0 if not provided
        $width = $request->input('width', 15);   // Default to 0 if not provided
        $height = $request->input('height', 10);  // Default to 0 if not provided
        $mailClass = $request->input('mail_class', 'ALL'); // Default to 'PARCEL_SELECT' if not provided

        // Retrieve the access token
        $token = $this->getAccessToken();

        if (!$token) {
            return response()->json(['error' => 'Unable to get USPS access token'], 500);
        }

        // Prepare the request payload
        $payload = [
            "pricingOptions" => [
                [
                    "priceType" => "RETAIL",
                    "paymentAccount" => [
                        "accountType" => "EPS"
                    ]
                ]
            ],
            "originZIPCode" => $originZip,
            "destinationZIPCode" => $destZip,
            "destinationEntryFacilityType" => "NONE", // Default to 'NONE' if not provided
            "packageDescription" => [
                "weight" => 10,
                "length" => $length,
                "height" => $height,
                "width" => $width,
                "mailClass" => 'ALL',
            ],
            "shippingFilter" => "PRICE" // Default to 'PRICE' if not provided
        ];

        // Send the request to USPS API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post('https://apis.usps.com/shipments/v3/options/search', $payload);

        // Check if the response is successful
        if ($response->successful()) {
            return response()->json($response->json());
        }

        // Return an error if the request failed
        return response()->json(['error' => 'Failed to retrieve USPS rates.'], 500);
    }


}
