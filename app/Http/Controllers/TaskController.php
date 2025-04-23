<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; 
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class TaskController extends Controller
{
    /** 
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = \App\Models\User::all();
        return view('tasks.create', compact('users'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
    
        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->user_id = auth()->id();
        $task->assigned_to = $request->assigned_to;

        if (Session::has('google_token')) {
            $client = new Google_Client();
            $client->setAccessToken(Session::get('google_token'));

            $service = new Google_Service_Calendar($client);

            $event = new Google_Service_Calendar_Event([
                'summary' => $task->title,
                'description' => $task->description,
                'start' => [
                    'date' => $task->due_date,
                    'timeZone' => 'America/Sao_Paulo',
                ],
                'end' => [
                    'date' => $task->due_date,
                    'timeZone' => 'America/Sao_Paulo',
                ],
            ]);

            $createdEvent = $service->events->insert('primary', $event);

            // (Opcional) Salvar o ID do evento para editar/excluir depois
            $task->google_event_id = $createdEvent->getId();
            $task->save();
        }

        
        return redirect()->route('tasks.index')->with('success', 'Tarefa criada com sucesso!');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $task = Task::find($id);

        if ($task) {
            $task->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Tarefa não encontrada'], 404);
    }

}
