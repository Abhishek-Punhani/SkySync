<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia; 

class Filecontroller extends Controller
{
    public function myFiles(){
        $folder=$this->getRoot();
        $files=File::query()
        ->where('parent_id',$folder->id)
        ->where('created_by',Auth::id())
        ->orderBy('is_folder','desc')
        ->orderBy('created_at','desc')
        ->paginate(10);

        $files=FileResource::collection($files);
        return Inertia::render('MyFiles', compact('files'));
    }
     public function createFolder(StoreFolderRequest $request){
       $data=$request->validated();
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

    public function getRoot(){
        return File :: query()->whereIsRoot()->where('created_by',Auth::id())->firstOrFail();
    }
}
