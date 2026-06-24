<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Integration;
use App\Models\Post;
use App\Models\PostCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoContentSeeder extends Seeder
{
    private const IMAGES = [
        'https://images.pexels.com/photos/6064833/pexels-photo-6064833.jpeg',
        'https://images.pexels.com/photos/9228629/pexels-photo-9228629.jpeg',
        'https://images.pexels.com/photos/18896744/pexels-photo-18896744.jpeg',
    ];

    private const LOCATIONS = [
        'Lima',
        'Miraflores',
        'San Isidro',
        'Barranco',
        'Santiago de Surco',
        'San Borja',
        'La Molina',
        'Jesús María',
        'Magdalena del Mar',
        'Pueblo Libre',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedIntegrations();
            $templates = $this->seedCardTemplates();
            $categories = $this->seedCategories();
            $this->seedPosts($categories, $templates);
        });

        $this->command?->info('✓ Contenido demo: 2 integraciones, 2 cards, 3 categorías y 30 posts.');
    }

    private function seedIntegrations(): void
    {
        $integrations = [
            [
                'name' => 'Contactar por WhatsApp',
                'provider' => 'whatsapp',
                'base_url' => 'https://wa.me',
                'button_color' => '#25D366',
                'icon' => Integration::DEFAULT_ICONS['whatsapp'],
                'credentials' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Contactar por Telegram',
                'provider' => 'telegram',
                'base_url' => 'https://t.me',
                'button_color' => '#229ED9',
                'icon' => Integration::DEFAULT_ICONS['telegram'],
                'credentials' => null,
                'is_active' => true,
            ],
        ];

        foreach ($integrations as $integration) {
            Integration::query()->updateOrCreate(
                ['provider' => $integration['provider']],
                $integration,
            );
        }
    }

    /**
     * @return array<int, PostCard>
     */
    private function seedCardTemplates(): array
    {
        $cards = [
            [
                'post_id' => null,
                'title' => 'Perfil',
                'color' => '#E91E63',
                'fill_background' => false,
                'fields' => [
                    ['key' => 'Edad', 'value' => '25 años'],
                    ['key' => 'Idiomas', 'value' => 'Español e inglés'],
                    ['key' => 'Disponibilidad', 'value' => 'Todos los días'],
                ],
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'post_id' => null,
                'title' => 'Atención',
                'color' => '#7C3AED',
                'fill_background' => true,
                'fields' => [
                    ['key' => 'Horario', 'value' => '10:00 a 22:00'],
                    ['key' => 'Modalidad', 'value' => 'Previa coordinación'],
                    ['key' => 'Zona', 'value' => 'Lima Metropolitana'],
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
        ];

        return collect($cards)
            ->map(fn (array $card): PostCard => PostCard::query()->updateOrCreate(
                [
                    'post_id' => null,
                    'title' => $card['title'],
                ],
                $card,
            ))
            ->all();
    }

    /**
     * @return array<int, Category>
     */
    private function seedCategories(): array
    {
        $categories = [
            [
                'name' => 'Acompañantes',
                'slug' => 'acompanantes',
                'description' => 'Perfiles disponibles para experiencias y encuentros coordinados.',
                'image_url' => self::IMAGES[0],
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Masajes',
                'slug' => 'masajes',
                'description' => 'Servicios de masajes y bienestar publicados en distintas zonas.',
                'image_url' => self::IMAGES[1],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Perfiles VIP',
                'slug' => 'perfiles-vip',
                'description' => 'Selección de perfiles destacados con mayor visibilidad.',
                'image_url' => self::IMAGES[2],
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        return collect($categories)
            ->map(fn (array $category): Category => Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            ))
            ->all();
    }

    /**
     * @param array<int, Category> $categories
     * @param array<int, PostCard> $templates
     */
    private function seedPosts(array $categories, array $templates): void
    {
        $sequence = 0;

        foreach ($categories as $categoryIndex => $category) {
            foreach (range(1, 10) as $postNumber) {
                $sequence++;
                $location = self::LOCATIONS[($sequence - 1) % count(self::LOCATIONS)];
                $coverIndex = ($sequence - 1) % count(self::IMAGES);
                $slug = $category->slug.'-demo-'.str_pad((string) $postNumber, 2, '0', STR_PAD_LEFT);
                $phone = '900'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
                $telegramUsername = 'perfil_demo_'.$sequence;

                $post = Post::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'title' => $category->name.' en '.$location.' #'.$postNumber,
                        'subtitle' => 'Atención coordinada, ambiente agradable y disponibilidad flexible.',
                        'location' => $location,
                        'body' => "Conoce este perfil de {$category->name} disponible en {$location}.\n\nContacta directamente para consultar horarios, condiciones y disponibilidad.",
                        'cover_image_url' => self::IMAGES[$coverIndex],
                        'gallery_image_urls' => $this->galleryFor($coverIndex),
                        'whatsapp_country_code' => '51',
                        'whatsapp_number' => $phone,
                        'whatsapp_url' => 'https://wa.me/51'.$phone,
                        'telegram_username' => $telegramUsername,
                        'telegram_url' => 'https://t.me/'.$telegramUsername,
                        'sms_country_code' => null,
                        'sms_number' => null,
                        'sms_url' => null,
                        'tags' => $this->tagsFor($categoryIndex, $postNumber),
                        'is_active' => true,
                        'is_vip' => $category->slug === 'perfiles-vip' || $postNumber <= 2,
                        'published_at' => now()->subHours($sequence),
                        'ends_at' => null,
                    ],
                );

                $this->copyCardsToPost($post, $templates, $location, $postNumber);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function galleryFor(int $coverIndex): array
    {
        return collect(range(1, 4))
            ->map(fn (int $offset): string => self::IMAGES[
                ($coverIndex + $offset) % count(self::IMAGES)
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function tagsFor(int $categoryIndex, int $postNumber): array
    {
        $tags = ['verificado', 'Lima'];

        if ($categoryIndex === 2 || $postNumber <= 2) {
            $tags[] = 'destacado';
        }

        return $tags;
    }

    /**
     * @param array<int, PostCard> $templates
     */
    private function copyCardsToPost(Post $post, array $templates, string $location, int $postNumber): void
    {
        foreach ($templates as $template) {
            $fields = collect($template->fields ?? [])
                ->map(function (array $field) use ($template, $location, $postNumber): array {
                    $value = match ([$template->title, $field['key'] ?? '']) {
                        ['Perfil', 'Edad'] => (23 + ($postNumber % 8)).' años',
                        ['Atención', 'Zona'] => $location,
                        default => (string) ($field['value'] ?? ''),
                    };

                    return [
                        'key' => (string) ($field['key'] ?? ''),
                        'value' => $value,
                    ];
                })
                ->all();

            PostCard::query()->updateOrCreate(
                [
                    'post_id' => $post->id,
                    'title' => $template->title,
                ],
                [
                    'color' => $template->color,
                    'fill_background' => $template->fill_background,
                    'fields' => $fields,
                    'sort_order' => $template->sort_order,
                    'is_active' => true,
                ],
            );
        }
    }
}
