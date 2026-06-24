<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        Location::query()->create($data);

        return $this->redirectToSettings('Ubicación agregada correctamente.');
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $this->validatedData($request, $location);
        $previousName = $location->name;

        DB::transaction(function () use ($data, $location, $previousName): void {
            $location->update($data);

            if ($previousName !== $data['name']) {
                Post::query()
                    ->where('location', $previousName)
                    ->update(['location' => $data['name']]);
            }
        });

        return $this->redirectToSettings('Ubicación actualizada correctamente.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if (Post::query()->where('location', $location->name)->exists()) {
            return $this->redirectToSettings('No se puede eliminar una ubicación que está siendo usada por posts.', 'error');
        }

        $location->delete();

        return $this->redirectToSettings('Ubicación eliminada correctamente.');
    }

    /**
     * @return array{name: string, department: string, sort_order: int}
     */
    private function validatedData(Request $request, ?Location $location = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'department' => trim((string) $request->input('department')),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations', 'name')->ignore($location),
                function (string $attribute, mixed $value, \Closure $fail) use ($location): void {
                    $normalizedName = Str::lower(trim((string) $value));
                    $exists = Location::query()
                        ->when($location, fn ($query) => $query->whereKeyNot($location->getKey()))
                        ->get(['name'])
                        ->contains(fn (Location $candidate): bool => Str::lower(trim($candidate->name)) === $normalizedName);

                    if ($exists) {
                        $fail('La ubicación ya existe.');
                    }
                },
            ],
            'department' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'name' => trim($data['name']),
            'department' => trim($data['department']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function redirectToSettings(string $message, string $key = 'status'): RedirectResponse
    {
        $page = max(1, (int) request()->input('locations_page', 1));
        $url = route('settings.edit', ['locations_page' => $page]).'#locations';

        return redirect($url)->with($key, $message);
    }
}
