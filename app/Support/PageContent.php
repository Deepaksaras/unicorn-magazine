<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Arr;

/**
 * Read-only view of a blueprint page (config/page_blueprints.php) merged
 * with what the admin saved in page_sections.settings.
 *
 *   $c = PageContent::for('about');
 *   $c->get('hero.overlay');      // saved value or blueprint default
 *   $c->image('hero.image');      // URL
 *   $c->items('perks.items');     // repeater rows
 *   $c->enabled('team');          // section visible?
 */
class PageContent
{
    protected array $data = [];
    protected array $enabled = [];

    public function __construct(
        public readonly string $key,
        public readonly array $blueprint,
        public readonly ?Page $page = null
    ) {
        $saved = $page ? $page->sections->keyBy('section_name') : collect();

        foreach ($blueprint['sections'] as $sectionKey => $section) {
            $row = $saved->get($sectionKey);
            $stored = $row ? (array) ($row->settings ?? []) : [];
            $values = [];

            foreach ($section['fields'] as $fieldKey => $field) {
                $values[$fieldKey] = array_key_exists($fieldKey, $stored)
                    ? $stored[$fieldKey]
                    : ($field['default'] ?? null);
            }

            $this->data[$sectionKey] = $values;
            $this->enabled[$sectionKey] = $row ? (int) $row->status === 1 : true;
        }
    }

    public static function blueprint(string $key): ?array
    {
        return config("page_blueprints.$key");
    }

    public static function for(string $key): self
    {
        $blueprint = static::blueprint($key);

        abort_if(!$blueprint, 404);

        $page = null;

        try {
            $page = Page::with(['sections' => fn ($q) => $q->orderBy('position')])
                ->where('slug', $blueprint['slug'])
                ->first();
        } catch (\Throwable $e) {
            // table missing – fall back to defaults
        }

        return new static($key, $blueprint, $page);
    }

    public function get(string $path, $default = null)
    {
        $value = Arr::get($this->data, $path);

        return ($value === null || $value === '') ? $default : $value;
    }

    public function section(string $section): array
    {
        return $this->data[$section] ?? [];
    }

    public function enabled(string $section): bool
    {
        return $this->enabled[$section] ?? false;
    }

    public function image(string $path, ?string $fallback = null): ?string
    {
        return Media::url($this->get($path), $fallback);
    }

    /**
     * Repeater rows / lines as an array (never null).
     */
    public function items(string $path): array
    {
        $value = $this->get($path, []);

        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value);
        }

        return array_values(array_filter((array) $value, fn ($row) => $row !== null && $row !== '' && $row !== []));
    }

    /**
     * Escaped text with line breaks turned into <br>.
     */
    public function lines(string $path, $default = null): string
    {
        return nl2br(e((string) $this->get($path, $default)), false);
    }

    public function metaTitle(): string
    {
        $site = SiteSettings::get('site_name', 'The Unicorn Magazine');
        $title = $this->page->meta_title ?? null;

        return $title ?: (($this->page->title ?? $this->blueprint['title']) . ' - ' . $site);
    }

    public function metaDescription(): ?string
    {
        return $this->page->meta_description ?? null;
    }

    /**
     * First real sentence of the page's own text – used as the SEO description
     * when the admin left the SEO box empty.
     */
    public function summary(): ?string
    {
        foreach ($this->blueprint['sections'] as $sectionKey => $section) {
            if (!$this->enabled($sectionKey)) {
                continue;
            }
            foreach ($section['fields'] as $fieldKey => $field) {
                if (!in_array($field['type'] ?? '', ['textarea', 'richtext', 'text'], true)) {
                    continue;
                }
                $value = $this->data[$sectionKey][$fieldKey] ?? null;
                if (!is_string($value)) {
                    continue;
                }
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                if (mb_strlen($text) >= 60) {
                    return \Illuminate\Support\Str::limit($text, 160, '…');
                }
            }
        }

        return null;
    }

    public function isPublished(): bool
    {
        return !$this->page || (int) $this->page->status === 1;
    }
}
