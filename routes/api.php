<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::group(['middleware' => ['api', 'cors']], function () {
//
//   Route::get('/k', function () {
//       return view('welcome');
//   });
// });

Route::group(['middleware' => ['api', 'cors']], function () {


/*** APP API****/
/** USERS **/
Route::get('/users',"UserController@index");

Route::post('/users/add',"UserController@store");

Route::get('/users/{id}',"UserController@show");

Route::put('/users/{id}','UserController@update');

Route::delete('/users/{id}','UserController@delete');



/** GOOGLE DRIVE API ****/

  Route::get('/hi', function () {
      return view('welcome');
  });

  Route::get('put', function() {
      Storage::cloud()->put('test.txt', 'Hello World');
      return 'File was saved to Google Drive';
  });

  Route::get('put-existing', function() {
      $filename = 'laravel.png';
      $filePath = public_path($filename);
      $fileData = File::get($filePath);

      Storage::cloud()->put($filename, $fileData);
      return 'File was saved to Google Drive';
  });



  /***
  *
  ** Return list of content a dir
  * if no path given, return lsit of root dir
  *
  */
  Route::get('list', function(Request $request) {
      if ($request->has('path')){
        $dir = $request->path;
      }else{
        $dir = '/';
      }
      $recursive = false; // Get subdirectories also?
      $contents = collect(Storage::cloud()->listContents($dir, $recursive));
      return $contents;
      // return $contents->where('type', '=', 'file'); // files
  });


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



  Route::get('get', function(Request $request) {


    $dir = '/';
    $path;
    $mimetype;
    $filename;
    if ($request->has('mimetype') && $request->has('filename') && $request->has('path') ){
      $mimetype = $request->mimetype;
      $filename = $request->filename;
      $path = $request->path;
    } else {
      return abort(405);
    }

    // $recursive = true; // Get subdirectories also?
    // $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //
    // $file = $contents
    //     ->where('type', '=', 'file')
    //     ->first(); // there can be duplicate file names!
    // $filename = $file['filename'];

    //return $file; // array with file info

    $rawData = Storage::cloud()->get($path);

    return response($rawData, 200)
        ->header('ContentType', $mimetype)
        ->header('Content-Disposition', "attachment; filename='$filename'");
  });

  Route::get('put-get-stream', function() {
      // Use a stream to upload and download larger files
      // to avoid exceeding PHP's memory limit.

      // Thanks to @Arman8852's comment:
      // https://github.com/ivanvermeyen/laravel-google-drive-demo/issues/4#issuecomment-331625531
      // And this excellent explanation from Freek Van der Herten:
      // https://murze.be/2015/07/upload-large-files-to-s3-using-laravel-5/

      // Assume this is a large file...
      $filename = 'laravel.png';
      $filePath = public_path($filename);

      // Upload using a stream...
      Storage::cloud()->put($filename, fopen($filePath, 'r+'));

      // Get file listing...
      $dir = '/';
      $recursive = false; // Get subdirectories also?
      $contents = collect(Storage::cloud()->listContents($dir, $recursive));

      // Get file details...
      $file = $contents
          ->where('type', '=', 'file')
          ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
          ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
          ->first(); // there can be duplicate file names!

      //return $file; // array with file info

      // Store the file locally...
      //$readStream = Storage::cloud()->getDriver()->readStream($file['path']);
      //$targetFile = storage_path("downloaded-{$filename}");
      //file_put_contents($targetFile, stream_get_contents($readStream), FILE_APPEND);

      // Stream the file to the browser...
      $readStream = Storage::cloud()->getDriver()->readStream($file['path']);

      return response()->stream(function () use ($readStream) {
          fpassthru($readStream);
      }, 200, [
          'Content-Type' => $file['mimetype'],
          //'Content-disposition' => 'attachment; filename="'.$filename.'"', // force download?
      ]);
  });

  Route::get('create-dir', function() {
      Storage::cloud()->makeDirectory('Test Dir');
      return 'Directory was created in Google Drive';
  });

  Route::get('put-in-dir', function() {
      $dir = '/';
      $recursive = false; // Get subdirectories also?
      $contents = collect(Storage::cloud()->listContents($dir, $recursive));

      $dir = $contents->where('type', '=', 'dir')
          ->where('filename', '=', 'Test Dir')
          ->first(); // There could be duplicate directory names!

      if ( ! $dir) {
          return 'Directory does not exist!';
      }

      Storage::cloud()->put($dir['path'].'/test.txt', 'Hello World');

      return 'File was created in the sub directory in Google Drive';
  });

  Route::get('newest', function() {
      $filename = 'test.txt';

      Storage::cloud()->put($filename, \Carbon\Carbon::now()->toDateTimeString());

      $dir = '/';
      $recursive = false; // Get subdirectories also?

      $file = collect(Storage::cloud()->listContents($dir, $recursive))
          ->where('type', '=', 'file')
          ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
          ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
          ->sortBy('timestamp')
          ->last();

      return Storage::cloud()->get($file['path']);
  });

  Route::get('delete', function() {
      $filename = 'test.txt';

      // First we need to create a file to delete
      Storage::cloud()->makeDirectory('Test Dir');

      // Now find that file and use its ID (path) to delete it
      $dir = '/';
      $recursive = false; // Get subdirectories also?
      $contents = collect(Storage::cloud()->listContents($dir, $recursive));

      $file = $contents
          ->where('type', '=', 'file')
          ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
          ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
          ->first(); // there can be duplicate file names!

      Storage::cloud()->delete($file['path']);

      return 'File was deleted from Google Drive';
  });

  Route::get('delete-dir', function() {
      $directoryName = 'test';

      // First we need to create a directory to delete
      Storage::cloud()->makeDirectory($directoryName);

      // Now find that directory and use its ID (path) to delete it
      $dir = '/';
      $recursive = false; // Get subdirectories also?
      $contents = collect(Storage::cloud()->listContents($dir, $recursive));

      $directory = $contents
          ->where('type', '=', 'dir')
          ->where('filename', '=', $directoryName)
          ->first(); // there can be duplicate file names!

      Storage::cloud()->deleteDirectory($directory['path']);

      return 'Directory was deleted from Google Drive';
  });

  Route::get('rename-dir', function() {
      $directoryName = 'test';

      // First we need to create a directory to rename
      Storage::cloud()->makeDirectory($directoryName);

      // Now find that directory and use its ID (path) to rename it
      $dir = '/';
      $recursive = false; // Get subdirectories also?
      $contents = collect(Storage::cloud()->listContents($dir, $recursive));

      $directory = $contents
          ->where('type', '=', 'dir')
          ->where('filename', '=', $directoryName)
          ->first(); // there can be duplicate file names!

      Storage::cloud()->move($directory['path'], 'new-test');

      return 'Directory was renamed in Google Drive';
  });


});
