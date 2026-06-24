<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Integration;
use App\Models\Location;
use App\Models\Post;
use App\Models\PostCard;
use App\Models\SiteSetting;
use App\Support\SecureImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PostController extends Controller
{
    public function __construct(private readonly SecureImageUploader $imageUploader)
    {
    }

    public function index(): View
    {
        $posts = Post::query()
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('posts.create', [
            'categories' => $this->categoriesForSelect(),
            'integrations' => $this->activeIntegrations(),
            'locations' => $this->locationsForSelect(),
            'postCardColorSuggestions' => $this->postCardColorSuggestions(),
            'cardTemplates' => $this->cardTemplates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $cards = $this->validatedCards($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $uploadedUrls = [];

        try {
            if ($request->hasFile('cover_image_file')) {
                $data['cover_image_url'] = $this->imageUploader->upload(
                    $request->file('cover_image_file'),
                    'posts/covers',
                    'cover_image_file',
                );
                $uploadedUrls[] = $data['cover_image_url'];
            }

            $galleryUploads = $this->imageUploader->uploadMany(
                $request->file('gallery_image_files', []),
                'posts/gallery',
                'gallery_image_files',
            );
            $uploadedUrls = array_merge($uploadedUrls, $galleryUploads);
            $data['gallery_image_urls'] = array_merge($data['gallery_image_urls'], $galleryUploads);

            DB::transaction(function () use ($data, $cards): void {
                $post = Post::create($data);
                $this->syncCards($post, $cards);
            });
        } catch (Throwable $exception) {
            foreach ($uploadedUrls as $url) {
                $this->imageUploader->deleteManagedUrl($url);
            }

            throw $exception;
        }

        return redirect()
            ->route('posts.index')
            ->with('status', 'Post creado correctamente.');
    }

    public function edit(Post $post): View
    {
        $post->load('cards');

        return view('posts.edit', [
            'categories' => $this->categoriesForSelect(),
            'integrations' => $this->activeIntegrations(),
            'locations' => $this->locationsForSelect(),
            'post' => $post,
            'postCardColorSuggestions' => $this->postCardColorSuggestions(),
            'cardTemplates' => $this->cardTemplates(),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validatedData($request);
        $cards = $this->validatedCards($request);
        $oldCoverUrl = $post->cover_image_url;
        $oldGalleryUrls = $post->gallery_image_urls ?? [];
        $uploadedUrls = [];

        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post);
        }

        try {
            if ($request->hasFile('cover_image_file')) {
                $data['cover_image_url'] = $this->imageUploader->upload(
                    $request->file('cover_image_file'),
                    'posts/covers',
                    'cover_image_file',
                );
                $uploadedUrls[] = $data['cover_image_url'];
            }

            $galleryUploads = $this->imageUploader->uploadMany(
                $request->file('gallery_image_files', []),
                'posts/gallery',
                'gallery_image_files',
            );
            $uploadedUrls = array_merge($uploadedUrls, $galleryUploads);
            $data['gallery_image_urls'] = array_merge($data['gallery_image_urls'], $galleryUploads);

            DB::transaction(function () use ($post, $data, $cards): void {
                $post->update($data);
                $this->syncCards($post, $cards);
            });
        } catch (Throwable $exception) {
            foreach ($uploadedUrls as $url) {
                $this->imageUploader->deleteManagedUrl($url);
            }

            throw $exception;
        }

        if ($post->cover_image_url !== $oldCoverUrl) {
            $this->imageUploader->deleteManagedUrl($oldCoverUrl);
        }

        foreach (array_diff($oldGalleryUrls, $post->gallery_image_urls ?? []) as $removedUrl) {
            $this->imageUploader->deleteManagedUrl($removedUrl);
        }

        return redirect()
            ->route('posts.index')
            ->with('status', 'Post actualizado correctamente.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $imageUrls = array_filter([
            $post->cover_image_url,
            ...($post->gallery_image_urls ?? []),
        ]);

        $post->delete();

        foreach ($imageUrls as $imageUrl) {
            $this->imageUploader->deleteManagedUrl($imageUrl);
        }

        return redirect()
            ->route('posts.index')
            ->with('status', 'Post eliminado correctamente.');
    }

    public function toggleVisibility(Post $post): RedirectResponse
    {
        $post->update([
            'is_active' => ! $post->is_active,
        ]);

        $status = $post->is_active
            ? 'Post publicado correctamente.'
            : 'Post ocultado correctamente.';

        return redirect()
            ->route('posts.index')
            ->with('status', $status);
    }

    public function toggleVip(Post $post): RedirectResponse
    {
        $post->update([
            'is_vip' => ! $post->is_vip,
        ]);

        $status = $post->is_vip
            ? 'Post marcado como VIP correctamente.'
            : 'Post removido de VIP correctamente.';

        return redirect()
            ->route('posts.index')
            ->with('status', $status);
    }

    /**
     * @return array{
     *     category_id: int,
     *     title: string,
     *     slug?: string|null,
     *     subtitle?: string|null,
     *     location: string,
     *     body: string,
     *     cover_image_url?: string|null,
     *     gallery_image_urls: array<int, string>,
     *     whatsapp_url?: string|null,
     *     telegram_url?: string|null,
     *     sms_url?: string|null,
     *     tags: array<int, string>
     * }
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255', Rule::exists('locations', 'name')],
            'body' => ['required', 'string'],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'cover_image_file' => $this->imageUploader->validationRules(),
            'gallery_image_urls' => ['nullable', 'string'],
            'gallery_image_files' => ['nullable', 'array', 'max:12'],
            'gallery_image_files.*' => $this->imageUploader->validationRules(),
            'whatsapp_country_code' => ['nullable', 'string', 'max:8'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'telegram_username' => ['nullable', 'string', 'max:64'],
            'sms_country_code' => ['nullable', 'string', 'max:8'],
            'sms_number' => ['nullable', 'string', 'max:32'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'publish_mode' => ['required', Rule::in(['immediate', 'scheduled'])],
            'published_at' => ['nullable', 'date', 'required_if:publish_mode,scheduled'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $this->validatePublicationWindow($data);

        $data['location'] = trim($data['location']);
        $data['gallery_image_urls'] = $this->validatedImageUrls($data['gallery_image_urls'] ?? null);
        $data['tags'] = $this->tagsToArray($data['tags'] ?? null);
        $data = array_merge($data, $this->buildContactUrls($data));
        $data['is_active'] = $request->boolean('is_active');
        $data['published_at'] = $data['publish_mode'] === 'scheduled'
            ? $data['published_at']
            : now();

        unset($data['publish_mode'], $data['cover_image_file'], $data['gallery_image_files']);

        return $data;
    }

    private function linesToArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function validatedImageUrls(?string $value): array
    {
        $urls = $this->linesToArray($value);
        $validator = Validator::make(
            ['urls' => $urls],
            [
                'urls.*' => ['url:http,https', 'max:2048'],
            ],
            [],
            ['urls.*' => 'URL de galería'],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'gallery_image_urls' => $validator->errors()->first(),
            ]);
        }

        return $urls;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validatePublicationWindow(array $data): void
    {
        if (empty($data['ends_at'])) {
            return;
        }

        $endsAt = Carbon::parse($data['ends_at']);

        if ($data['publish_mode'] === 'immediate' && $endsAt->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'ends_at' => 'La fecha de finalización debe ser posterior al momento actual.',
            ]);
        }

        if (
            $data['publish_mode'] === 'scheduled'
            && ! empty($data['published_at'])
            && $endsAt->lessThanOrEqualTo(Carbon::parse($data['published_at']))
        ) {
            throw ValidationException::withMessages([
                'ends_at' => 'La fecha de finalización debe ser posterior a la fecha de publicación.',
            ]);
        }
    }

    private function tagsToArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/,|\r\n|\r|\n/', $value))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{title: string, color: string, fill_background: bool, fields: array<int, array{key: string, value: string}>, is_active: bool}>
     */
    private function validatedCards(Request $request): array
    {
        $data = $request->validate([
            'post_cards' => ['nullable', 'array'],
            'post_cards.*.title' => ['nullable', 'string', 'max:120'],
            'post_cards.*.color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'post_cards.*.fill_background' => ['nullable', 'boolean'],
            'post_cards.*.is_active' => ['nullable', 'boolean'],
            'post_cards.*.fields' => ['nullable', 'array'],
            'post_cards.*.fields.*.key' => ['nullable', 'string', 'max:80'],
            'post_cards.*.fields.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        return collect($data['post_cards'] ?? [])
            ->map(function (array $card): array {
                $fields = collect($card['fields'] ?? [])
                    ->map(fn (array $field): array => [
                        'key' => trim((string) ($field['key'] ?? '')),
                        'value' => trim((string) ($field['value'] ?? '')),
                    ])
                    ->filter(fn (array $field): bool => $field['key'] !== '' && $field['value'] !== '')
                    ->values()
                    ->all();

                return [
                    'title' => trim((string) ($card['title'] ?? '')),
                    'color' => $card['color'] ?? '#E91E63',
                    'fill_background' => filter_var($card['fill_background'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'fields' => $fields,
                    'is_active' => filter_var($card['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(fn (array $card): bool => $card['title'] !== '' && count($card['fields']) > 0)
            ->values()
            ->all();
    }

    /**
     * @param array<int, array{title: string, color: string, fill_background: bool, fields: array<int, array{key: string, value: string}>, is_active: bool}> $cards
     */
    private function syncCards(Post $post, array $cards): void
    {
        $post->cards()->delete();

        foreach ($cards as $index => $card) {
            $post->cards()->create([
                'title' => $card['title'],
                'color' => $card['color'],
                'fill_background' => $card['fill_background'],
                'fields' => $card['fields'],
                'sort_order' => $index,
                'is_active' => $card['is_active'],
            ]);
        }
    }

    /**
     * @return array{byTitle: array<string, string>, colors: array<int, string>}
     */
    private function postCardColorSuggestions(): array
    {
        $cards = PostCard::query()
            ->whereNotNull('color')
            ->latest()
            ->get(['title', 'color']);

        return [
            'byTitle' => $cards
                ->filter(fn (PostCard $card): bool => filled($card->title) && filled($card->color))
                ->unique(fn (PostCard $card): string => mb_strtolower($card->title))
                ->mapWithKeys(fn (PostCard $card): array => [mb_strtolower($card->title) => $card->color])
                ->all(),
            'colors' => $cards
                ->pluck('color')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function categoriesForSelect()
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function locationsForSelect()
    {
        return Location::query()
            ->orderBy('department')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'department']);
    }

    private function activeIntegrations()
    {
        return Integration::query()
            ->where('is_active', true)
            ->get()
            ->keyBy('provider');
    }

    private function cardTemplates()
    {
        return PostCard::query()
            ->whereNull('post_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'color', 'fill_background', 'fields']);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{whatsapp_url: string|null, telegram_url: string|null, sms_url: string|null}
     */
    private function buildContactUrls(array $data): array
    {
        $integrations = $this->activeIntegrations();

        return [
            'whatsapp_url' => $this->buildWhatsAppUrl($integrations->get('whatsapp'), $data),
            'telegram_url' => $this->buildTelegramUrl($integrations->get('telegram'), $data),
            'sms_url' => $this->buildSmsUrl($integrations->get('sms'), $data),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildWhatsAppUrl(?Integration $integration, array $data): ?string
    {
        if (! $integration || empty($data['whatsapp_number'])) {
            return null;
        }

        $baseUrl = rtrim($integration->base_url ?: 'https://wa.me', '/');
        $phone = $this->digitsOnly($this->contactCountryCode($data['whatsapp_country_code'] ?? null).($data['whatsapp_number'] ?? ''));

        return $phone !== '' ? "{$baseUrl}/{$phone}" : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildTelegramUrl(?Integration $integration, array $data): ?string
    {
        if (! $integration || empty($data['telegram_username'])) {
            return null;
        }

        $baseUrl = rtrim($integration->base_url ?: 'https://t.me', '/');
        $username = ltrim((string) $data['telegram_username'], '@');

        return $username !== '' ? "{$baseUrl}/{$username}" : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildSmsUrl(?Integration $integration, array $data): ?string
    {
        if (! $integration || empty($data['sms_number'])) {
            return null;
        }

        $baseUrl = rtrim($integration->base_url ?: 'sms:', '/');
        $phone = $this->digitsOnly($this->contactCountryCode($data['sms_country_code'] ?? null).($data['sms_number'] ?? ''));

        return $phone !== '' ? "{$baseUrl}+{$phone}" : null;
    }

    private function contactCountryCode(?string $value): string
    {
        $provided = $this->digitsOnly((string) $value);

        if ($provided !== '') {
            return $provided;
        }

        $settings = SiteSetting::current();

        return SiteSetting::SERVER_COUNTRIES[$settings->server_country]['dial_code'] ?? '51';
    }

    private function digitsOnly(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function uniqueSlug(string $title, ?Post $post = null): string
    {
        $base = Str::slug($title) ?: 'post';

        do {
            $slug = $base.'-'.random_int(1000, 9999);
            $exists = Post::query()
                ->where('slug', $slug)
                ->when($post, fn ($query) => $query->whereKeyNot($post->getKey()))
                ->exists();
        } while ($exists);

        return $slug;
    }
}
