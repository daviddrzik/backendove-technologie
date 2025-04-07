<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
//    public function index()
//    {
//        $notes = DB::table('notes')
//            ->orderBy('updated_at', 'desc')
//            ->get();
//        return response()->json($notes);
//    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Ak je admin, vrátime všetky poznámky
            $notes = Note::with(['categories', 'user'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            // Ak je normálny používateľ, vrátime iba jeho poznámky
            $notes = Note::with(['categories'])
                ->where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return response()->json($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
//    public function store(Request $request)
//    {
//        $note = DB::table('notes')->insert([
//            'user_id' => $request->user_id,
//            'title' => $request->title,
//            'body' => $request->body,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//
//        if ($note) {
//            return response()->json(['message' => 'Poznámka bola vytvorená'], Response::HTTP_CREATED);
//        } else {
//            return response()->json(['message' => 'Poznámka nebola vytvorená'], Response::HTTP_FORBIDDEN);
//        }
//    }

    public function store(Request $request)
    {
        try {
            // Validácia vstupu
            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'body' => 'required|string',
                'categories' => 'array|max:3',  // Očakáva pole kategórií
                'categories.*' => 'exists:categories,id' // Každé ID kategórie musí existovať v DB
            ]);

            // Vytvorenie poznámky
            $note = Note::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'body' => $validated['body']
            ]);

            // Priradenie kategórií, ak boli zadané
            if (!empty($validated['categories'])) {
                $note->categories()->sync($validated['categories']);
            }

            // Načítanie poznámky aj s kategóriami a používateľom
            $note->load(['user', 'categories']);

            return response()->json([
                'message' => 'Poznámka bola vytvorená',
                'note' => $note
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            // Ak validácia zlyhá, vrátime chybové hlásenie
            return response()->json([
                'message' => 'Chyba pri vytváraní poznámky',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $note = Note::with(['user', 'categories'])->find($id);
        } else {
            $note = Note::with(['categories'])
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->first();
        }

        if (!$note) {
            return response()->json(['message' => 'Poznámka nebola nájdená alebo nemáte oprávnenie'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($note);
    }


    /**
     * Update the specified resource in storage.
     */
//    public function update(Request $request, $id)
//    {
//        $updated = DB::table('notes')->where('id', $id)->update([
//            'title' => $request->title,
//            'body' => $request->body,
//            'updated_at' => now(),
//        ]);
//
//        if ($updated) {
//            return response()->json(['message' => 'Poznámka bola aktualizovaná'], Response::HTTP_OK);
//        } else {
//            return response()->json(['message' => 'Nič sa nezmenilo'], Response::HTTP_OK);
//        }
//    }

    public function update(Request $request, $id)
    {
        try {
            // Validácia vstupu
            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'body' => 'required|string',
                'categories' => 'array|max:3',
                'categories.*' => 'exists:categories,id'
            ]);

            // Získanie prihláseného používateľa
            $user = auth()->user();

            // Hľadáme poznámku, buď ako admin, alebo podľa používateľa
            if ($user->isAdmin()) {
                $note = Note::with(['user', 'categories'])->find($id);
            } else {
                $note = Note::with(['categories'])
                    ->where('user_id', $user->id)
                    ->where('id', $id)
                    ->first();
            }

            // Ak poznámka neexistuje alebo používateľ nemá oprávnenie, vrátime chybu
            if (!$note) {
                return response()->json(['message' => 'Poznámka nebola nájdená alebo nemáte oprávnenie'], Response::HTTP_NOT_FOUND);
            }

            // Aktualizácia iba tých polí, ktoré sú v requeste
            $note->update([
                'title' => $validated['title'],
                'body' => $validated['body']
            ]);

            // Priradenie kategórií, ak boli zadané
            if (isset($validated['categories'])) {
                $note->categories()->sync($validated['categories']);
            }

            // Načítanie poznámky aj s kategóriami a používateľom
            $note->load(['user', 'categories']);

            return response()->json([
                'message' => 'Poznámka bola aktualizovaná',
                'note' => $note
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri aktualizácii poznámky',
                'errors' => $e->getMessage() // Chybová správa pre debugging
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
//    public function destroy($id)
//    {
//        $deleted = DB::table('notes')->where('id', $id)->delete();
//
//        if ($deleted) {
//            return response()->json(['message' => 'Poznámka bola vymazaná'], Response::HTTP_OK);
//        } else {
//            return response()->json(['message' => 'Poznámka nebola nájdená'], Response::HTTP_NOT_FOUND);
//        }
//    }

    public function destroy($id)
    {
        $user = auth()->user();

        // Získame poznámku
        if ($user->isAdmin()) {
            // Admin môže vymazať akúkoľvek poznámku
            $note = Note::find($id);
        } else {
            // Bežný používateľ môže vymazať len svoje poznámky
            $note = Note::where('user_id', $user->id)->find($id);
        }

        // Ak poznámka neexistuje alebo nemá oprávnenie, vrátime chybu
        if (!$note) {
            return response()->json(['message' => 'Poznámka nebola nájdená alebo nemáte oprávnenie'], Response::HTTP_NOT_FOUND);
        }

        // Vymazanie poznámky
        $note->delete();

        return response()->json(['message' => 'Poznámka bola vymazaná']);
    }


    /**
     * Vlastné metódy
     */

    // Získanie poznámok s menami používateľov
    public function notesWithUsers()
    {
        $notes = DB::table('notes')
            ->join('users', 'notes.user_id', '=', 'users.id')
            ->select('notes.*', 'users.name as user_name')
            ->get();

        return response()->json($notes);
    }

    // Počet poznámok pre každého používateľa
    public function usersWithNoteCount()
    {
        $users = DB::table('users')
            ->select('users.id', 'users.name')
            ->selectSub(function ($query) {
                $query->from('notes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('notes.user_id', 'users.id');
            }, 'note_count')
            ->get();

        return response()->json($users);
    }

    // Fulltextové vyhľadávanie v poznámkach
//    public function searchNotes(Request $request)
//    {
//        $query = $request->query('q');
//
//        if (empty($query)) {
//            return response()->json(['message' => 'Musíte zadať dopyt na vyhľadávanie'], Response::HTTP_BAD_REQUEST);
//        }
//
//        $notes = DB::table('notes')
//            ->where('title', 'like', '%' . $query . '%')
//            ->orWhere('body', 'like', '%' . $query . '%')
//            ->get();
//
//        if ($notes->isEmpty()) {
//            return response()->json(['message' => 'Žiadne poznámky sa nenašli'], Response::HTTP_NOT_FOUND);
//        }
//
//        return response()->json($notes);
//    }

    public function searchNotes(Request $request)
    {
        $query = $request->query('q');

        if (empty($query)) {
            return response()->json(['message' => 'Musíte zadať dopyt na vyhľadávanie'], Response::HTTP_BAD_REQUEST);
        }

        $notes = Note::searchByTitleOrBody($query); // Použitie vlastnej metódy z modelu

        if ($notes->isEmpty()) {
            return response()->json(['message' => 'Žiadne poznámky sa nenašli'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($notes);
    }


    //Počet poznámok podľa používateľa
    public function usersWithNotesCount()
    {
        $users = DB::table('notes')
            ->join('users', 'notes.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('COUNT(notes.id) as note_count'))
            ->groupBy('users.id', 'users.name')
            ->having('note_count', '>', 1)
            ->orderByDesc('note_count')
            ->get();

        return response()->json($users);
    }

    //Najdlhšia a najkratšia poznámka
    public function longestAndShortestNote()
    {
        $longest = DB::table('notes')
            ->select('id', 'title', 'body', DB::raw('LENGTH(body) as length'))
            ->orderByDesc('length')
            ->first();

        $shortest = DB::table('notes')
            ->select('id', 'title', 'body', DB::raw('LENGTH(body) as length'))
            ->orderBy('length')
            ->first();

        return response()->json([
            'longest' => $longest,
            'shortest' => $shortest
        ]);
    }

    //Počet poznámok za posledných 7 dní
    public function notesLastWeek()
    {
        $count = DB::table('notes')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return response()->json(['last_week_notes' => $count]);
    }

}
