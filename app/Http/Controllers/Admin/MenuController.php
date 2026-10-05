<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /** Menus the website layout renders. */
    public const SYSTEM = [
        'header-menu' => 'Main navigation under the logo',
        'footer-stories' => 'Footer column 1 (“Stories”)',
        'footer-company' => 'Footer column 2 (“Company”)',
        'footer-legal' => 'Footer bottom links',
        'offcanvas-menu' => 'Side panel opened by the ☰ button',
    ];

    public function index()
    {
        $menus = Menu::withCount(['items' => fn ($q) => $q->where('status', '!=', 4)])
            ->where('status', '!=', 4)->orderBy('id')->get();

        return view('admin.menus.index', ['menus' => $menus, 'system' => self::SYSTEM]);
    }

    public function create()
    {
        return view('admin.menus.create', ['menu' => new Menu(['status' => 1])]);
    }

    public function store(Request $request)
    {
        $menu = Menu::create($this->validated($request));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created — now add some links.');
    }

    public function show(Menu $menu)
    {
        return redirect()->route('admin.menus.edit', $menu);
    }

    public function edit(Menu $menu)
    {
        $items = $menu->items()->where('status', '!=', 4)->orderBy('position')->get();

        return view('admin.menus.edit', [
            'menu' => $menu,
            'items' => $items,
            'system' => self::SYSTEM,
            'suggestions' => $this->suggestions(),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validated($request, $menu));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu saved.');
    }

    public function destroy(Menu $menu)
    {
        if (array_key_exists($menu->slug, self::SYSTEM)) {
            return back()->with('error', 'This menu is used by the website layout and cannot be deleted.');
        }

        $menu->update(['status' => 4, 'slug' => $menu->slug . '-deleted-' . $menu->id]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    /* ----------------------------- Items ----------------------------- */

    public function storeItem(Request $request, Menu $menu)
    {
        $data = $this->validatedItem($request);
        $data['menu_id'] = $menu->id;
        $data['position'] = (int) $menu->items()->where('status', '!=', 4)->max('position') + 1;
        MenuItem::create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Link added.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless((int) $item->menu_id === (int) $menu->id, 404);
        $item->update($this->validatedItem($request));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Link updated.');
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        abort_unless((int) $item->menu_id === (int) $menu->id, 404);
        $item->update(['status' => 4]);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Link removed.');
    }

    public function reorder(Request $request, Menu $menu)
    {
        foreach ((array) $request->input('ids', []) as $i => $id) {
            MenuItem::where('menu_id', $menu->id)->whereKey($id)->update(['position' => $i + 1]);
        }

        return response()->json(['message' => 'Menu order saved.']);
    }

    /* ----------------------------------------------------------------- */

    protected function suggestions(): array
    {
        $pages = [
            'Home' => '/', 'Latest News' => '/latest', 'About Us' => '/about', 'Contact' => '/contact',
            'Careers' => '/career', 'Advertise with Us' => '/advertise-with-us', 'Reports' => '/reports',
            'Archive' => '/archive', 'Privacy Policy' => '/privacy-policy', 'Terms & Conditions' => '/terms-conditions',
        ];

        $cats = Category::where('status', 1)->orderBy('name')->get(['name', 'slug'])
            ->mapWithKeys(fn ($c) => [$c->name => '/category/' . $c->slug])->all();

        return ['Pages' => $pages, 'Categories' => $cats];
    }

    protected function validated(Request $request, ?Menu $menu = null): array
    {
        $isSystem = $menu && array_key_exists($menu->slug, self::SYSTEM);
        $request->merge(['slug' => $isSystem ? $menu->slug : Str::slug($request->input('slug') ?: $request->input('name'))]);

        return $request->validate([
            'name' => 'required|string|max:191',
            'slug' => ['required', 'max:191', Rule::unique('menus', 'slug')->ignore($menu?->id)],
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:0,1',
        ]);
    }

    protected function validatedItem(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:191',
            'url' => 'required|string|max:191',
            'target' => 'required|in:_self,_blank',
            'css_class' => 'nullable|string|max:191',
            'status' => 'required|in:0,1',
        ]);
    }
}
