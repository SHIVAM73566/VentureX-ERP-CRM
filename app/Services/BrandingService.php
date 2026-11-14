<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;

/**
 * Company branding used by invoices, PDF exports and the app shell.
 *
 * Every value is a per-company setting with a sensible fallback to the
 * company record, so a fresh install still produces a complete invoice and
 * no brand name is ever hard-coded in a Blade template.
 */
class BrandingService
{
    public function __construct(protected SettingService $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $company = CompanyContext::current();
        $defaults = $this->defaults($company);

        $stored = [
            'company_name' => $this->settings->get('branding.company_name'),
            'legal_name' => $this->settings->get('branding.legal_name'),
            'tagline' => $this->settings->get('branding.tagline'),
            'email' => $this->settings->get('branding.email'),
            'phone' => $this->settings->get('branding.phone'),
            'website' => $this->settings->get('branding.website'),
            'address' => $this->settings->get('branding.address'),
            'tax_id' => $this->settings->get('branding.tax_id'),
            'registration_number' => $this->settings->get('branding.registration_number'),
            'logo_path' => $this->settings->get('branding.logo_path'),
            'accent_color' => $this->settings->get('branding.accent_color'),
            'font_family' => $this->settings->get('branding.font_family'),
            'invoice_footer' => $this->settings->get('branding.invoice_footer'),
            'invoice_terms' => $this->settings->get('branding.invoice_terms'),
            'watermark_text' => $this->settings->get('branding.watermark_text'),
            'show_watermark' => $this->settings->get('branding.show_watermark'),
        ];

        $branding = [];
        foreach ($defaults as $key => $value) {
            $branding[$key] = filled($stored[$key] ?? null) ? $stored[$key] : $value;
        }

        $branding['show_watermark'] = filter_var(
            $branding['show_watermark'] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? true;

        $branding['logo_url'] = $this->logoUrl($branding['logo_path']);
        $branding['logo_data_uri'] = $this->logoDataUri($branding['logo_path']);
        $branding['accent_color'] = $this->safeColor((string) $branding['accent_color']);
        $branding['font_family'] = $this->safeFont((string) $branding['font_family']);

        return $branding;
    }

    /**
     * @return array<string, string|bool|null>
     */
    protected function defaults(?Company $company): array
    {
        $address = collect([
            $company?->address_line1,
            $company?->address_line2,
            $company?->city,
            $company?->state,
            $company?->postal_code,
        ])->filter()->implode(', ');

        return [
            'company_name' => $company?->name ?: config('app.name', 'VentureX ERP & CRM'),
            'legal_name' => $company?->legal_name ?: ($company?->name ?: config('app.name', 'VentureX ERP & CRM')),
            'tagline' => 'Business Operating System',
            'email' => $company?->email ?: '',
            'phone' => $company?->phone ?: '',
            'website' => '',
            'address' => $address,
            'tax_id' => $company?->tax_id ?: '',
            'registration_number' => $company?->registration_number ?: '',
            'logo_path' => null,
            'accent_color' => '#1a365d',
            'font_family' => 'Helvetica, Arial, sans-serif',
            'invoice_footer' => 'Thank you for your business.',
            'invoice_terms' => 'Payment is due within the agreed credit period. Please quote the invoice number with your remittance.',
            'watermark_text' => 'ORIGINAL',
            'show_watermark' => true,
        ];
    }

    public function logoUrl(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function logoDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';
        $contents = Storage::disk('public')->get($path);

        if ($contents === null || $contents === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    protected function safeColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : '#1a365d';
    }

    protected function safeFont(string $font): string
    {
        $allowed = [
            'Helvetica, Arial, sans-serif',
            'Georgia, Times New Roman, serif',
            'Courier New, monospace',
            'DejaVu Sans, sans-serif',
            'DejaVu Serif, serif',
        ];

        return in_array($font, $allowed, true) ? $font : $allowed[0];
    }
}
