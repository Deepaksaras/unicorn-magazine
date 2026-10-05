<?php

namespace App\Http\Controllers;

use App\Rules\RealEmail;
use App\Models\AdvertisingEnquiry;
use App\Models\ContactMessage;
use App\Models\JobOpening;
use App\Models\TeamMember;
use App\Support\PageContent;
use App\Support\Seo;
use Illuminate\Http\Request;

/**
 * Public "designed" pages. All texts/images are edited in Admin → Pages.
 */
class PageController extends Controller
{
    protected function content(string $key): PageContent
    {
        $content = PageContent::for($key);

        // Draft pages are only visible to logged-in admins (preview)
        abort_if(!$content->isPublished() && !auth()->check(), 404);

        // SEO tags: Admin → Website → Pages → (this page) → SEO box. Empty = page title + first text.
        Seo::model($content->page, [
            'title' => $content->page->title ?? ($content->blueprint['title'] ?? null),
            'description' => $content->summary(),
        ]);

        return $content;
    }

    public function about()
    {
        $content = $this->content('about');

        $team = TeamMember::where('status', 1)->orderBy('sort_order')->orderBy('id')->get();

        return view('pages.about', compact('content', 'team'));
    }

    public function contact()
    {
        return view('pages.contact', ['content' => $this->content('contact')]);
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => ['required', 'string', 'max:191', new RealEmail()],
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
            'consent' => 'accepted',
        ], [
            'consent.accepted' => 'Please accept the privacy policy and terms of use.',
        ]);

        unset($data['consent']);

        ContactMessage::create($data + [
            'ip_address' => $request->ip(),
            'is_read' => 0,
            'status' => 1,
        ]);

        $message = PageContent::for('contact')->get('form.success', 'Thank you! We will get back to you shortly.');

        return redirect()->to(route('contact') . '#contactForm')->with('success', $message);
    }

    public function career()
    {
        $content = $this->content('career');

        $jobs = JobOpening::where('status', 1)->orderBy('position')->orderByDesc('id')->get();

        return view('pages.career', compact('content', 'jobs'));
    }

    public function advertise()
    {
        return view('pages.advertise', ['content' => $this->content('advertise')]);
    }

    public function sendEnquiry(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'company' => 'required|string|max:191',
            'email' => ['required', 'string', 'max:191', new RealEmail()],
            'phone' => 'nullable|string|max:20',
            'industry' => 'required|string|max:100',
            'budget' => 'nullable|string|max:50',
            'interest' => 'required|array|min:1',
            'interest.*' => 'string|max:100',
            'message' => 'required|string|max:5000',
            'consent' => 'accepted',
        ], [
            'interest.required' => 'Please choose at least one advertising interest.',
            'consent.accepted' => 'Please agree to be contacted.',
        ]);

        AdvertisingEnquiry::create([
            'name' => $data['name'],
            'company' => $data['company'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'industry' => $data['industry'],
            'budget' => $data['budget'] ?? null,
            'placement_interest' => implode(', ', $data['interest']),
            'message' => $data['message'],
            'enquiry_status' => 'new',
            'is_read' => 0,
            'status' => 1,
        ]);

        $message = PageContent::for('advertise')->get('form.success', 'Thank you for your enquiry.');

        return redirect()->to(route('advertise') . '#advertiseForm')->with('success', $message);
    }

    public function privacy()
    {
        return view('pages.legal', ['content' => $this->content('privacy'), 'crumb' => 'Privacy Policy']);
    }

    public function terms()
    {
        return view('pages.legal', ['content' => $this->content('terms'), 'crumb' => 'Terms & Conditions']);
    }
}
