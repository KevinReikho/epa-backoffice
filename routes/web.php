<?php
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('put', function() {
    // Storage::cloud()->put('test.txt', 'Hello World');
    return 'Nothing to see here';
});

Route::get('put-existing', function() {
    // $filename = 'laravel.png';
    // $filePath = public_path($filename);
    // $fileData = File::get($filePath);
    //
    // Storage::cloud()->put($filename, $fileData);
    return 'Nothing to see here';
});

Route::get('list', function() {
    $dir = '/';
    $recursive = true; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));

    //return $contents->where('type', '=', 'dir'); // directories
    return $contents; // files
});

// Route::get('list-folder-contents', function() {
//     // The human readable folder name to get the contents of...
//     // For simplicity, this folder is assumed to exist in the root directory.
//     $folder = 'Test Dir';
//
//     // Get root directory contents...
//     $contents = collect(Storage::cloud()->listContents('/', false));
//
//     // Find the folder you are looking for...
//     $dir = $contents->where('type', '=', 'dir')
//         ->where('filename', '=', $folder)
//         ->first(); // There could be duplicate directory names!
//
//     if ( ! $dir) {
//         return 'No such folder!';
//     }
//
//     // Get the files inside the folder...
//     $files = collect(Storage::cloud()->listContents($dir['path'], false))
//         ->where('type', '=', 'file');
//
//     return $files->mapWithKeys(function($file) {
//         $filename = $file['filename'].'.'.$file['extension'];
//         $path = $file['path'];
//
//         // Use the path to download each file via a generated link..
//         // Storage::cloud()->get($file['path']);
//
//         return [$filename => $path];
//     });
// });

function recursive_content($content) {






    if ($content["type"] == 'dir'){
      $inner = Storage::cloud()->listContents($content['path'], false);
      $content["content"] = array_map("recursive_content",$inner);
    }


  return $content;
}

  Route::get('list-folder-contents', function() {
    // The human readable folder name to get the contents of...
    // For simplicity, this folder is assumed to exist in the root directory.

    // Get root directory contents...
    $contents = Storage::cloud()->listContents('/', false);

    $contentsfinal = array_map("recursive_content",$contents);

    return $contentsfinal;

    // // Get the files inside the folder...
    // $files = collect(Storage::cloud()->listContents($dir['path'], false))
    //     ->where('type', '=', 'file');

    // return $files->mapWithKeys(function($file) {
    //     $filename = $file['filename'].'.'.$file['extension'];
    //     $path = $file['path'];
    //
    //     // Use the path to download each file via a generated link..
    //     // Storage::cloud()->get($file['path']);
    //
    //     return [$filename => $path];
    // });

});


// Download a file with a route ?path=path
Route::get('get', function(Request $request) {

    $dir = '/';
    $path;
    if ($request->has('path')){
      $path = $request->path;
    }
    $recursive = true; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));

    $file = $contents
        ->where('type', '=', 'file')
        ->where('path', '=', $path)
        ->first(); // there can be duplicate file names!
    $filename = $file['filename'];

    //return $file; // array with file info

    $rawData = Storage::cloud()->get($file['path']);

    return response($rawData, 200)
        ->header('ContentType', $file['mimetype'])
        ->header('Content-Disposition', "attachment; filename='$filename'");
});

Route::get('put-get-stream', function() {
    // // Use a stream to upload and download larger files
    // // to avoid exceeding PHP's memory limit.
    //
    // // Thanks to @Arman8852's comment:
    // // https://github.com/ivanvermeyen/laravel-google-drive-demo/issues/4#issuecomment-331625531
    // // And this excellent explanation from Freek Van der Herten:
    // // https://murze.be/2015/07/upload-large-files-to-s3-using-laravel-5/
    //
    // // Assume this is a large file...
    // $filename = 'laravel.png';
    // $filePath = public_path($filename);
    //
    // // Upload using a stream...
    // Storage::cloud()->put($filename, fopen($filePath, 'r+'));
    //
    // // Get file listing...
    // $dir = '/';
    // $recursive = false; // Get subdirectories also?
    // $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //
    // // Get file details...
    // $file = $contents
    //     ->where('type', '=', 'file')
    //     ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
    //     ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
    //     ->first(); // there can be duplicate file names!
    //
    // //return $file; // array with file info
    //
    // // Store the file locally...
    // //$readStream = Storage::cloud()->getDriver()->readStream($file['path']);
    // //$targetFile = storage_path("downloaded-{$filename}");
    // //file_put_contents($targetFile, stream_get_contents($readStream), FILE_APPEND);
    //
    // // Stream the file to the browser...
    // $readStream = Storage::cloud()->getDriver()->readStream($file['path']);
    //
    // return response()->stream(function () use ($readStream) {
    //     fpassthru($readStream);
    // }, 200, [
    //     'Content-Type' => $file['mimetype'],
    //     //'Content-disposition' => 'attachment; filename="'.$filename.'"', // force download?
    // ]);
    return 'nothing to see here';

});

Route::get('create-dir', function() {
    // Storage::cloud()->makeDirectory('Test Dir');
    return 'nothing to see here';
});

Route::get('put-in-dir', function() {
    // $dir = '/';
    // $recursive = false; // Get subdirectories also?
    // $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //
    // $dir = $contents->where('type', '=', 'dir')
    //     ->where('filename', '=', 'Test Dir')
    //     ->first(); // There could be duplicate directory names!
    //
    // if ( ! $dir) {
    //     return 'Directory does not exist!';
    // }
    //
    // Storage::cloud()->put($dir['path'].'/test.txt', 'Hello World');

    return 'nothing to see here';
});

Route::get('newest', function() {
    // $filename = 'test.txt';
    //
    // Storage::cloud()->put($filename, \Carbon\Carbon::now()->toDateTimeString());
    //
    // $dir = '/';
    // $recursive = false; // Get subdirectories also?
    //
    // $file = collect(Storage::cloud()->listContents($dir, $recursive))
    //     ->where('type', '=', 'file')
    //     ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
    //     ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
    //     ->sortBy('timestamp')
    //     ->last();

    // return Storage::cloud()->get($file['path']);
    return 'nothing to see here';

});

Route::get('delete', function() {
    // $filename = 'test.txt';
    //
    // // First we need to create a file to delete
    // Storage::cloud()->makeDirectory('Test Dir');
    //
    // // Now find that file and use its ID (path) to delete it
    // $dir = '/';
    // $recursive = false; // Get subdirectories also?
    // $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //
    // $file = $contents
    //     ->where('type', '=', 'file')
    //     ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
    //     ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
    //     ->first(); // there can be duplicate file names!
    //
    // Storage::cloud()->delete($file['path']);

    return 'nothing to see here';
});

Route::get('delete-dir', function() {
    // $directoryName = 'test';
    //
    // // First we need to create a directory to delete
    // Storage::cloud()->makeDirectory($directoryName);
    //
    // // Now find that directory and use its ID (path) to delete it
    // $dir = '/';
    // $recursive = false; // Get subdirectories also?
    // $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //
    // $directory = $contents
    //     ->where('type', '=', 'dir')
    //     ->where('filename', '=', $directoryName)
    //     ->first(); // there can be duplicate file names!
    //
    // Storage::cloud()->deleteDirectory($directory['path']);

    return 'nothing to see here';
});

Route::get('rename-dir', function() {
    // $directoryName = 'test';
    //
    // // First we need to create a directory to rename
    // Storage::cloud()->makeDirectory($directoryName);
    //
    // // Now find that directory and use its ID (path) to rename it
    // $dir = '/';
    // $recursive = false; // Get subdirectories also?
    // $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //
    // $directory = $contents
    //     ->where('type', '=', 'dir')
    //     ->where('filename', '=', $directoryName)
    //     ->first(); // there can be duplicate file names!
    //
    // Storage::cloud()->move($directory['path'], 'new-test');

    return 'nothing to see here';
});
