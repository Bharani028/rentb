<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
public function store(Request $request)
{
    // FilePond usually sends the file as 'file'
    if ($request->hasFile('file') || $request->hasFile('filepond')) {
        $uploadedFile = $request->file('file') ?? $request->file('filepond');
        $path = $uploadedFile->store('temp');

        return response()->json(['id' => $path], 200);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
}


public function revert(Request $request)
{
    $filePath = $request->getContent();
    Storage::delete($filePath);

    return response($filePath, 200); // return same id so JS can match hidden input
}

}
