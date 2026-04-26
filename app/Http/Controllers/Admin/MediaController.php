<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Media, MediaFolder};
use App\Services\MediaService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function index(Request $request): View|\Illuminate\Contracts\View\View
    {
        $isPicker = $request->boolean('picker');
        $accept   = $request->get('accept', ''); // image | document | video | ''
        $multiple = $request->boolean('multiple');

        $folders = MediaFolder::whereNull('parent_id')
            ->with('children.children')
            ->orderBy('name')
            ->get();

        $query = Media::query()->latest();

        if ($folderId = $request->integer('folder_id') ?: null) {
            $query->where('folder_id', $folderId);
        }
        if ($search = $request->get('search')) {
            $query->where('original_filename', 'like', '%'.$search.'%');
        }
        if ($typeFilter = $request->get('type') ?: $accept) {
            $query->where('type', $typeFilter);
        }

        $media      = $query->paginate(48)->withQueryString();
        $activeFolder = $folderId ? MediaFolder::find($folderId) : null;

        return view('admin.media.index', compact(
            'media', 'folders', 'isPicker', 'accept', 'multiple', 'activeFolder'
        ));
    }

    public function resolve(Request $request): JsonResponse
    {
        $ids   = array_filter(array_map('intval', (array) $request->get('ids', [])));
        $items = Media::whereIn('id', $ids)->get()->keyBy('id');

        $result = collect($ids)
            ->filter(fn ($id) => $items->has($id))
            ->map(fn ($id) => $this->serialize($items[$id]))
            ->values();

        return response()->json($result);
    }

    public function chunk(Request $request): JsonResponse
    {
        $request->validate([
            'chunk' => 'required|file',
            'uuid'  => ['required', 'string', 'regex:/^[0-9a-f\-]{36}$/'],
            'index' => 'required|integer|min:0',
            'total' => 'required|integer|min:1|max:500',
            'name'  => 'required|string|max:255',
            'mime'  => 'required|string|max:100',
        ]);

        $uuid  = $request->input('uuid');
        $index = (int) $request->input('index');
        $total = (int) $request->input('total');

        $this->mediaService->saveChunk($request->file('chunk'), $uuid, $index);

        if ($this->mediaService->chunksComplete($uuid, $total)) {
            $media = $this->mediaService->assembleAndStore(
                uuid: $uuid,
                total: $total,
                originalName: $request->input('name'),
                mimeType: $request->input('mime', 'application/octet-stream'),
                disk: $request->input('disk', 'public'),
                folderId: $request->integer('folder_id') ?: null,
            );

            return response()->json([
                'complete' => true,
                'media'    => $this->serialize($media),
            ]);
        }

        return response()->json(['complete' => false, 'received' => $index + 1]);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $data = $request->validate([
            'alt'     => 'nullable|string|max:500',
            'title'   => 'nullable|string|max:500',
            'caption' => 'nullable|string|max:1000',
        ]);

        $media->update($data);

        return response()->json(['success' => true, 'media' => $this->serialize($media->fresh())]);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->mediaService->deleteMedia($media);
        return response()->json(['success' => true]);
    }

    public function serialize(Media $media): array
    {
        return [
            'id'                => $media->id,
            'uuid'              => $media->uuid,
            'type'              => $media->type,
            'original_filename' => $media->original_filename,
            'mime_type'         => $media->mime_type,
            'size'              => $media->size,
            'human_size'        => $media->humanSize(),
            'width'             => $media->width,
            'height'            => $media->height,
            'alt'               => $media->alt ?? '',
            'title'             => $media->title ?? '',
            'caption'           => $media->caption ?? '',
            'url'               => $media->getUrl(),
            'thumb_url'         => $media->getUrl('thumb'),
            'created_at'        => $media->created_at?->toISOString(),
        ];
    }
}
