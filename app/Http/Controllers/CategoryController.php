<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\SecureImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    public function __construct(private readonly SecureImageUploader $imageUploader)
    {
    }

    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $uploadedUrl = null;

        try {
            if ($request->hasFile('image_file')) {
                $uploadedUrl = $this->imageUploader->upload($request->file('image_file'), 'categories', 'image_file');
                $data['image_url'] = $uploadedUrl;
            }

            Category::create($data);
        } catch (Throwable $exception) {
            $this->imageUploader->deleteManagedUrl($uploadedUrl);

            throw $exception;
        }

        return redirect()
            ->route('categories.index')
            ->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validatedData($request, $category);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $category);
        $data['is_active'] = $request->boolean('is_active');
        $oldImageUrl = $category->image_url;
        $uploadedUrl = null;

        try {
            if ($request->hasFile('image_file')) {
                $uploadedUrl = $this->imageUploader->upload($request->file('image_file'), 'categories', 'image_file');
                $data['image_url'] = $uploadedUrl;
            }

            $category->update($data);
        } catch (Throwable $exception) {
            $this->imageUploader->deleteManagedUrl($uploadedUrl);

            throw $exception;
        }

        if ($category->image_url !== $oldImageUrl) {
            $this->imageUploader->deleteManagedUrl($oldImageUrl);
        }

        return redirect()
            ->route('categories.index')
            ->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $imageUrls = collect([$category->image_url])
            ->merge(
                $category->posts()
                    ->get(['cover_image_url', 'gallery_image_urls'])
                    ->flatMap(fn ($post) => [$post->cover_image_url, ...($post->gallery_image_urls ?? [])]),
            )
            ->filter()
            ->all();

        $category->delete();

        foreach ($imageUrls as $imageUrl) {
            $this->imageUploader->deleteManagedUrl($imageUrl);
        }

        return redirect()
            ->route('categories.index')
            ->with('status', 'Categoría eliminada correctamente.');
    }

    public function toggleVisibility(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        $status = $category->is_active
            ? 'Categoría publicada correctamente.'
            : 'Categoría ocultada correctamente.';

        return redirect()
            ->route('categories.index')
            ->with('status', $status);
    }

    /**
     * @return array{name: string, slug?: string|null, description?: string|null, image_url?: string|null, sort_order: int}
     */
    private function validatedData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'image_file' => $this->imageUploader->validationRules(),
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        unset($data['image_file']);

        return $data;
    }

    private function uniqueSlug(string $value, ?Category $category = null): string
    {
        $slug = Str::slug($value);
        $slug = $slug !== '' ? $slug : Str::random(8);

        $baseSlug = $slug;
        $counter = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($category, fn ($query) => $query->whereKeyNot($category->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
