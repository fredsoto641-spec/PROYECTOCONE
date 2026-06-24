<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $integrations = Integration::query()
            ->orderBy('provider')
            ->paginate(12);

        return view('integrations.index', compact('integrations'));
    }

    public function create(): View
    {
        return view('integrations.create', [
            'providers' => Integration::PROVIDERS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['icon'] = $data['icon'] ?: Integration::DEFAULT_ICONS[$data['provider']] ?? Integration::DEFAULT_ICONS['custom'];

        Integration::create($data);

        return redirect()
            ->route('integrations.index')
            ->with('status', 'Integración creada correctamente.');
    }

    public function edit(Integration $integration): View
    {
        return view('integrations.edit', [
            'integration' => $integration,
            'providers' => Integration::PROVIDERS,
        ]);
    }

    public function update(Request $request, Integration $integration): RedirectResponse
    {
        $data = $this->validatedData($request, $integration);
        $data['icon'] = $data['icon'] ?: Integration::DEFAULT_ICONS[$data['provider']] ?? Integration::DEFAULT_ICONS['custom'];

        $integration->update($data);

        return redirect()
            ->route('integrations.index')
            ->with('status', 'Integración actualizada correctamente.');
    }

    public function destroy(Integration $integration): RedirectResponse
    {
        $integration->delete();

        return redirect()
            ->route('integrations.index')
            ->with('status', 'Integración eliminada correctamente.');
    }

    public function toggleVisibility(Integration $integration): RedirectResponse
    {
        $integration->update([
            'is_active' => ! $integration->is_active,
        ]);

        $status = $integration->is_active
            ? 'Integración publicada correctamente.'
            : 'Integración ocultada correctamente.';

        return redirect()
            ->route('integrations.index')
            ->with('status', $status);
    }

    /**
     * @return array{name: string, provider: string, base_url?: string|null, button_color: string, icon?: string|null, credentials: array<string, mixed>|null, is_active: bool}
     */
    private function validatedData(Request $request, ?Integration $integration = null): array
    {
        $providerRules = [
            'required',
            'string',
            Rule::in(array_keys(Integration::PROVIDERS)),
        ];

        if ($request->input('provider') !== 'custom') {
            $providerRules[] = Rule::unique('integrations', 'provider')->ignore($integration);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => $providerRules,
            'base_url' => ['nullable', 'string', 'max:2048'],
            'button_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', Rule::in(array_keys(Integration::ICON_OPTIONS))],
            'credentials' => ['nullable', 'json'],
        ]);

        $data['credentials'] = isset($data['credentials'])
            ? json_decode($data['credentials'], true)
            : null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
