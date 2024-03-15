<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{

    //Dohvati sve dostupne kvizove iz baze
    public function index()
    {
        try {
            $quizzes = Quiz::all();
            return response()->json(["data" => $quizzes], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
    //Dohvati jedan kviz iz liste svih kvizova u bazi
    public function showSingleQuiz(string $id)
    {
        try {

            $quiz = Quiz::find($id);

            if (!$quiz) return response(["error" => "Kviz sa ID-em $id ne postoji."], 404, ['status' => 'fail']);

            return response()->json(["data" => $quiz], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
    //Korisnik klikne na "save" na neki kviz iz liste svih kvizova (quizzes.index) => Kviz se sprema u kolekciju "Spremljeni kvizovi"
    public function saveOtherQuiz(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);

            $quiz = Quiz::find($id);
            if (!$quiz) return response()->json(["error" => "Kviz sa ID-em $id ne postoji."], 404, ['status' => 'fail']);

            $saveQuiz = $user->savedQuizzes()->attach($quiz);

            return response()->json(["message" => "Kviz uspješno spremljen u kolekciju."], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Korisnik dohvaća sve kvizove koje je spremio
    public function getOtherQuizzes()
    {
        try {
            $user = User::checkAuth(Auth::class);

            $savedQuizzes = $user->savedQuizzes()->get();
            return response()->json(["saved_quizzes" => $savedQuizzes], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Korisnik brise kviz iz kolekcije "Spremljeni kvizovi"
    public function deleteOtherQuiz(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);

            $quiz = $user->savedQuizzes()->find($id);

            if (!$quiz) return response()->json(["error" => "Kviz sa ID-em $id ne postoji."], 404, ['status' => 'fail']);

            $user->savedQuizzes()->detach($quiz->id);
            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
