<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Models\Quiz;
use App\Models\Scoreboard;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{

    //Dohvati sve timove iz baze
    public function getAllTeams()
    {
        try {

            $teams = Team::orderBy('points_earned', 'desc')->get();
            return response()->json(["teams" => $teams], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Dohvati jedan tim iz baze
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

    //Kreiraj novi tim
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

    //Dohvati svoj tim (u kojem je korisnik trenutno)
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

    //Pridruži se timu
    public function joinTeam(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);

            $teamToJoin = Team::find($id);

            if (!$teamToJoin) return response()->json(["error" => "Tim sa ID-em $id ne postoji."], 400, ['status' => 'fail']);
            if ($teamToJoin->num_of_members === $teamToJoin->capacity) return response()->json(["error" => "Ne možete se pridružiti timu, tim je popunjen!"], 400, ['status' => 'fail']);

            $teamToJoin->increment('num_of_members');

            User::where('id', $user->id)->update(["is_in_team" => true]);
            $user->teams()->attach($teamToJoin);

            return response()->json(["message" => "Uspješno ste se pridružili timu $teamToJoin->name"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Izadi iz tima
    public function leaveTeam(string $id)
    {
        try {
            $user = User::checkAuth(Auth::class);

            if (!$user->is_in_team) return response()->json(["error" => "Niste dio niti jednog tima."], 400, ['status' => 'fail']);

            $teamToLeave = Team::find($id);

            if (!$teamToLeave) return response()->json(["error" => "Tim sa ID-em $id ne postoji."], 400, ['status' => 'fail']);
            if ($user->id === $teamToLeave->team_leader) return response()->json(["error" => "Ne možete izaći iz vlastitog tima, možete ga samo obrisati."], 403, ['status' => 'fail']);
            if ($user->is_currently_in_quiz) return response()->json(["error" => "Ne možete izaći iz tima jer trenutno sudjelujete u kvizu."], 403, ['status' => 'fail']);

            $teamToLeave->decrement('num_of_members');

            User::where('id', $user->id)->update(["is_in_team" => false]);
            $user->teams()->detach($teamToLeave);

            return response()->json(["message" => "Uspješno ste izašli iz tima $teamToLeave->name"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Azuriraj tim (samo tim lider)
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
            if ($user->is_currently_in_quiz) return response()->json(["error" => "Ne možete uređivati tim jer trenutno sudjelujete u kvizu."], 403, ['status' => 'fail']);

            $validate = $request->validate($rules);

            Team::where('id', $id)->update($validate);

            return response()->json(["message" => "Tim uspješno ažuriran."], 200, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Prikazi sve clanove nekog tima
    public function displayTeamMembers(string $id)
    {
        try {
            $usersInTeam = Team::find($id);

            if (!$usersInTeam) return response()->json(["error" => "Tim sa ID-em $id ne postoji."], 404, ['status' => 'fail']);

            $users = $usersInTeam->users;

            return response()->json(["members" => $users], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Obrisi tim (samo tim lider => svi korisnici koji su bili u ovom timu updatea im se is_in_team = false, pa se mogu pridruziti nekom drugom timu)
    //Kviz se ne brise iz baze zbog bodovnih ljestvica
    public function deleteTeam(string $id)
    {
        try {

            $user = User::checkAuth(Auth::class);


            $team = $user->teams()->find($id);
            if (!$team) return response()->json(["error" => "Tim sa ID-em $id ne postoji. Pokušajte ponovo."], 404, ['status' => 'fail']);
            if ($user->id !== $team->team_leader) return response()->json(["error" => "Ne možete uređivati tim jer niste tim lider."], 403, ['status' => 'fail']);
            if ($user->is_currently_in_quiz) return response()->json(["error" => "Ne možete obrisati tim jer trenutno sudjelujete u kvizu."], 403, ['status' => 'fail']);

            $usersInTeam = Team::find($id);
            $users = $usersInTeam->users;

            foreach ($users as $user) {
                $user->is_in_team = false;
                $user->save();
            }

            $team->destroy($id);
            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    //Pridruži se kvizu
    public function joinQuiz(string $teamId, string $quizId)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $team = $user->teams()->find($teamId);

            if (!$team) return response()->json(["error" => "Tim sa ID-em $teamId ne postoji."], 404, ['status' => 'fail']);

            if (!$user->is_in_team) return response()->json(["error" => "Ne možete se pridružiti kvizu jer niste dio tima."], 403, ['status' => 'fail']);
            if ($user->id !== $team->team_leader) return response()->json(["error" => "Ne možete ući u kviz jer niste tim lider."], 403, ['status' => 'fail']);

            $quizToJoin = Quiz::find($quizId);
            if (!$quizToJoin) return response()->json(["error" => "Kviz sa ID-em $quizId ne postoji."], 404, ['status' => 'fail']);

            //Nakon joinanja kviza, svim userima iz tima se state is_currently_in_quiz mjenja u true
            $usersToUpdate = $team->users;
            foreach ($usersToUpdate as $user) {
                $user->is_currently_in_quiz = true;
                $user->save();
            }

            //Postavi state trenutnog tima da je u kvizu i u kojem je kvizu
            $team->is_currently_in_quiz = true;
            $team->quiz_session_id = $quizToJoin->id;
            $team->save();


            //Dodaj tim na scoreboard trenutnog kviza
            $scoreboard = Scoreboard::where('id', $quizToJoin->scoreboard_id)->first();
            $scoreboard->teams()->attach($team);
            $scoreboard->save();

            return response()->json(["message" => "Uspješno sudjelujete u kvizu $quizToJoin->name"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function leaveQuiz(string $teamId, string $quizId)
    {
        try {
            $user = User::checkAuth(Auth::class);
            $team = $user->teams()->find($teamId);

            if (!$team) return response()->json(["error" => "Tim sa ID-em $teamId ne postoji."], 404, ['status' => 'fail']);

            if (!$user->is_currently_in_quiz) return response()->json(["error" => "Ne sudjelujete u kvizu."], 400, ['status' => 'fail']);
            if ($user->id !== $team->team_leader) return response()->json(["error" => "Samo tim lider može izaći iz kviza, nakon što on izađe, svi članovi tima će biti automatski izbačeni iz kviza."], 403, ['status' => 'fail']);

            $quizToLeave = Quiz::find($quizId);
            if (!$quizToLeave) return response()->json(["error" => "Kviz sa ID-em $quizId ne postoji."], 404, ['status' => 'fail']);

            //Postavi state korisnika da nije vise u kvizu
            $usersToUpdate = $team->users;
            foreach ($usersToUpdate as $user) {
                $user->is_currently_in_quiz = false;
                $user->save();
            }

            //Postavi state trenutnog tima da vise nije u kvizu
            $team->is_currently_in_quiz = false;
            $team->quiz_session_id = null;
            $team->save();

            //Makni tim sa scoreboarda trenutnog kviza
            $scoreboard = Scoreboard::where('id', $quizToLeave->scoreboard_id)->first();
            $scoreboard->teams()->detach($team->id);
            $scoreboard->save();

            return response()->json(["message" => "Izašli ste iz kviza $quizToLeave->name"], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
