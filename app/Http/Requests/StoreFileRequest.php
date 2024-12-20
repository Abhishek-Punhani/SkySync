<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Support\Facades\Auth;;

class StoreFileRequest extends ParentIdBaseRequest
{

    protected function prepareForValidation()
    {
        $paths = array_filter($this->relative_paths ?? [], fn($f) => $f != null);


        $this->merge([
            'file_paths' => $paths,
            'folder_name' => $this->detectFolderName($paths),
        ]);
    }

    protected function passedValidation()
    {
        $data = $this->validated();

        $this->replace([
            'file_tree' => $this->buildFileTree($this->file_paths, $data['files'])
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return  array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file', // type is file
                function ($attribute, $value, $fail) {
                    if (!$this->folder_name) {
                        /** @var $value \Illuminate\Http\UploadedFile */

                        $file = File::query()->where('name', $value->getClientOriginalName())
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            return $fail('File "' . $value->getClientOriginalName() . '" already exists');
                        }
                    }
                }
            ],
            'folder_name' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {


                        /** @var $value \Illuminate\Http\UploadedFile */

                        $file = File::query()->where('name', $value)
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            return $fail('Folder "' . $value . '" already exists');
                        }
                    }
                }

            ]
        ]);
    }


    public function detectFolderName($paths)
    {
        if (!$paths) {
            return null;
        }

        $parts = explode("/", $paths[0]);

        return $parts[0];
    }

    private  function buildFileTree($filePaths, $files)
    {
        $filePaths = array_slice($filePaths, 0, count($files));

        $filePaths = array_filter($filePaths, fn($f) => $f != null);
        $tree = [];

        /**
         * how its meant to be  say its Ecommerce/test/1.jpg
         * [
         * Ecommerce => [
         *       test => [
         *              1.jpg => 1.jpg // actual file
         *                ]
         *             ]
         */

        foreach ($filePaths as $ind => $filePath) {
            $parts = explode('/', $filePath);

            $currentNode = &$tree;

            foreach ($parts as $i => $part) {
                if (!isset($currentNode[$part])) {
                    $currentNode[$part] = [];
                }

                if ($i == count($parts) - 1) {
                    $currentNode[$part] = $files[$ind];  // since we matched the order now
                } else {
                    $currentNode = &$currentNode[$part]; // making current part array as current node
                }
            }
        }
        return $tree;
    }
}