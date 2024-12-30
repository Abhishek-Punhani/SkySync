<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilesActionRequest;
use App\Http\Requests\ModifyFavRequest;
use App\Http\Requests\ShareFilesRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\TrashFileActionRequest;
use App\Http\Resources\FileResource;
use App\Jobs\UploadFileToCloudJob;
use App\Mail\ShareFilesMail;
use App\Models\File;
use App\Models\FileShare;
use App\Models\StarredFiles;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Filecontroller extends Controller
{
    public function myFiles(Request $request, string $folder = null)
    {
        $search = $request->get("search") ?? false;

        if ($folder) {
            $folder = File::query()->where('created_by', Auth::id())
                ->where('path', $folder)->firstOrFail();
        }
        if (!$folder)
            $folder = $this->getRoot();
        $query = File::query()
            ->select('files.*')
            ->with('starred')
            ->where('created_by', Auth::id())
            ->where('_lft', '!=', 1)
            ->orderBy('is_folder', 'desc')
            ->orderBy('files.created_at', 'desc')
            ->orderBy('files.id', 'desc');

        if ($search) {
            $query->where('name', 'like', "%$search%");
            $search = true;
        } else {
            $query->where('parent_id', $folder->id);
        }



        $files = $query->paginate(10);
        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return response()->json($files);
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);
        $folder = new FileResource($folder);
        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors', 'search'));
    }

    public function trash(Request $request)
    {

        $search = $request->get("search") ?? false;
        $query = File::onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('deleted_at', 'desc');

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $files = $query->paginate(10);
        if ($request->wantsJson()) {
            return response()->json($files);
        }


        return Inertia::render('Trash', compact('files'));
    }

    public function starredFiles(Request $request)
    {
        $starred_files = StarredFiles::query()
            ->where('user_id', Auth::id());
        $file_ids = $starred_files->pluck('file_id')->toArray();

        $files = File::query()
            ->whereIn('id', $file_ids)
            ->with('starred')
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);
        if ($request->wantsJson()) {
            return response()->json($files);
        }

        return Inertia::render('StarredFiles', compact('files'));
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

    public function destroy(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        $user = Auth::user();

        if ($data['all']) {
            $children = $parent->children;
            foreach ($children as $child) {
                $child->moveToTrash(); // Soft delete  :) --> to prevent deleting its children and only folder appears in trash
            }
        } else {
            foreach ($data['ids'] ?? [] as $id) {
                $file = File::find($id);
                if ($file) {
                    $file->moveToTrash();
                }
            }
        }

        return to_route('myFiles', ['folder' => $parent->path]);
    }

    public function download(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        $user = Auth::user();

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];
        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to download.'
            ];
        }
        if ($all) {
            if ($parent->children->count() == 0) {
                return [
                    'message' => 'Folder is empty.'
                ];
            }
            if ($parent->children->count() == 1) {
                $file = $parent->children->first();
                if ($file->is_folder) {
                    if ($file->children->count() == 0) {
                        return [
                            'message' => 'Folder is empty.'
                        ];
                    }
                    $url = $this->createZip($file->children);
                    $filename = $file->name . '.zip';
                } else {
                    $dest = 'public/' . pathinfo(($file->storage_path), PATHINFO_BASENAME);
                    Storage::disk('public')->put($dest, Storage::disk('local')->get($file->storage_path));
                    Storage::disk('public')->copy($file->storage_path, $dest);

                    $url = asset(Storage::disk('public')->url($dest));
                    $filename = $file->name;
                }
            } else {
                $url = $this->createZip($parent->children);
                $filename = $parent->name . '.zip';
            }
        } else {
            [$url, $filename] = $this->getDownloadUrl($ids, $parent->name);
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
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
        $path = $file->store('/files/' . $user->id, 'local');
        $model = new File();
        $model->storage_path = $path;
        $model->is_folder = false;
        $model->name = $file->getClientOriginalName();
        $model->created_by = Auth::id();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        $model->uploaded_on_cloud = 0;

        $parent->appendNode($model);

        // start uploading to cloud
        UploadFileToCloudJob::dispatch($model);
    }

    public function createZip($files): string
    {
        $zipPath = 'zip/' . Str::random() . '.zip';
        $publicPath = "$zipPath";
        if (!is_dir(dirname($publicPath))) {
            Storage::disk('public')->makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::disk('public')->path($publicPath);
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();
        return asset(Storage::disk('local')->url($zipPath));
    }

    private function addFilesToZip($zip,  $files, $ancestors = ''): void
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            } else {
                $localPath = Storage::disk('local')->path($file->storage_path);
                if ($file->uploaded_on_cloud) {
                    $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);

                    $content = Storage::get($file->storage_path);

                    Storage::disk('public')->put($dest, $content);
                    $localPath = Storage::disk('public')->path($dest);
                }
                $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
            }
        }
    }

    public function restore(TrashFileActionRequest $request)
    {
        $data = $request->validated();
        $all = $data['all'] ?? false;
        if ($all) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->restore();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->restore();
            }
        }

        return to_route('trash');
    }
    public function delete_forever(TrashFileActionRequest $request)
    {
        $data = $request->validated();
        $all = $data['all'] ?? false;
        if ($all) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        }

        return to_route('trash');
    }

    public function addtoFavorites(ModifyFavRequest $request)
    {
        $data = $request->validated();
        $id = $data['id'] ?? null;

        $file = File::find($id);

        if (!$id || !$file) {
            return [
                'message' => 'Something went wrong.'
            ];
        }
        $starred_file = StarredFiles::query()->where('file_id', $file->id)->where('user_id', Auth::id())->first();
        if (!$starred_file) {
            StarredFiles::create([
                'file_id' => $file->id,
                'user_id' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            $starred_file->delete();
        }
        return redirect()->back()->with('success', 'Files added to favorites successfully.');
    }

    public function share(ShareFilesRequest $request)
    {
        $data = $request->validated();
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $email = $data['email'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to share'
            ];
        }

        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            return redirect()->back();
        }

        if ($all) {
            $files = $parent->children;
        } else {
            $files = File::find($ids);
        }

        $data = [];
        $ids = Arr::pluck($files, 'id');
        $existingFileIds = FileShare::query()
            ->whereIn('file_id', $ids)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('file_id');

        foreach ($files as $file) {
            if ($existingFileIds->has($file->id)) {
                continue;
            }
            $data[] = [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        FileShare::insert($data);
        Mail::to($user)->send(new ShareFilesMail($user, Auth::user(), $files));

        return redirect()->back();
    }

    public function sharedWithMe(Request $request)
    {
        $search = $request->get("search") ?? false;
        $query = File::getSharedWithMe();

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $files = $query->paginate(10);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('SharedWithMe', compact('files'));
    }

    public function sharedByMe(Request $request)
    {
        $search = $request->get("search") ?? false;
        $query = File::getSharedByMe();


        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $files = $query->paginate(10);
        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('SharedByMe', compact('files'));
    }
    public function downloadSharedWithMe(FilesActionRequest $request)
    {
        $data = $request->validated();

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to download'
            ];
        }

        $zipName = 'shared_with_me';
        if ($all) {
            $files = File::getSharedWithMe()->get();
            $url = $this->createZip($files);
            $filename = $zipName . '.zip';
        } else {
            [$url, $filename] = $this->getDownloadUrl($ids, $zipName);
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    public function downloadSharedByMe(FilesActionRequest $request)
    {
        $data = $request->validated();

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to download'
            ];
        }

        $zipName = 'shared_by_me';
        if ($all) {
            $files = File::getSharedByMe()->get();
            $url = $this->createZip($files);
            $filename = $zipName . '.zip';
        } else {
            [$url, $filename] = $this->getDownloadUrl($ids, $zipName);
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    private function getDownloadUrl(array $ids, string $zipName)
    {
        if (count($ids) == 1) {
            $file = File::find($ids[0]);
            if ($file->is_folder) {
                if ($file->children->count() == 0) {
                    return [
                        'message' => 'Folder is empty.'
                    ];
                }
                $url = $this->createZip($file->children);
                $filename = $file->name . '.zip';
            } else {
                if ($file->uploaded_on_cloud) {
                    $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);

                    $content = Storage::get($file->storage_path);

                    Storage::disk('public')->put($dest, $content);

                    $url = asset(Storage::disk('public')->url($dest));
                    $filename = $file->name;
                } else {
                    $dest = 'public/' . pathinfo(($file->storage_path), PATHINFO_BASENAME);
                    Storage::disk('public')->put($dest, Storage::disk('local')->get($file->storage_path));
                    Storage::disk('public')->copy($file->storage_path, $dest);

                    $url = asset(Storage::disk('public')->url($dest));
                    $filename = $file->name;
                }
            }
        } else {
            $files = File::query()->whereIn('id', $ids)->get();
            $url = $this->createZip($files);
            $filename = $zipName . '.zip';
        }

        return [$url, $filename];
    }
}
