<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentTranslation;
use App\Models\Language;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private AiService $ai) {}

    public function generateProductDescription(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'category'   => 'nullable|string|max:100',
            'brand'      => 'nullable|string|max:100',
            'attributes' => 'nullable|string|max:500',
            'tone'       => 'nullable|string|in:professional,casual,enthusiastic,minimal',
            'length'     => 'nullable|string|in:short,medium,long',
        ]);

        if (! $this->ai->hasDefault()) {
            return response()->json(['error' => 'No AI provider configured. Go to Integrations to set one up.'], 422);
        }

        $tone   = $request->input('tone', 'professional');
        $length = match ($request->input('length', 'medium')) {
            'short'  => '1-2 short paragraphs (under 100 words)',
            'long'   => '4-5 paragraphs with detailed benefits and features',
            default  => '2-3 paragraphs (around 150-200 words)',
        };

        $parts = array_filter([
            "Product name: {$request->input('name')}",
            $request->filled('category')   ? "Category: {$request->input('category')}"     : null,
            $request->filled('brand')      ? "Brand: {$request->input('brand')}"            : null,
            $request->filled('attributes') ? "Key attributes: {$request->input('attributes')}" : null,
        ]);

        $prompt = "Write a {$tone} e-commerce product description for the following product.\n"
            . "Length: {$length}.\n"
            . "Do not include a headline or product name as a title — start directly with the description body.\n"
            . "Use plain text with no markdown.\n\n"
            . implode("\n", $parts);

        try {
            $text = $this->ai->complete($prompt, ['max_tokens' => 600]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['text' => trim($text)]);
    }

    public function generateBlogContent(Request $request): JsonResponse
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'keywords' => 'nullable|string|max:300',
            'tone'     => 'nullable|string|in:professional,conversational,informative,persuasive',
            'length'   => 'nullable|string|in:short,medium,long',
        ]);

        if (! $this->ai->hasDefault()) {
            return response()->json(['error' => 'No AI provider configured. Go to Integrations to set one up.'], 422);
        }

        $tone   = $request->input('tone', 'informative');
        $length = match ($request->input('length', 'medium')) {
            'short'  => 'around 300 words',
            'long'   => 'around 900-1200 words with multiple sections',
            default  => 'around 500-600 words',
        };

        $prompt = "Write a {$tone} blog post with the title: \"{$request->input('title')}\".\n"
            . "Length: {$length}.\n"
            . ($request->filled('keywords') ? "Include these keywords naturally: {$request->input('keywords')}.\n" : '')
            . "Use plain text only. Separate sections with double newlines. Do not use markdown headers or bullet dashes.";

        try {
            $text = $this->ai->complete($prompt, ['max_tokens' => 1500]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['text' => trim($text)]);
    }

    public function generateMetaDescription(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'title'   => 'nullable|string|max:255',
        ]);

        if (! $this->ai->hasDefault()) {
            return response()->json(['error' => 'No AI provider configured.'], 422);
        }

        $excerpt = mb_substr(strip_tags($request->input('content')), 0, 1500);
        $prompt  = "Write a concise SEO meta description (under 160 characters) for the following content"
            . ($request->filled('title') ? " titled \"{$request->input('title')}\"" : '')
            . ". Return only the meta description text, nothing else.\n\n{$excerpt}";

        try {
            $text = $this->ai->complete($prompt, ['max_tokens' => 100]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['text' => trim($text, " \t\n\r\0\x0B\"'")]);
    }

    public function translateContent(Request $request): JsonResponse
    {
        $request->validate([
            'language_id'       => 'required|integer|exists:languages,id',
            'translatable_type' => 'required|in:product,blog',
            'translatable_id'   => 'required|integer',
            'fields'            => 'required|array',
        ]);

        if (! $this->ai->hasDefault()) {
            return response()->json(['error' => 'No AI provider configured. Go to Integrations to set one up.'], 422);
        }

        $language = Language::findOrFail($request->integer('language_id'));
        $fields   = $request->input('fields');

        $json   = json_encode($fields, JSON_UNESCAPED_UNICODE);
        $prompt = <<<PROMPT
Translate the following JSON key-value pairs into {$language->name} (language code: {$language->code}).
Keep every key exactly as-is. Translate only the values. Preserve HTML tags exactly as they appear.
Return ONLY valid JSON, no markdown, no code fences.

$json
PROMPT;

        try {
            $raw     = $this->ai->complete($prompt, ['max_tokens' => 3000, 'temperature' => 0.3]);
            $raw     = preg_replace('/^```json\s*/i', '', trim($raw));
            $raw     = preg_replace('/```$/', '', $raw);
            $decoded = json_decode(trim($raw), true);

            if (! is_array($decoded)) {
                return response()->json(['error' => 'AI returned invalid JSON. Try again.'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'AI request failed: ' . $e->getMessage()], 500);
        }

        $modelClass = $request->input('translatable_type') === 'product'
            ? \App\Models\Product::class
            : \App\Models\Blog::class;

        ContentTranslation::updateOrCreate(
            [
                'language_id'       => $language->id,
                'translatable_type' => $modelClass,
                'translatable_id'   => $request->integer('translatable_id'),
            ],
            ['fields' => $decoded]
        );

        return response()->json(['success' => true, 'fields' => $decoded]);
    }

    public function saveContentTranslation(Request $request): JsonResponse
    {
        $request->validate([
            'language_id'       => 'required|integer|exists:languages,id',
            'translatable_type' => 'required|in:product,blog',
            'translatable_id'   => 'required|integer',
            'fields'            => 'required|array',
        ]);

        $modelClass = $request->input('translatable_type') === 'product'
            ? \App\Models\Product::class
            : \App\Models\Blog::class;

        ContentTranslation::updateOrCreate(
            [
                'language_id'       => $request->integer('language_id'),
                'translatable_type' => $modelClass,
                'translatable_id'   => $request->integer('translatable_id'),
            ],
            ['fields' => $request->input('fields')]
        );

        return response()->json(['success' => true]);
    }
}
