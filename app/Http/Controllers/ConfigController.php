<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\CompanyApp;
use Exception;
use Illuminate\Http\Request;

/**
 * @tags Configurations
 */
class ConfigController extends Controller
{
    /**
     * Get App Configuration
     *
     * This endpoint returns company-specific configuration based on the provided `company_id` and the API key in the request header.
     */
    public function getConfig(Request $request)
    {
        try {
            $companyId = $request->input('company_id');
            $apiKey = $request->header('X-API-KEY');

            $hashedKey = hash('sha256', $apiKey);

            $apiKeyRecord = ApiKey::where('key_hash', $hashedKey)
                ->where('active', true)
                ->first();

            if (! $apiKeyRecord) {
                return response()->json(['message' => 'Invalid API key'], 401);
            }

            if (! $companyId) {
                return response()->json(['message' => 'company_id is required'], 400);
            }

            $company = Company::where('company_id', $companyId)->first();

            if (! $company) {
                    return response()->json(['message' => 'company id not found in our records. Contact info@kinetics.co.ke to get onboarded'], 400);
                }
            $apiKeyRecord->update(['last_used_at' => now()]);
            $appId = $apiKeyRecord->id;
            $app = CompanyApp::where('app_id', $appId)
                ->where('company_id', $company->id)
                ->first();

            if (! $company) {
                return response()->json(['message' => 'Company Configuration not found'], 404);
            }

            return response()->json([
                'company_id' => $company->company_id,
                'app_name' => $app->app->app_name,
                'branding' => [
                    'company_name' => $company->name,
                    'logo_url' => $app->branding['logo_url'] ?? null,
                    'default_theme_mode' => $app->branding['default_theme_mode'] ?? null,
                ],
                'colors_light_mode' => [
                    'primary_color' => $app->branding['primary_color_light'] ?? null,
                    'secondary_color' => $app->branding['secondary_color_light'] ?? null,
                    'surface_color' => $app->branding['surface_color_light'] ?? null,
                    'background_color' => $app->branding['background_color_light'] ?? null,
                    'text_color' => $app->branding['text_color_light'] ?? null,
                ],
                'colors_dark_mode' => [
                    'primary_color' => $app->branding['primary_color_dark'] ?? null,
                    'secondary_color' => $app->branding['secondary_color_dark'] ?? null,
                    'surface_color' => $app->branding['surface_color_dark'] ?? null,
                    'background_color' => $app->branding['background_color_dark'] ?? null,
                    'text_color' => $app->branding['text_color_dark'] ?? null,
                ],
                'api' => [
                    'base_url' => $app->api_config['endpoint'] ?? null,
                ],
        ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch company configurations',
                'debug' => 'Exception: '.$e->getMessage(),
            ]);
        }
    }
}
