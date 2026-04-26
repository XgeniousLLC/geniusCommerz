<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        $languages = Language::orderByDesc('is_default')->orderBy('name')->get();
        return view('admin.languages.index', compact('languages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'code' => 'required|string|max:10|unique:languages,code|alpha_dash',
        ]);

        Language::create($data + ['is_active' => true]);
        return redirect()->route('admin.languages.index')->with('success', 'Language added.');
    }

    public function edit(Language $language): View
    {
        $strings      = config('storefront_strings');
        $translations = $language->translationsMap();
        return view('admin.languages.edit', compact('language', 'strings', 'translations'));
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:60',
            'is_active'  => 'boolean',
            'is_default' => 'boolean',
            'strings'    => 'array',
            'strings.*'  => 'nullable|string',
        ]);

        $language->update([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->boolean('is_default')) {
            $language->setAsDefault();
        }

        foreach ($request->input('strings', []) as $key => $value) {
            if (array_key_exists($key, config('storefront_strings'))) {
                Translation::updateOrCreate(
                    ['language_id' => $language->id, 'key' => $key],
                    ['value' => $value ?? '']
                );
            }
        }

        return redirect()->route('admin.languages.edit', $language)
            ->with('success', 'Language updated.');
    }

    public function destroy(Language $language): RedirectResponse
    {
        $language->delete();
        return redirect()->route('admin.languages.index')->with('success', 'Language deleted.');
    }

    public function setDefault(Language $language): RedirectResponse
    {
        $language->setAsDefault();
        return redirect()->route('admin.languages.index')->with('success', "{$language->name} is now the default language.");
    }

    public function translateWithAi(Language $language): JsonResponse
    {
        $ai = app(AiService::class);

        if (! $ai->hasDefault()) {
            return response()->json(['error' => 'No default AI provider configured. Go to Integrations and set one as default.'], 422);
        }

        $strings = config('storefront_strings');
        $json    = json_encode($strings, JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
Translate the following JSON key-value pairs into {$language->name} (language code: {$language->code}).
Keep every key exactly as-is in English. Translate only the values.
Preserve any {placeholder} tokens (like {count}, {attribute}, {from}, {to}, {total}) exactly as they appear — do not translate them.
Return ONLY valid JSON with no markdown, no code fences, no explanation.

$json
PROMPT;

        try {
            $raw     = $ai->complete($prompt, ['temperature' => 0.3]);
            $raw     = preg_replace('/^```json\s*/i', '', trim($raw));
            $raw     = preg_replace('/```$/', '', $raw);
            $decoded = json_decode(trim($raw), true);

            if (! is_array($decoded)) {
                return response()->json(['error' => 'AI returned invalid JSON. Try again.'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'AI request failed: ' . $e->getMessage()], 500);
        }

        foreach ($decoded as $key => $value) {
            if (array_key_exists($key, $strings) && is_string($value)) {
                Translation::updateOrCreate(
                    ['language_id' => $language->id, 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        return response()->json(['success' => true, 'count' => count($decoded)]);
    }
}
