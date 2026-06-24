<?php

namespace App\Http\Controllers;

use App\Models\AgeGateSetting;
use App\Models\Location;
use App\Models\SiteSetting;
use App\Support\SecureImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SiteSettingController extends Controller
{
    public function __construct(private readonly SecureImageUploader $imageUploader)
    {
    }

    public function edit(): View
    {
        return view('settings.edit', [
            'ageGateSettings' => AgeGateSetting::current(),
            'locations' => Location::query()
                ->orderBy('department')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15, ['*'], 'locations_page')
                ->withQueryString()
                ->fragment('locations'),
            'serverCountries' => SiteSetting::SERVER_COUNTRIES,
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = SiteSetting::current();
        $ageGateSettings = AgeGateSetting::current();
        $data = $this->validatedData($request);
        $oldCoverUrl = $settings->cover_image_url;
        $uploadedUrl = null;

        try {
            if ($request->hasFile('cover_image_file')) {
                $uploadedUrl = $this->imageUploader->upload(
                    $request->file('cover_image_file'),
                    'settings/banners',
                    'cover_image_file',
                );
                $data['site']['cover_image_url'] = $uploadedUrl;
            }

            DB::transaction(function () use ($settings, $ageGateSettings, $data): void {
                $settings->update($data['site']);
                $ageGateSettings->update($data['age_gate']);
            });
        } catch (Throwable $exception) {
            $this->imageUploader->deleteManagedUrl($uploadedUrl);

            throw $exception;
        }

        if ($settings->cover_image_url !== $oldCoverUrl) {
            $this->imageUploader->deleteManagedUrl($oldCoverUrl);
        }

        $section = in_array($request->input('settings_section'), ['cover', 'colors', 'server', 'age', 'footer'], true)
            ? $request->input('settings_section')
            : 'cover';

        return redirect(route('settings.edit').'#'.$section)
            ->with('status', 'Configuración actualizada correctamente.');
    }

    /**
     * @return array{site: array<string, mixed>, age_gate: array<string, bool|string>}
     */
    private function validatedData(Request $request): array
    {
        $hex = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        $data = $request->validate([
            'brand_primary_text' => ['required', 'string', 'max:80'],
            'brand_accent_text' => ['required', 'string', 'max:80'],
            'contact_country' => ['required', 'string', Rule::in(array_keys(SiteSetting::SERVER_COUNTRIES))],
            'contact_phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9\s()+.-]+$/'],
            'contact_telegram_username' => ['nullable', 'string', 'max:64', 'regex:/^@?[A-Za-z0-9_]+$/'],
            'site_title' => ['required', 'string', 'max:255'],
            'site_subtitle' => ['required', 'string', 'max:500'],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'cover_image_file' => $this->imageUploader->validationRules(),
            'primary_color' => $hex,
            'primary_hover_color' => $hex,
            'text_color' => $hex,
            'muted_color' => $hex,
            'background_color' => $hex,
            'admin_ink_color' => $hex,
            'admin_ink_hover_color' => $hex,
            'admin_muted_color' => $hex,
            'admin_danger_color' => $hex,
            'admin_focus_color' => $hex,
            'server_country' => ['required', 'string', 'max:255'],
            'server_country_code' => ['required', 'string', 'max:8'],
            'server_utc_offset' => ['required', 'regex:/^[+-](0\d|1[0-4]):[0-5]\d$/'],
            'footer_columns' => ['required', 'json', 'max:50000'],
            'age_gate_storage_key' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_.:-]+$/'],
            'age_gate_badge' => ['required', 'string', 'max:24'],
            'age_gate_title' => ['required', 'string', 'max:255'],
            'age_gate_description' => ['required', 'string', 'max:1000'],
            'age_gate_confirm_label' => ['required', 'string', 'max:80'],
            'age_gate_exit_label' => ['required', 'string', 'max:80'],
            'age_gate_exit_href' => ['required', 'url', 'max:2048'],
            'age_gate_legal_text' => ['required', 'string', 'max:1000'],
        ]);

        $footerColumns = json_decode($data['footer_columns'], true);

        $footerValidator = Validator::make(
            ['footer_columns' => $footerColumns],
            [
                'footer_columns' => ['required', 'array', 'min:1', 'max:8'],
                'footer_columns.*.title' => ['required', 'string', 'max:80', 'distinct:ignore_case'],
                'footer_columns.*.items' => ['required', 'array', 'min:1', 'max:12'],
                'footer_columns.*.items.*.label' => ['required', 'string', 'max:120'],
                'footer_columns.*.items.*.href' => [
                    'required',
                    'string',
                    'max:2048',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $href = trim((string) $value);

                        if (
                            ! str_starts_with($href, '/')
                            && ! str_starts_with($href, '#')
                            && ! preg_match('/^(https?:|mailto:|tel:|sms:)/i', $href)
                        ) {
                            $fail('El enlace debe ser una ruta interna, un ancla o una URL válida.');
                        }
                    },
                ],
            ],
            [],
            [
                'footer_columns.*.title' => 'título de columna',
                'footer_columns.*.items' => 'elementos de columna',
                'footer_columns.*.items.*.label' => 'texto del enlace',
                'footer_columns.*.items.*.href' => 'destino del enlace',
            ],
        );

        if ($footerValidator->fails()) {
            throw ValidationException::withMessages([
                'footer_columns' => $footerValidator->errors()->first(),
            ]);
        }

        $data['footer_columns'] = collect($footerColumns)
            ->map(fn (array $column): array => [
                'title' => trim($column['title']),
                'items' => collect($column['items'])
                    ->map(fn (array $item): array => [
                        'label' => trim($item['label']),
                        'href' => trim($item['href']),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        $data['contact_phone'] = filled($data['contact_phone'] ?? null)
            ? preg_replace('/\D+/', '', (string) $data['contact_phone'])
            : null;
        $data['contact_telegram_username'] = filled($data['contact_telegram_username'] ?? null)
            ? ltrim(trim((string) $data['contact_telegram_username']), '@')
            : null;

        return [
            'site' => collect($data)
                ->except([
                    'age_gate_storage_key',
                    'age_gate_badge',
                    'age_gate_title',
                    'age_gate_description',
                    'age_gate_confirm_label',
                    'age_gate_exit_label',
                    'age_gate_exit_href',
                    'age_gate_legal_text',
                    'cover_image_file',
                ])
                ->all(),
            'age_gate' => [
                'is_enabled' => $request->boolean('age_gate_is_enabled'),
                'storage_key' => $data['age_gate_storage_key'],
                'badge' => $data['age_gate_badge'],
                'title' => $data['age_gate_title'],
                'description' => $data['age_gate_description'],
                'confirm_label' => $data['age_gate_confirm_label'],
                'exit_label' => $data['age_gate_exit_label'],
                'exit_href' => $data['age_gate_exit_href'],
                'legal_text' => $data['age_gate_legal_text'],
            ],
        ];
    }

}
