<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{

    /**
    * Get all user from the database.
    *
    * @return surveys
    */
   public function index()
   {
       return User::all();
   }


   /**
    * Get a user by its ID.
    *
    * @param  int  $id
    * @return Response
    */
   public function show($id)
   {
       return User::findOrFail($id);
   }


   /**
   *
   * Store a user with post request
   *
   *
   */
   public function store(Request $request){

     $this->validate($request, [
             'email' => 'required',
             'password' => 'required',
             ]);

     $user = new User;

             $user->password=$request->password;
             $user->email=$request->email;
             $user->name=$request->name;
             $user->save();

     return response()->json($user,201);


   }

   /**
   *
   * Update a user with post request
   *
   *
   */
   public function update(Request $request){

     $this->validate($request, [
             'id' => 'required',
             'email' => 'required',
             'password' => 'required',
             ]);

     $user = User::find($request->id);

             $user->password=$request->password;
             $user->email=$request->email;
             $user->save();

     return response()->json($user,201);
   }

   /**
   *
   * Delete a user with post request
   *
   *
   */
   public function delete(Request $request){

     $this->validate($request, [
             'id' => 'required',
             ]);

     $user = User::find($request->id);
             $user->delete();

     return response(201);


   }

   public function authentifyUser(Request $request){
    $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

    $user= User::where('email',$request->email)->firstOrFail();

    if($user != null){
      if(Hash::check($request->password,$user->password)){
        $answer=response();

      }
    else{
    $answer=abort(403,'Wrong Password');
    }
    }
    else{
      $answer=abort(403, 'Unauthorized action.');
      }
    return $answer;
    }



}
