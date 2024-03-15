<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuizRequest;
use App\Models\Quiz;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function showMe()
    {
        try {
            $user = User::checkAuth(Auth::class);

            if (!$user) return response()->json(["error" => "Niste prijavljeni u sustav."], 400, ['status' => 'fail']);

            $userData = User::findOrFail($user->id);

            if (!$userData) return response()->json(["error" => "Korisnik ne postoji."], 404, ['status' => 'fail']);
            return response()->json(["user" => $userData], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    public function deactivateMe()
    {
        try {
            $user = User::checkAuth(Auth::class);

            if (!$user) return response()->json(["error" => "Niste prijavljeni u sustav."], 401, ['status' => 'fail']);

            User::where('id', $user->id)->update(['is_account_active' => 0]);
            return response()->json(["message" => "Račun uspješno deaktiviran. Račun možete aktivirati ponovo kada poželite."], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function activateMe()
    {
        try {
            $user = User::checkAuth(Auth::class);

            if (!$user) return response()->json(["error" => "Niste prijavljeni u sustav."], 401, ['status' => 'fail']);

            User::where('id', $user->id)->update(['is_account_active' => 1]);
            return response()->json(["message" => "Uspješno ste reaktivirali svoj korisnički račun"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function updateMe(Request $request)
    {
        //Treba dodat sliku profila
        try {
            $user = User::checkAuth(Auth::class);
            if (!$user) return response()->json(["error" => "Niste prijavljeni u sustav."], 401, ['status' => 'fail']);

            $rules = [
                "username" => "nullable|unique:users,username," . $user->username,
            ];
            $validateData = $request->validate($rules);

            User::where('id', $user->id)->update($validateData);



            $token = User::createAuthToken($user, "quizko");

            return response()->json(["message" => "Profil uspješno ažuriran.", "token" => $token], 200, ['status' => 'success'])->withCookie(cookie("quizko", $token));
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    //Dohvati sve moje kvizove
    public function myQuizzes()
    {
        try {

            $user = User::checkAuth(Auth::class);
            $myQuizzes = $user->myQuizzes()->get();
            return response()->json(["my_quizzes" => $myQuizzes], 200, ['status']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Iz liste dohvacenih mojih kvizova, dohvati jedan
    public function myQuiz(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $quiz = $user->myQuizzes()->find($id);

            if (!$quiz) return response()->json(["error" => "Kviz sa ID-em $id ne postoji."], 404, ['status' => 'fail']);

            return response()->json(["my_quiz" => $quiz], 200, ['status']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    //Spremi kviz u kolekciju "Moji kvizovi"
    public function storeQuiz(CreateQuizRequest $request)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $request->validated();

            if ($request->hasFile('picture') && $request->file('picture')->isValid()) {
                $file = $request->file('picture');
                $newImageName = uniqid() . '-' . $request->name . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $newImageName);
            } else $newImageName = null;


            $description = $request->filled('description') ? $request['description'] : 'Nema opisa.';
            $is_quiz_locked = $request->filled('is_quiz_locked') ? $request['is_quiz_locked'] : 0;

            $newQuiz = Quiz::create([
                "name" => $request['name'],
                "description" => $description,
                "picture" => $newImageName,
                "is_quiz_locked" => $is_quiz_locked,
                "category_id" => $request['category_id'],
                "starts_at" => $request['starts_at'],
                "ends_at" => $request['ends_at']
            ]);

            $user->myQuizzes()->attach($newQuiz);
            $user->savedQuizzes()->attach($newQuiz->id);
            return response()->json(["new_quiz" => $newQuiz], 201, ['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Updatea kviz u mojoj kolekciji
    public function updateMyQuiz(Request $request, string $quizId)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $quiz = $user->myQuizzes()->find($quizId);

            if (!$quiz) return response()->json(["error" => "Kviz sa ID-em $quizId ne postoji u kolekciji vlastitih kvizova."], 404, ['status' => 'fail']);


            $rulesQuiz = [
                "name" => "nullable|unique:quizzes,name," . $quizId,
                "description" => "nullable|string|max:255",
                "picture" => ['nullable', 'mimes:jpg,png,jpeg', 'max:5048'],
                "is_quiz_locked" => "nullable|boolean",
                "category_id" => "nullable|integer",
                "starts_at" => "nullable|date",
                "ends_at" => "nullable|date"
            ];

            $validatedData = $request->validate($rulesQuiz);

            $quiz->update([
                'name' => $request->filled('name') ? $validatedData['name'] : $quiz->name,
                'description' => $request->filled('description') ? $validatedData['description'] : $quiz->description,
                'picture' => $request->filled('picture') ? $validatedData['picture'] : $quiz->picture,
                'is_quiz_locked' => $request->filled('is_quiz_locked') ? $validatedData['is_quiz_locked'] : $quiz->is_quiz_locked,
                'category_id' => $request->filled('category_id') ?  $validatedData['category_id'] : $quiz->category_id,
                'starts_at' => $request->filled('starts_at') ? $validatedData['starts_at'] : $quiz->starts_at,
                'ends_at' => $request->filled('ends_at') ? $validatedData['ends_at'] : $quiz->starts_at,
            ]);

            return response()->json(["updated_quiz" => $quiz], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Brise kviz iz moje kolekcije
    public function deleteMyQuiz(string $quizId)
    {
        try {
            $user = User::checkAuth(Auth::class);

            $quiz = $user->myQuizzes()->find($quizId);
            if (!$quiz) return response()->json(["error" => "Kviz sa ID-em $quizId ne postoji u kolekciji vlastitih kvizova."], 404, ['status' => 'fail']);

            Quiz::destroy($quiz->id);
            $user->savedQuizzes()->detach($quiz->id);
            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
