<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $APP_URL;

    public function setEnvAppUrl()
    {
        return $this->APP_URL = env('APP_URL');
    }

    public function login(Request $request)
    {
        try {

            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = User::createAuthToken($user, "quizko");
                return response()->json(["user" => $user, "token" => $token], 200, ['status' => 'success']);
            }

            return response()->json(['error' => 'Netočan e-mail ili lozinka.'], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
    public function register(RegisterRequest $request)
    {
        try {

            $request->validated();


            $mailToken = User::generateHexToken();

            $user = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'profile_picture' => User::storeImage($request) ?? 'default.jpg',
                'password' => User::hashPassword(Hash::class, $request['password']),
                'password_confirm' => $request['password_confirm'],
                'confirm_email_token' => $mailToken,
                'api_token' => Str::random(60)
            ]);
            $token = User::createAuthToken($user, "quizko");

            $setUrl = $this->setEnvAppUrl();

            User::sendMail(Mail::class, $user->email, WelcomeMail::class, 'Potvrda e-mail adrese.', "Molimo potvrdite Vašu e-mail adresu tako što ćete kliknuti na sljedeći link: $setUrl/users/confirm-email/$mailToken");

            return response()->json(['message' => 'Registracija uspješna.', "token" => $token], 201);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function confirmMail(string $token)
    {
        try {
            $user = User::checkAuth(Auth::class);

            if ($token !== $user->confirm_email_token) return response()->json(["error" => "Tokeni za potvrdu e-mail adrese se ne podudaraju. Pokušajte ponovo."], 400, ['status' => 'fail']);

            User::where('id', $user->id)->update(['confirm_email_token' => null, 'is_email_verified' => 1, 'email_verified_at' => Carbon::now()]);
            return response()->json(["message" => "E-mail adresa uspješno potvrđena!"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
    public function updatePassword(Request $request)
    {
        try {
            $user = User::checkAuth(Auth::class);
            if (!$user) return response()->json(["error" => "Niste prijavljeni u sustav."], 401, ['status' => 'fail']);

            $rules = [
                "username" => "required|unique:users,username," . $user->id,
                "password" => "required",
                "password_new" => "required|string|min:8",
                "password_confirmation" => "required_with:password_new|same:password_new"
            ];

            $validateData = $request->validate($rules);
            if (!User::comparePassword(Hash::class, $validateData['password'], $user->password)) return response()->json(["error" => "Trenutna lozinka nije ispravna."], 400, ['status' => 'fail']);

            $hashedPassword = User::hashPassword(Hash::class, $validateData['password_new']);
            User::where('id', $user->id)->update(['password' => $hashedPassword], $validateData);

            $token = User::createAuthToken($user, "quizko");

            return response()->json(["message" => "Lozinka uspješno ažurirana.", "token" => $token], 200, ['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
    public function forgotPassword(ForgotPasswordRequest $request)
    {

        try {
            $request->validated();
            $user = User::where('email', $request['email'])->first();
            if (!$user) return response()->json(["error" => "E-mail nije pronađen."], 404, ['status' => 'fail']);

            $resetToken = User::generateHexToken();
            $setUrl = $this->setEnvAppUrl();
            User::sendMail(Mail::class, $user->email, ResetPasswordMail::class, 'Ažuriranje zaboravljene lozinke', "Posjetite ovaj URL za ponovno postavljanje lozinke: $setUrl/users/reset-password/$resetToken");


            User::where('email', $request['email'])->update(['forgot_password_token' => $resetToken]);
            return response()->json(["message" => "E-mail sa tokenom za ponovno postavljanje lozinke poslan je na: $user->email"]);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function resetPassword(ResetPasswordRequest $request, string $token)
    {
        try {

            $request->validated();
            $user = User::where('forgot_password_token', $token)->first();

            if (!$user) return response()->json(["error" => "Korisnik ne postoji."], 404, ['status' => 'fail']);
            if ($token !== $user->forgot_password_token) return response()->json(["error" => "Neispravan token."], 400, ['status' => 'fail']);

            $hashedPassword = User::hashPassword(Hash::class, $request['password_new']);
            User::where('forgot_password_token', $token)->update(["password" => $hashedPassword, "forgot_password_token" => null]);

            return response()->json(["message" => "Lozinka uspješno postavljena. Možete se prijaviti u aplikaciju."], 200, ['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function logOut()
    {
        $user = User::checkAuth(Auth::class);
        $user->tokens()->delete();
        return response()->json(['message' => 'Odjavili ste se iz aplikacije.'], 200, ['status' => 'success']);
    }
}
