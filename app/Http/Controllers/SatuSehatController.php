<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SatuSehatController extends Controller
{
    private $baseUrl;
    private $authUrl;
    private $clientId;
    private $clientSecret;
    private $organizationId;

    public function __construct()
    {
        $this->baseUrl = env('SATUSEHAT_BASE_URL', 'https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1');
        $this->authUrl = env('SATUSEHAT_AUTH_URL', 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1');
        $this->clientId = env('SATUSEHAT_CLIENT_ID');
        $this->clientSecret = env('SATUSEHAT_CLIENT_SECRET');
        $this->organizationId = env('SATUSEHAT_ORGANIZATION_ID');
    }

    public function getToken()
    {
        if (Cache::has('satusehat_token')) {
            return Cache::get('satusehat_token');
        }

        try {
            $response = Http::asForm()->post($this->authUrl . '/accesstoken?grant_type=client_credentials', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;

                Cache::put('satusehat_token', $token, $expiresIn - 60);

                return $token;
            } else {
                Log::error('SatuSehat Auth Failed: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('SatuSehat Auth Exception: ' . $e->getMessage());
            return null;
        }
    }


    public function searchPatientByNik($nik)
    {
        $response = $this->sendRequest('GET', 'Patient?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik);

        if ($response['status'] == 'success' && !empty($response['response']['entry'])) {
            return $response['response']['entry'][0]['resource']['id'] ?? null;
        }

        return null;
    }
    
    public function sendRequest($method, $endpoint, $data = [])
    {
        $token = $this->getToken();

        if (!$token) {
            return ['status' => 'failed', 'message' => 'Failed to get access token'];
        }

        try {
            $url = $this->baseUrl . '/' . $endpoint;
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->$method($url, $data);

            return [
                'status' => $response->successful() ? 'success' : 'failed',
                'code' => $response->status(),
                'response' => $response->json()
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
