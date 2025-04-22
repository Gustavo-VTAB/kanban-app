@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Criar Tarefa</h1>

    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="title">Título</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description">Descrição</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="due_date">Data Limite</label>
            <input type="date" name="due_date" class="form-control">
        </div>

        <div class="mb-3">
            <label for="assigned_to">Delegar para</label>
            <select name="assigned_to" class="form-control">
                <option value="">Nenhum</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>
@endsection