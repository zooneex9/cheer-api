<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    // Check if user has an active session
    public function loggedIn()
    {
        $user = Auth::guard('api')->user();
        $currentUser = User::where("id", $user->id)->first();
        return $this->sendResponse($currentUser, 'User logged.');
    }

    public function index()
    {
        $users = User::where('role_id', '!=', 1)->get();
        // $data = [];
        // $data['name'] = "Miembros";
        // $data['items'] = $users;
        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    public function image()
    {
        $user = Auth::guard('api')->user();
        
        return $this->sendResponse($user->image, 'Image retrieved successfully.');
    }


    public function getUser($id)
    {
        $user = User::with(['role'])->where("id", $id)->first();
        $data = [];
        $data['user'] = $user;
        return $this->sendResponse($data, 'user retrived successfully.');
    }

    public function updateUser(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update($request->only([
            'name',
            'last_name'
        ]));

        $password = $request->get('password');
        if (isset($password)) {
            $user->password = bcrypt($password);
            $user->save();
        }

        return $this->sendResponse($user, 'Usuario actualizado correctamente.');
    }

    public function create_user(Request $request)
    {
        $tmpPassword = $this->generateRandomString(10);

        $user = User::create([
            'password' => bcrypt($tmpPassword),
            'name' => $request->get('name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
            'role_id' => $request->get('role_id'),
            'phone' => $request->get('phone'),
            'uuid' => Str::uuid(),
        ]);

        $user = $user->fresh();

        if($user){
            $url = getenv('PROD_URL').'/register?user='.$user->id;
            $details = [
                // 'url' => $url,
                'name' => $user->name . ' ' . $user->last_name
            ];
            \Mail::to($user->email)->send(new \App\Mail\UserInvitation($details));        

            return $this->sendResponse($user, 'User created succesfully.');
        } else {
            return $this->sendError('Error inviting User.');
        }
    }

    public function getUserEmail($uuid){
        $user = User::where('uuid', $uuid)->first();
        if($user){
            return $this->sendResponse($user, 'Usuario validado correctamente.');
        } else {
            return $this->sendError('Token inválido.');
        }
    }

    public function send_test_email(Request $request){
        $email = $request->get('email');

        $details = [
            'email' => $email,
            'url' => "lol"
        ];

        \Mail::to($email)->send(new \App\Mail\TestEmail($details));        

        return $this->sendResponse([], 'Email enviado correctamente.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $offers = ArticleOffer::where('user_id', $user->id)->count();
        if ($offers > 0) {
            return $this->sendError("You can't delete this user, first delete all the offers.");
        }

        $user->delete();
        return $this->sendResponse([], 'User deleted successfully');
    }
}
