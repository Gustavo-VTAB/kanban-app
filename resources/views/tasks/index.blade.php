@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tarefas</h1>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary">Nova Tarefa</a>
    <ul>
        @foreach ($tasks as $task)
            <li>{{ $task->title }} - {{ $task->status }}</li>
        @endforeach
    </ul>
</div>
@endsection
