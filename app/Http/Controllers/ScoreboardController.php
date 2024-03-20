<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateScoreboardRequest;
use App\Models\Scoreboard;
use Exception;
use Illuminate\Http\Request;

class ScoreboardController extends Controller
{

    public function index()
    {
        try {
            $scoreboards = Scoreboard::all();
            return response()->json(["scoreboards" => $scoreboards], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }



    public function store(CreateScoreboardRequest $request)
    {
        try {
            $request->validated();

            $newScoreboard = Scoreboard::create([
                "name" => $request->name
            ]);

            return response()->json(["new_scoreboard" => $newScoreboard], 201, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    public function show(string $sbId)
    {
        try {

            $scoreboard = Scoreboard::where('id', $sbId)->first();
            $teams = $scoreboard->teams()
                ->orderBy('points_earned', 'desc')
                ->get()
                ->select('id', 'name', 'points_earned');


            return response()->json(["scoreboard" => $teams], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }



    public function update(Request $request, string $id)
    {
        try {

            $rules = [
                "name" => "nullable|max:60|unique:scoreboards,name," . $id,
            ];

            $validate = $request->validate($rules);

            Scoreboard::where('id', $id)->update($validate);

            return response()->json(["message" => "Bodovna ljestvica uspješno ažurirana."], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }


    public function destroy(string $id)
    {
        try {
            Scoreboard::destroy($id);
            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
