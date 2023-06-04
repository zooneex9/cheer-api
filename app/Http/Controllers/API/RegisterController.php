<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Category;
use App\Models\Business;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Http\Controllers\API\BaseController as BaseController;

class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required',
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $u = User::where("email", $input['email'])->first();
        if($u){
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $u->update($request->all());

                $n_password = $request->get('newPassword');
                $u->password = bcrypt($n_password);

                $u->save();

                return $this->sendResponse([], 'Usuario actualizado correctamente.');
            } else {
                return $this->sendError('Este usuario ya existe.', ['error'=>'Unauthorised'], 401);
            }
        } else {

            $password = $request->get('password');

            $user = User::create([
                'password' => bcrypt(Str::random(15)),
                'role_id' => 2,
                'email' => $request->get('email'),
                'name' => $request->get('name'),
                'last_name' => $request->get('last_name'),
                'uuid' => Str::uuid()
            ]);

            $user = $user->fresh();

            $details = [
                'url' => getenv('PROD_URL').'/crear-cuenta'.'?user='.$user->uuid,
                'name' => $user->name . ' ' . $user->last_name,
                'business' => $b->name
            ];

            \Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($details));

            // $details = [
            //     'url' => getenv('PROD_URL').'/backoffice/members',
            //     'name' => $user->name . ' ' . $user->last_name
            // ];

            // \Mail::to(getenv('ADMIN_EMAIL'))->send(new \App\Mail\UserRegistered($details));  

            return $this->sendResponse($user, 'Te has registrado correctamente.');
        }

        /*
        $tmpPassword = $this->generateRandomString(10);
        $input['password'] = bcrypt($tmpPassword);

        $user = User::create([
            'name' => $request->get('name'),
            'last_name' => $request->get('last_name'),
            'mother_last_name' => $request->get('mother_last_name'),

            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'role_id' => $request->get('role_id')
        ]);

        $success['token'] =  $user->createToken('cms')->accessToken;
        $success['name'] =  $user->name;
        */


    }

    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            
            $success['token'] =  $user->createToken('cms')->accessToken;
            $success['user'] =  $user;

            return $this->sendResponse($success, 'Usuario loggeado correctamente.');
        }
        else{
            return $this->sendError('Credenciales incorrectas.', ['error'=>'Unauthorised'], 401);
        }
    }
}
