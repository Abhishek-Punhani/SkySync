<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class Filecontroller extends Controller
{
    public function myFiles(Request $request, string $folder = null)
    {
        if ($folder) {
            $folder = File::query()->where('created_by', Auth::id())
                ->where('path', $folder)->firstOrFail();
        }
        if (!$folder)
            $folder = $this->getRoot();
        $files = File::query()
            ->where('parent_id', $folder->id)
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return response()->json($files);
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);
        $folder = new FileResource($folder);
        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors'));
    }
    public function createFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent_id ? File::find($request->parent_id) : $this->getRoot();

        if (!$parent) {
            return redirect()->back()->withErrors(['parent_id' => 'Invalid parent folder.']);
        }
        if ($parent->children()->where('name', $data['name'])->exists()) {
            return redirect()->back()->withErrors(['name' => 'A folder with the same name already exists.']);
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];
        $file->created_by = Auth::id();
        $parent->appendNode($file);


        return redirect()->route('myFiles')->with('success', 'Folder created successfully.');
    }
    public function store(StoreFileRequest $request)
    {
        $data = $request->validated();
        $fileTree = $request->file_tree;
        $parent = $request->parent;
        $user = $request->user();

        if (!$parent) {
            $parent = $this->getRoot();
        }
        if (!empty($fileTree)) {
            $this->saveFileTree($fileTree, $parent, $user);
        } else {
            foreach ($data['files'] as $file) {
                /**
                 * @var \Illuminate\Http\UploadedFile $file
                 */
                $this->saveFile($file, $user, $parent);
            }
        }
    }
    public function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    public function saveFileTree($fileTree, $parent, $user)
    {
        foreach ($fileTree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->is_folder = true;
                $folder->name = $name;

                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            } else {
                $this->saveFile($file, $user, $parent);
            }
        }
    }

    /**
     *
     *
     * @param $file
     * @param $user
     * @param $parent
     *
     */
    public function saveFile($file, $user, $parent)
    {
        $path = $file->store('/files/' . $user->id);
        $model = new File();
        $model->storage_path = $path;
        $model->is_folder = false;
        $model->name = $file->getClientOriginalName();
        $model->created_by = Auth::id();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        $parent->appendNode($model);
    }
}
