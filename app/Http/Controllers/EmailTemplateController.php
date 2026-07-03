<?php

namespace App\Http\Controllers;

use App\Mail\EmailTemplateTestEmail;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateCategory;
use App\Models\EmailTemplateVersion;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $this->syncBuiltInMailTemplates();

        $categories = EmailTemplateCategory::active()->orderBy('name')->get();

        $query = EmailTemplate::with('categoryRelation')
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('category', $request->input('category'))
                    ->orWhereHas('categoryRelation', function ($inner) use ($request) {
                        $inner->where('slug', $request->input('category'))
                            ->orWhere('name', $request->input('category'));
                    });
            });
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('portal.admin.email_templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        $categories = EmailTemplateCategory::active()->orderBy('name')->get();

        return view('portal.admin.email_templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name',
            'subject' => 'required|string|max:255',
            'html_body' => 'required|string',
            'text_body' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $category = EmailTemplateCategory::findOrCreateByName($request->input('category'));

        EmailTemplate::create(array_merge($validated, [
            'category_id' => $category?->id,
            'is_active' => $request->boolean('is_active', true),
        ]));

        return Redirect::route('portal.admin.messages.email_templates.index')
            ->with('status', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $categories = EmailTemplateCategory::active()->orderBy('name')->get();
        $emailTemplate->load('versions');

        return view('portal.admin.email_templates.edit', compact('emailTemplate', 'categories'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name,'.$emailTemplate->id,
            'subject' => 'required|string|max:255',
            'html_body' => 'required|string',
            'text_body' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $category = EmailTemplateCategory::findOrCreateByName($request->input('category'));

        $emailTemplate->update(array_merge($validated, [
            'category_id' => $category?->id,
            'is_active' => $request->boolean('is_active', true),
        ]));

        return Redirect::route('portal.admin.messages.email_templates.index')
            ->with('status', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return Redirect::route('portal.admin.messages.email_templates.index')
            ->with('status', 'Email template deleted successfully.');
    }

    public function preview(EmailTemplate $emailTemplate)
    {
        $sampleValues = $emailTemplate->sampleVariables();
        $rendered = $emailTemplate->render($sampleValues);

        return view('portal.admin.email_templates.preview', compact('emailTemplate', 'rendered', 'sampleValues'));
    }

    public function duplicate(EmailTemplate $emailTemplate)
    {
        $duplicate = $emailTemplate->replicate(['slug']);
        $duplicate->name = $emailTemplate->name.' Copy';
        $duplicate->slug = EmailTemplate::makeUniqueSlug($duplicate->name);
        $duplicate->is_active = false;
        $duplicate->save();

        return Redirect::route('portal.admin.messages.email_templates.edit', $duplicate)
            ->with('status', 'Template cloned successfully. Review and publish the copy.');
    }

    public function restoreVersion(EmailTemplate $emailTemplate, EmailTemplateVersion $version)
    {
        abort_if($version->email_template_id !== $emailTemplate->id, 404);

        $emailTemplate->update([
            'name' => $version->name,
            'slug' => $version->slug,
            'subject' => $version->subject,
            'html_body' => $version->html_body,
            'text_body' => $version->text_body,
            'variables' => $version->variables,
            'category_id' => $version->category_id,
            'category' => $version->category,
            'is_active' => $version->is_active,
        ]);

        return Redirect::route('portal.admin.messages.email_templates.edit', $emailTemplate)
            ->with('status', 'Template restored to version '.$version->version_number.'.');
    }

    public function sendTestEmail(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        try {
            Mail::to($validated['test_email'])->send(
                new EmailTemplateTestEmail($emailTemplate, $emailTemplate->sampleVariables())
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }

        return back()->with('status', 'Test email sent to '.$validated['test_email'].'.');
    }

    private function syncBuiltInMailTemplates(): void
    {
        foreach (EmailTemplateService::getBuiltInTemplateDefinitions() as $definition) {
            $template = EmailTemplate::where('slug', $definition['slug'])->first();
            $htmlBody = EmailTemplateService::resolveBuiltInTemplateHtml($definition);
            $textBody = $definition['text'] ?? $this->convertHtmlToText($htmlBody);
            $category = EmailTemplateCategory::findOrCreateByName($definition['category']);

            $payload = [
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'subject' => $definition['subject'],
                'html_body' => $htmlBody,
                'text_body' => $textBody,
                'category' => $category?->name,
                'category_id' => $category?->id,
                'is_active' => true,
            ];

            if ($template) {
                $needsRefresh = empty(trim((string) $template->html_body)) || empty(trim((string) $template->text_body));
                if ($needsRefresh) {
                    $template->fill($payload);
                    $template->save();
                }
            } else {
                EmailTemplate::create($payload);
            }
        }
    }

    private function convertHtmlToText(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = strip_tags($text);
        $text = preg_replace('/\R{3,}/u', "\n\n", $text);

        return trim((string) $text);
    }
}
