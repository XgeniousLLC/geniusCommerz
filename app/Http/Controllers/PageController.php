<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(Page $page): Response
    {
        if ($page->status !== 'published') {
            abort(404);
        }

        $page->load('metaInformation');
        $meta = $page->metaInformation;

        return Inertia::render('Page', [
            'page' => [
                'id'      => $page->id,
                'title'   => $page->title,
                'slug'    => $page->slug,
                'content' => $page->content,
                'metaInformation' => $meta ? [
                    'meta_title'       => $meta->meta_title,
                    'meta_description' => $meta->meta_description,
                ] : null,
            ],
        ]);
    }
}
