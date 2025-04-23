@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tarefas & Kanban</h1>

    <a href="{{ route('tasks.create') }}" class="btn btn-primary mb-4">Nova Tarefa</a>

    @if (!Session::has('google_token'))
        <a href="{{ route('google.auth') }}" class="btn btn-danger mb-3">
            <i class="bi bi-google"></i> Conectar com Google
        </a>
    @else
        <div class="alert alert-success mb-3">
            Conta Google conectada com sucesso ✅
        </div>
    @endif


    <div class="row">
        @foreach (['to_do' => 'A Fazer', 'in_progress' => 'Em Progresso', 'done' => 'Concluído'] as $status => $label)
            <div class="col-md-4">
                <h3>{{ $label }}</h3>
                <div class="card p-2 mb-3" id="{{ $status }}">
                    @foreach ($tasks->where('status', $status) as $task)
                    <div class="card p-2 mb-2 task-item" 
                        data-id="{{ $task->id }}"
                        data-title="{{ $task->title }}"
                        data-description="{{ $task->description }}"
                        data-due-date="{{ $task->due_date }}">
                        <strong>{{ $task->title }}</strong>
                        <p class="mb-0">{{ $task->due_date }}</p>
                    </div>

                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="taskModalLabel">Detalhes da Tarefa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <h4 id="modalTitle"></h4>
        <p id="modalDescription"></p>
        <p><strong>Data Limite:</strong> <span id="modalDueDate"></span></p>

        <button id="deleteTaskButton" class="btn btn-danger">Deletar Tarefa</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirmar Exclusão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja excluir esta tarefa?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="confirmDeleteButton" class="btn btn-danger">Excluir</button>
      </div>
    </div>
  </div>
</div>


@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
 // Inicializa Sortable nas colunas
['to_do', 'in_progress', 'done'].forEach(function(status) {
    new Sortable(document.getElementById(status), {
        group: 'shared',
        animation: 150,
        onEnd: function (evt) {
            let taskId = evt.item.dataset.id;
            let newStatus = evt.to.id;

            fetch("{{ route('tasks.updateStatus') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: taskId,
                    status: newStatus
                })
            });
        }
    });
});

// Delegação de evento de clique no container pai
document.querySelectorAll('.card.p-2.mb-3').forEach(function(container) {
    container.addEventListener('click', function(event) {
        // só continua se clicou em um .task-item
        if (event.target.closest('.task-item')) {
            let item = event.target.closest('.task-item');

            document.getElementById('modalTitle').innerText = item.dataset.title;
            document.getElementById('modalDescription').innerText = item.dataset.description || 'Sem descrição';
            document.getElementById('modalDueDate').innerText = item.dataset.dueDate || 'Sem data limite';

            var taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
            
            document.getElementById('deleteTaskButton').setAttribute('data-id', item.dataset.id);
            document.getElementById('taskModal').style.display = 'block';

            taskModal.show();
        }
    });
});


let taskIdToDelete = null;

document.getElementById('deleteTaskButton').addEventListener('click', function () {
  taskIdToDelete = this.getAttribute('data-id');

  var taskModal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
  taskModal.hide();

  var confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
  confirmModal.show();
});

document.getElementById('confirmDeleteButton').addEventListener('click', function () {
  if (taskIdToDelete) {
    deleteTask(taskIdToDelete);
  }
});

function deleteTask(taskId) {
  fetch("/tasks/" + taskId, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  })
    .then(response => {
      if (response.ok) {
        var taskModal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
        var confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));

        taskModal.hide();
        confirmModal.hide();

        document.querySelector('[data-id="' + taskId + '"]').remove();
      } else {
        alert('Erro ao deletar.');
      }
    })
    .catch(error => {
      console.error('Erro:', error);
    });
}




</script>
@endsection
