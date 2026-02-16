<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SatuSehatController extends Controller
{
    protected $baseUrl;
    protected $authUrl;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->baseUrl      = env('SATUSEHAT_BASE_URL', 'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1');
        $this->authUrl      = env('SATUSEHAT_AUTH_URL', 'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1');
        $this->clientId     = env('SATUSEHAT_CLIENT_ID');
        $this->clientSecret = env('SATUSEHAT_CLIENT_SECRET');
    }

    /**
     * Get OAuth2 access token from SatuSehat, cached for 50 minutes.
     */
    public function getAccessToken()
    {
        return Cache::remember('satusehat_token', 3000, function () {
            $response = Http::asForm()->post($this->authUrl . '/accesstoken?grant_type=client_credentials', [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('SatuSehat Token Error: ' . $response->body());
            return null;
        });
    }

    public function sendRequest($method, $endpoint, $data = null)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['status' => 'failed', 'code' => 401, 'response' => ['message' => 'Cannot obtain access token']];
        }

        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $http = Http::withToken($token)
                        ->timeout(30);

            // PATCH requests use JSON Patch content type
            if (strtoupper($method) === 'PATCH') {
                $http = $http->withHeaders(['Content-Type' => 'application/json-patch+json']);
            }

            switch (strtoupper($method)) {
                case 'GET':
                    $response = $http->get($url, $data ?? []);
                    break;
                case 'POST':
                    $response = $http->post($url, $data ?? []);
                    break;
                case 'PUT':
                    $response = $http->put($url, $data ?? []);
                    break;
                case 'PATCH':
                    $response = $http->patch($url, $data ?? []);
                    break;
                case 'DELETE':
                    $response = $http->delete($url);
                    break;
                default:
                    return ['status' => 'failed', 'code' => 400, 'response' => ['message' => 'Invalid HTTP method']];
            }

            return [
                'status'   => $response->successful() ? 'success' : 'failed',
                'code'     => $response->status(),
                'response' => $response->json() ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('SatuSehat API Error: ' . $e->getMessage());
            return ['status' => 'failed', 'code' => 500, 'response' => ['message' => $e->getMessage()]];
        }
    }

    public function searchPatientByNik($nik)
    {
        $query = ['identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik];
        $response = $this->sendRequest('GET', 'Patient', $query);

        if ($response['status'] == 'success' && !empty($response['response']['entry'])) {
            $entries = $response['response']['entry'];
            $patientResource = null;

            foreach ($entries as $entry) {
                $res = $entry['resource'] ?? [];
                if (isset($res['identifier'])) {
                    foreach ($res['identifier'] as $id) {
                        if (isset($id['system']) && $id['system'] === 'https://fhir.kemkes.go.id/id/ihs-number') {
                            $patientResource = $res;
                            break 2;
                        }
                    }
                }
            }

            if (!$patientResource) {
                $patientResource = $entries[0]['resource'] ?? [];
            }

            if (empty($patientResource)) {
                return ['found' => false];
            }

            return [
                'found'     => true,
                'ihs_id'    => $patientResource['id'] ?? '',
                'nik'       => $nik,
                'name'      => $patientResource['name'][0]['text'] ?? '',
                'gender'    => ($patientResource['gender'] ?? '') == 'male' ? 'L' : 'P',
                'birthDate' => $patientResource['birthDate'] ?? '',
            ];
        }

        return ['found' => false];
    }

    public function searchPractitioner($type, $query)
    {
        if ($type === 'nik') {
            $params = ['identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $query];
        } else {
            $params = ['name' => $query];
        }

        $response = $this->sendRequest('GET', 'Practitioner', $params);

        if ($response['status'] === 'success' && !empty($response['response']['entry'])) {
            $results = [];
            foreach ($response['response']['entry'] as $entry) {
                $resource = $entry['resource'] ?? [];
                $nik = '';
                $ihsId = $resource['id'] ?? '';

                if (isset($resource['identifier'])) {
                    foreach ($resource['identifier'] as $id) {
                        if (isset($id['system']) && $id['system'] === 'https://fhir.kemkes.go.id/id/nik') {
                            $nik = $id['value'] ?? '';
                        }
                    }
                }

                $results[] = [
                    'ihs_id' => $ihsId,
                    'name'   => $resource['name'][0]['text'] ?? '',
                    'nik'    => $nik,
                ];
            }

            return ['found' => true, 'results' => $results];
        }

        return ['found' => false, 'results' => []];
    }
}
