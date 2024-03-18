<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function getAllTeams()
    {
        try {

            $teams = Team::orderBy('points_earned', 'desc')->get();
            return response()->json(["teams" => $teams], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function getSingleTeam(string $id)
    {
        try {

            $team = Team::find($id);

            if (!$team) return response()->json(["error" => "Traženi tim ne postoji. Pokušajte ponovo."], 404, ['status' => 'fail']);

            return response()->json(["team" => $team], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function createNewTeam(CreateTeamRequest $request)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $request->validated();



            $team = Team::create(["name" => $request->name, "team_leader" => $user->id, "num_of_members" => 1, "capacity" => $request->capacity]);
            User::where('id', $user->id)->update(["is_in_team" => true]);

            $user->teams()->attach($team);

            return response()->json(["new_team" => $team], 201, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function getMyTeam()
    {
        try {

            $user = User::checkAuth(Auth::class);
            $teams = $user->teams()->get();

            return response()->json(["my_teams" => $teams], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function joinTeam(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);

            $teamToJoin = Team::find($id);

            if (!$teamToJoin) return response()->json(["error" => "Tim sa ID-em $id ne postoji."], 400, ['status' => 'fail']);

            $teamToJoin->increment('num_of_members');

            User::where('id', $user->id)->update(["is_in_team" => true]);
            $user->teams()->attach($teamToJoin);

            return response()->json(["message" => "Uspješno ste se pridružili timu $teamToJoin->name"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function leaveTeam(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);

            if (!$user->is_in_team) return response()->json(["error" => "Niste dio niti jednog tima."], 400, ['status' => 'fail']);

            $teamToLeave = Team::find($id);

            if (!$teamToLeave) return response()->json(["error" => "Tim sa ID-em $id ne postoji."], 400, ['status' => 'fail']);

            if ($user->id === $teamToLeave->team_leader) return response()->json(["error" => "Ne možete izaći iz vlastitog tima, možete ga samo obrisati."], 400, ['status' => 'fail']);

            $teamToLeave->decrement('num_of_members');

            User::where('id', $user->id)->update(["is_in_team" => false]);
            $user->teams()->detach($teamToLeave);

            return response()->json(["message" => "Uspješno ste izašli iz tima $teamToLeave->name"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function updateTeam(Request $request, string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $rules = [
                "name" => "nullable|unique:teams,name," . $id,
                "points_earned" => "nullable"
            ];

            $team = $user->teams()->find($id);

            if (!$team) return response()->json(["error" => "Tim sa ID-em $id ne postoji. Pokušajte ponovo."], 404, ['status' => 'fail']);
            if ($user->id !== $team->team_leader) return response()->json(["error" => "Ne možete uređivati tim jer niste tim lider."], 400, ['status' => 'fail']);

            $validate = $request->validate($rules);

            Team::where('id', $id)->update($validate);

            return response()->json(["message" => "Tim uspješno ažuriran."], 200, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function deleteTeam(string $id)
    {
        try {

            $user = User::checkAuth(Auth::class);


            $team = $user->teams()->find($id);
            if (!$team) return response()->json(["error" => "Tim sa ID-em $id ne postoji. Pokušajte ponovo."], 404, ['status' => 'fail']);
            if ($user->id !== $team->team_leader) return response()->json(["error" => "Ne možete uređivati tim jer niste tim lider."], 400, ['status' => 'fail']);

            $team->destroy($id);
            User::where('id', $user->id)->update(["is_in_team" => false]);
            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
