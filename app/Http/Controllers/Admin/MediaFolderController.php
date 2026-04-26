<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Str;

class MediaFolderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $data['slug']       = Str::slug($data['name']);
        $data['created_by'] = auth('admin')->id();

        MediaFolder::create($data);

        return back()->with('success', 'Folder created.');
    }

    public function update(Request $request, MediaFolder $folder): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $folder->update(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);
        return back()->with('success', 'Folder renamed.');
    }

    public function destroy(MediaFolder $folder): RedirectResponse
    {
        if ($folder->media()->exists() || $folder->children()->exists()) {
            return back()->with('error', 'Folder is not empty.');
        }
        $folder->delete();
        return redirect()->route('admin.media.index')->with('success', 'Folder deleted.');
    }
}
