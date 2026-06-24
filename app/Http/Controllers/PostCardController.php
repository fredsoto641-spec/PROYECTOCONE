<?php

namespace App\Http\Controllers;

use App\Models\PostCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostCardController extends Controller
{
    public function index(): View
    {
        $postCards = PostCard::query()
            ->whereNull('post_id')
            ->orderBy('title')
            ->paginate(12);

        return view('post-cards.index', compact('postCards'));
    }

    public function create(): View
    {
        return view('post-cards.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['post_id'] = null; // Template cards don't belong to a post

        PostCard::create($data);

        return redirect()
            ->route('post-cards.index')
            ->with('status', 'Card creada correctamente.');
    }

    public function edit(PostCard $postCard): View
    {
        // Only allow editing template cards (not associated with a post)
        if ($postCard->post_id !== null) {
            abort(403, 'No puedes editar cards asociadas a posts.');
        }

        return view('post-cards.edit', compact('postCard'));
    }

    public function update(Request $request, PostCard $postCard): RedirectResponse
    {
        // Only allow editing template cards
        if ($postCard->post_id !== null) {
            abort(403, 'No puedes editar cards asociadas a posts.');
        }

        $previousTitle = $postCard->title;
        $previousColor = $postCard->color;
        $data = $this->validatedData($request);

        $postCard->update($data);
        $this->syncCopiedCardPresentation($previousTitle, $previousColor, $data);

        return redirect()
            ->route('post-cards.index')
            ->with('status', 'Card actualizada correctamente.');
    }

    public function destroy(PostCard $postCard): RedirectResponse
    {
        // Only allow deleting template cards
        if ($postCard->post_id !== null) {
            abort(403, 'No puedes eliminar cards asociadas a posts.');
        }

        $postCard->delete();

        return redirect()
            ->route('post-cards.index')
            ->with('status', 'Card eliminada correctamente.');
    }

    public function toggleVisibility(PostCard $postCard): RedirectResponse
    {
        // Only allow toggling template cards
        if ($postCard->post_id !== null) {
            abort(403, 'No puedes modificar cards asociadas a posts.');
        }

        $postCard->update([
            'is_active' => ! $postCard->is_active,
        ]);

        $status = $postCard->is_active
            ? 'Card activada correctamente.'
            : 'Card desactivada correctamente.';

        return redirect()
            ->route('post-cards.index')
            ->with('status', $status);
    }

    /**
     * @return array{title: string, color: string, fill_background: bool, fields: array<int, array{key: string, value: string}>, is_active: bool, sort_order: int}
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'fill_background' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'fields' => ['nullable', 'array'],
            'fields.*.key' => ['nullable', 'string', 'max:80'],
            'fields.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $fields = collect($data['fields'] ?? [])
            ->map(fn (array $field): array => [
                'key' => trim((string) ($field['key'] ?? '')),
                'value' => trim((string) ($field['value'] ?? '')),
            ])
            ->filter(fn (array $field): bool => $field['key'] !== '')
            ->values()
            ->all();

        return [
            'title' => trim($data['title']),
            'color' => $data['color'],
            'fill_background' => filter_var($data['fill_background'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'fields' => $fields,
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $data['sort_order'],
        ];
    }

    /**
     * @param array{title: string, color: string, fill_background: bool, fields: array<int, array{key: string, value: string}>, is_active: bool, sort_order: int} $data
     */
    private function syncCopiedCardPresentation(string $previousTitle, string $previousColor, array $data): void
    {
        PostCard::query()
            ->whereNotNull('post_id')
            ->where('title', $previousTitle)
            ->where('color', $previousColor)
            ->update([
                'color' => $data['color'],
                'fill_background' => $data['fill_background'],
            ]);
    }
}
