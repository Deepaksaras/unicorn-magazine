<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesSeo;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\PageSection;
use App\Support\ImageUploader;
use App\Support\PageContent;
use Illuminate\Http\Request;

/**
 * Edits the designed pages defined in config/page_blueprints.php.
 * Each blueprint section is stored as one page_sections row whose
 * `settings` JSON holds the field values; `status` shows/hides it.
 */
class PageController extends Controller
{
    use SavesSeo;

    public function index()
    {
        $blueprints = config('page_blueprints');
        $pages = Page::whereIn('slug', collect($blueprints)->pluck('slug'))->get()->keyBy('slug');

        return view('admin.pages.index', compact('blueprints', 'pages'));
    }

    public function edit(string $key)
    {
        $blueprint = PageContent::blueprint($key);
        abort_unless($blueprint, 404);

        $content = PageContent::for($key);
        $categories = Category::where('status', 1)->orderBy('name')->pluck('name', 'slug');

        return view('admin.pages.edit', compact('key', 'blueprint', 'content', 'categories'));
    }

    public function update(Request $request, string $key)
    {
        $blueprint = PageContent::blueprint($key);
        abort_unless($blueprint, 404);

        $request->validate([
            'title' => 'required|string|max:191',
            'status' => 'required|in:0,1',
            'sections' => 'array',
        ] + $this->seoRules());

        $page = Page::firstOrNew(['slug' => $blueprint['slug']]);
        $page->fill([
            'title' => $request->input('title'),
            'template' => $key,
            'status' => (int) $request->input('status'),
        ]);
        $page->user_id = $page->user_id ?: auth()->id();
        $page->published_at = $page->published_at ?: now();
        $page->save();
        $this->saveSeo($request, $page);   // SEO box (title, description, keywords, share image…)

        $existing = $page->sections()->get()->keyBy('section_name');
        $position = 0;

        foreach ($blueprint['sections'] as $sectionKey => $section) {
            $input = (array) $request->input("sections.$sectionKey", []);
            $files = (array) ($request->file("sections.$sectionKey") ?? []);
            $row = $existing->get($sectionKey);
            $old = $row ? (array) $row->settings : [];

            $values = [];
            foreach ($section['fields'] as $fieldKey => $field) {
                $values[$fieldKey] = $this->fieldValue(
                    $field,
                    $input[$fieldKey] ?? null,
                    $files,
                    $fieldKey,
                    $input,
                    $old[$fieldKey] ?? null,
                    "pages/{$key}"
                );
            }

            PageSection::updateOrCreate(
                ['page_id' => $page->id, 'section_name' => $sectionKey],
                [
                    'settings' => $values,
                    'layout' => $key,
                    'position' => $position++,
                    'status' => $request->boolean("enabled.$sectionKey") ? 1 : 0,
                ]
            );
        }

        return redirect()->route('admin.pages.edit', $key)->with('success', $blueprint['title'] . ' saved.');
    }

    /**
     * Convert posted input into the stored value for one field.
     */
    protected function fieldValue(array $field, $value, array $files, string $fieldKey, array $input, $old, string $dir)
    {
        switch ($field['type']) {
            case 'image':
                $file = $files[$fieldKey . '_file'] ?? null;
                if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()
                    && str_starts_with((string) $file->getMimeType(), 'image/') && $file->getSize() <= 5 * 1024 * 1024) {
                    return ImageUploader::store($file, $dir);
                }
                if (!empty($input[$fieldKey . '_remove'])) {
                    return null;
                }

                return is_string($value) ? trim($value) : $old;

            case 'lines':
                return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $value))));

            case 'number':
                return is_numeric($value) ? $value + 0 : null;

            case 'toggle':
                return (bool) $value;

            case 'repeater':
                $rows = [];
                $fileRows = (array) ($files[$fieldKey] ?? []);

                foreach ((array) $value as $rowKey => $row) {
                    $clean = [];
                    foreach ($field['fields'] as $subKey => $subField) {
                        $clean[$subKey] = $this->fieldValue(
                            $subField,
                            $row[$subKey] ?? null,
                            (array) ($fileRows[$rowKey] ?? []),
                            $subKey,
                            (array) $row,
                            null,
                            $dir
                        );
                    }
                    if (collect($clean)->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])->isNotEmpty()) {
                        $rows[] = $clean;
                    }
                }

                return $rows;

            default:
                return is_string($value) ? $value : ($value ?? null);
        }
    }
}
