<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PasswordResetController extends BaseController
{
    public function create(Request $request)
    {
        $request->validate([  'email' => 'required|string|email' ]);
        $user = User::where('email', $request->email)->first();

        if (!$user){
            return $this->sendError('No se encontró un usuario con este correo.' );
        }

        $passwordReset = PasswordReset::updateOrCreate( ['email' => $user->email], [ 'email' => $user->email, 'token' => Str::random(60)]);
        if ($user && $passwordReset){
            $url = getenv('PROD_URL').'/password/reset?token='.$passwordReset['token'];

            $details = [
                'url' => $url,
                'name' => $user->name
            ];

            \Mail::to($user->email)->send(new \App\Mail\CreateResetPassword($details));

            // app('App\Http\Controllers\API\SendGridController') ->passwordResetRequest($user, $url);
        }

        return $this->sendResponse([], 'Te envíamos un correo para recuperar tu contraseña.');
    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset){
            return $this->sendError('Tu token es inválido.');
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return $this->sendError("Tu token ha expirado.");
        }
        return $this->sendResponse($passwordReset->email, 'Token validado.');
    }

    public function reset(Request $request)
    {
        $request->validate([ 'email' => 'required|string|email', 'password' => 'required|string|confirmed', 'token' => 'required|string']);
        $passwordReset = PasswordReset::where([ ['token', $request->token], ['email', $request->email] ])->first();

        if (!$passwordReset){
            return $this->sendError('El token es inválido.');
        }

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user){
            return $this->sendError('No se encontró un usuario con este correo.');
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();

        //TODO: enviar correo de password cambiado
        //app('App\Http\Controllers\API\SendGridController') ->passwordResetSuccess($user);

        return $this->sendResponse($user, 'Contraseña actualizada correctamente.');
    }
}
