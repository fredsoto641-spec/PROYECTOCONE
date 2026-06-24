<?php

namespace Tests\Feature;

use App\Models\AgeGateSetting;
use App\Models\Category;
use App\Models\Location;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Support\SecureImageUploader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_upload_is_reencoded_and_overrides_the_url(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('categories.store'), [
                'name' => 'Categoría con archivo',
                'slug' => '',
                'description' => 'Descripción',
                'image_url' => 'https://example.com/original.jpg',
                'image_file' => $this->image('category.png', '<?php echo "payload"; ?>'),
                'sort_order' => 0,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $category = Category::query()->where('name', 'Categoría con archivo')->firstOrFail();
        $this->assertStringContainsString('/storage/categories/', $category->image_url);
        $this->assertStringEndsWith('.webp', $category->image_url);

        $storedPath = Storage::disk('public')->allFiles('categories')[0];
        $this->assertStringNotContainsString('<?php', Storage::disk('public')->get($storedPath));
    }

    public function test_post_gallery_combines_line_separated_urls_and_uploaded_files(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'is_active' => true,
        ]);
        Location::query()->create([
            'name' => 'Miraflores',
            'department' => 'Lima',
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('posts.store'), [
                'category_id' => $category->id,
                'title' => 'Post con imágenes',
                'subtitle' => '',
                'location' => 'Miraflores',
                'body' => 'Contenido',
                'cover_image_url' => 'https://example.com/cover.jpg',
                'cover_image_file' => $this->image('cover.png'),
                'gallery_image_urls' => "https://example.com/one.jpg\nhttps://example.com/two.png",
                'gallery_image_files' => [
                    $this->image('gallery-one.png'),
                    $this->image('gallery-two.png'),
                ],
                'publish_mode' => 'immediate',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $post = Post::query()->where('title', 'Post con imágenes')->firstOrFail();

        $this->assertStringContainsString('/storage/posts/covers/', $post->cover_image_url);
        $this->assertCount(4, $post->gallery_image_urls);
        $this->assertSame('https://example.com/one.jpg', $post->gallery_image_urls[0]);
        $this->assertSame('https://example.com/two.png', $post->gallery_image_urls[1]);
        $this->assertStringContainsString('/storage/posts/gallery/', $post->gallery_image_urls[2]);
        $this->assertStringContainsString('/storage/posts/gallery/', $post->gallery_image_urls[3]);
    }

    public function test_banner_can_be_uploaded_from_site_settings(): void
    {
        Storage::fake('public');
        $site = SiteSetting::DEFAULTS;
        $age = AgeGateSetting::DEFAULTS;

        $this->actingAs($this->admin())
            ->put(route('settings.update'), [
                'settings_section' => 'cover',
                'brand_primary_text' => $site['brand_primary_text'],
                'brand_accent_text' => $site['brand_accent_text'],
                'contact_country' => $site['contact_country'],
                'contact_phone' => $site['contact_phone'],
                'contact_telegram_username' => $site['contact_telegram_username'],
                'site_title' => $site['site_title'],
                'site_subtitle' => $site['site_subtitle'],
                'cover_image_url' => 'https://example.com/banner.jpg',
                'cover_image_file' => $this->image('banner.png'),
                'primary_color' => $site['primary_color'],
                'primary_hover_color' => $site['primary_hover_color'],
                'text_color' => $site['text_color'],
                'muted_color' => $site['muted_color'],
                'background_color' => $site['background_color'],
                'admin_ink_color' => $site['admin_ink_color'],
                'admin_ink_hover_color' => $site['admin_ink_hover_color'],
                'admin_muted_color' => $site['admin_muted_color'],
                'admin_danger_color' => $site['admin_danger_color'],
                'admin_focus_color' => $site['admin_focus_color'],
                'server_country' => $site['server_country'],
                'server_country_code' => $site['server_country_code'],
                'server_utc_offset' => $site['server_utc_offset'],
                'footer_columns' => json_encode($site['footer_columns'], JSON_THROW_ON_ERROR),
                'age_gate_is_enabled' => '1',
                'age_gate_storage_key' => $age['storage_key'],
                'age_gate_badge' => $age['badge'],
                'age_gate_title' => $age['title'],
                'age_gate_description' => $age['description'],
                'age_gate_confirm_label' => $age['confirm_label'],
                'age_gate_exit_label' => $age['exit_label'],
                'age_gate_exit_href' => $age['exit_href'],
                'age_gate_legal_text' => $age['legal_text'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertStringContainsString(
            '/storage/settings/banners/',
            SiteSetting::current()->refresh()->cover_image_url,
        );
    }

    public function test_a_script_renamed_as_an_image_is_rejected(): void
    {
        Storage::fake('public');
        $path = tempnam(sys_get_temp_dir(), 'fake-image-');
        file_put_contents($path, '<?php echo "shell";');

        $this->actingAs($this->admin())
            ->post(route('categories.store'), [
                'name' => 'Categoría insegura',
                'image_file' => new UploadedFile($path, 'shell.jpg', 'image/jpeg', null, true),
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('image_file');

        $this->assertDatabaseMissing('categories', ['name' => 'Categoría insegura']);
    }

    public function test_php_transport_upload_errors_are_explained_in_the_diagnostic_modal(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'oversized-image-');
        file_put_contents($path, '');
        $file = new UploadedFile(
            $path,
            'large-photo.jpg',
            'image/jpeg',
            UPLOAD_ERR_INI_SIZE,
            true,
        );

        $response = $this->actingAs($this->admin())
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => 'Categoría con error',
                'image_file' => $file,
                'sort_order' => 0,
            ]);

        $response
            ->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors('image_file')
            ->assertSessionHas('upload_error_modal', function (array $details): bool {
                return $details['summary'] === 'La imagen supera el tamaño máximo aceptado por PHP.'
                    && $details['technical']['codigo_php'] === UPLOAD_ERR_INI_SIZE
                    && filled($details['technical']['upload_max_filesize']);
            });

        $this->assertDatabaseMissing('categories', ['name' => 'Categoría con error']);
    }

    public function test_image_optimization_preserves_the_original_aspect_ratio_without_padding(): void
    {
        Storage::fake('public');

        $url = app(SecureImageUploader::class)->upload(
            $this->rectangularImage(),
            'aspect-ratio-test',
        );
        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $path = preg_replace('#^storage/#', '', $path);
        $processed = tempnam(sys_get_temp_dir(), 'processed-image-');
        file_put_contents($processed, Storage::disk('public')->get($path));

        [$width, $height] = getimagesize($processed);

        $this->assertSame(320, $width);
        $this->assertSame(180, $height);
        $this->assertSame(16 / 9, $width / $height);
    }

    private function image(string $name, string $suffix = ''): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'valid-image-');
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );
        file_put_contents($path, $png.$suffix);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function rectangularImage(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'rectangular-image-');
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAUAAAAC0EAIAAAA6Fr4FAAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAAGYktHRP///////wlY99wAAAAHdElNRQfqBhISLzgr5w8dAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDI2LTA2LTE4VDE4OjQ3OjU2KzAwOjAwqT0C0wAAACV0RVh0ZGF0ZTptb2RpZnkAMjAyNi0wNi0xOFQxODo0Nzo1NiswMDowMNhgum8AAAAodEVYdGRhdGU6dGltZXN0YW1wADIwMjYtMDYtMThUMTg6NDc6NTYrMDA6MDCPdZuwAAAEKUlEQVR42u3dsbHcMBBEQV7V8sJQcspMyoywlAQhAznMGL87AnosPGCBz79/v379/n0BAEGzn+t7/W1/BgD8LLPXde8/7c8AgJ/FChgACmY/13f7AQNA1FkBS9AAEDV7XbcEDQBZEjQAFMwrQQNA3OzlFDQApM1+rluCBoAsp6ABoMBFHABQMHs5BQ0AabOf65agASDLHDAAFBhDAoACF3EAQIE5YAAoMIYEAAX2gAGg4JyCtgcMAFFnDtgKGACiJGgAKJCgAaDAKWgAKJjXHDAAxJ09YAkaAKIkaAAo8BoSABTMXt4DBoA0CRoACiRoACg4K2AJGgCi5l3mgAEgzR4wABRI0ABQMFuCBoA4CRoACiRoACiY/XzMAQNA2LmK0g8YAKIkaAAomNdVlAAQN3s5BQ0AabOf694SNABEmQMGgAI/YAAomL2urwQNAFmzH3PAAJAmQQNAwbwSNADEWQEDQIE9YAAomO0qSgCIO1dR2gMGgCgrYAAosAcMAAUSNAAUSNAAUDCvOWAAiDt7wBI0AESd15CsgAEgylWUAFBwfsASNABEzV7XLUEDQJYEDQAFZw5YggaAKCtgACiYd7kLGgDSJGgAKJCgAaBgtgQNAHESNAAUSNAAUOAHDAAF5ypKCRoAoua1AgaAuHMIyw8YAKJmL88RAkDa7MccMACkSdAAUHDGkCRoAIg6e8BWwAAQNfv53BI0AGRJ0ABQMK8EDQBxTkEDQMGZA5agASDKChgACowhAUCB94ABoGD24z1gAEizAgaAAnvAAFAw73N9JWgAyPIeMAAUSNAAUHAu4pCgASDKKWgAKDhzwH7AABB19oAlaACIkqABoODMAfsBA0DU7OU9YABIk6ABoODMAfsBA0DUWQFL0AAQdfaArYABIEqCBoACCRoACmYvV1ECQNq8xpAAIE6CBoACh7AAoMAYEgAUSNAAUCBBA0DB7OUUNACkzX48RwgAafNK0AAQ5z1gACiYva7vlqABIOrsAVsBA0CUBA0ABRI0ABRYAQNAgT1gAChwFSUAFMy7Ph5jAIAwK2AAKLAHDAAF5zUkCRoAoiRoACgwBwwABd4DBoCCcxWlFTAAREnQAFAw7+MUNACkzV7XLUEDQJYEDQAFZw5YggaAKCtgACiYvdwFDQBpEjQAFEjQAFAwrwQNAHESNAAUSNAAUOAHDAAF5ypKCRoAoqyAAaDgHMLyAwaAqNnLc4QAkDb7MQcMAGnzStAAEHcOYUnQABB19oCtgAEgavZz3RI0AGRJ0ABQIEEDQIGLOACgwBwwABScOWB7wAAQZQ8YAAq8hgQABWcOWIIGgCgJGgAKJGgAKDhzwBI0AESZAwaAAnvAAFDgIg4AKJj9fKyAASBs9vIeMACkeQ8YAArMAQNAgfeAAaBg9rpuCRoAsiRoACj4D/41DLh2UA9/AAAAAElFTkSuQmCC',
            true,
        );
        file_put_contents($path, $png);

        return new UploadedFile($path, 'rectangular.png', 'image/png', null, true);
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
