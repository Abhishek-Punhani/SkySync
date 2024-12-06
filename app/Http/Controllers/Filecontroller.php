<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia; 

class Filecontroller extends Controller
{
    public function myFiles(){
        return Inertia::render(component: 'MyFiles');
    }
     public function createFolder(StoreFolderRequest $request){
       $data=$request->validated();
        $parent=$request->parent;
        if(!$parent){
            $parent=$this->getRoot();
        }

        $file=new File();
        $file->isFolder=1;
        $file->name=$data['name'];
        $parent->appendNode($file);
    }

    public function getRoot(){
        return File :: query()->whereIsRoot()->where('created_by',Auth::id())->firstOrFail();
    }
}
