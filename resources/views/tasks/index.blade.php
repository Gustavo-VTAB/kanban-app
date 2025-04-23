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
              <div class="card shadow-sm rounded-4 mb-4">
                  <div class="card-header bg-light border-0 text-center fw-bold text-primary">
                      {{ $label }}
                  </div>
                  <div class="card-body bg-white" id="{{ $status }}" style="min-height: 200px;">
                      @foreach ($tasks->where('status', $status) as $task)
                          <div class="card task-item p-2 mb-3 shadow-sm border-start border-4 border-primary rounded-3"
                              style="cursor: grab;"
                              data-id="{{ $task->id }}"
                              data-title="{{ $task->title }}"
                              data-description="{{ $task->description }}"
                              data-due-date="{{ $task->due_date }}">
                              <strong>{{ $task->title }}</strong>
                              <p class="mb-0 text-muted small">{{ $task->due_date }}</p>
                          </div>
                      @endforeach
                  </div>
              </div>
          </div>
      @endforeach
  </div>
</div>


<div class="container text-center mt-5">
  <h2 class="mb-3 fw-bold text-secondary">🗓️ Seu Calendário</h2>
  <p class="text-muted">Visualize suas tarefas diretamente no Google Calendar</p>

  <div class="alert alert-info shadow-sm rounded-3 mt-3">
    Para adicionar uma tarefa ao calendário, clique na tarefa e depois em "Adicionar ao Google Calendar".
  </div>

  <div class="calendar-container d-flex justify-content-center my-4">
    <iframe 
      src="https://calendar.google.com/calendar/embed?src=gust.inbug%40gmail.com&ctz=America%2FSao_Paulo"
      style="border:0; width:100%; max-width:900px; height:600px; border-radius:10px;" 
      frameborder="0" 
      scrolling="no"></iframe>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" data-id="" aria-labelledby="taskModalLabel" aria-hidden="true">
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
          
          <form action="{{ route('tasks.sync', $task->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">Adicionar ao Google Calendar</button>
          </form>
          
          
          <div class="modal-footer">
            <button id="deleteTaskButton" class="btn btn-danger">Deletar Tarefa</button>
            <button class="btn btn-primary" id="openEditModal">Editar Tarefa</button>
        </div>
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
<!-- Modal de Edição -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editTaskForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTaskModalLabel">Editar Tarefa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="task_id" id="editTaskId">
          <div class="mb-3">
            <label for="editTitle" class="form-label">Título</label>
            <input type="text" name="title" class="form-control" id="editTitle" required>
          </div>
          <div class="mb-3">
            <label for="editDescription" class="form-label">Descrição</label>
            <textarea name="description" class="form-control" id="editDescription"></textarea>
          </div>
          <div class="mb-3">
            <label for="editDueDate" class="form-label">Data Limite</label>
            <input type="date" name="due_date" class="form-control" id="editDueDate">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salvar</button>
        </div>
      </div>
    </form>
  </div>
</div>

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 🔁 Drag & Drop com Sortable
  ['to_do', 'in_progress', 'done'].forEach(function (status) {
    new Sortable(document.getElementById(status), {
      group: 'shared',
      animation: 150,
      onEnd: function (evt) {
        const taskId = evt.item.dataset.id;
        const newStatus = evt.to.id;

        fetch("{{ route('tasks.updateStatus') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ id: taskId, status: newStatus })
        });
      }
    });
  });

  // 💬 Abertura de modal de detalhes da tarefa + configuração do botão de editar
  document.querySelectorAll('.card.p-2.mb-3').forEach(function (container) {
    container.addEventListener('click', function (event) {
      const item = event.target.closest('.task-item');
      if (!item) return;

      // Preenche modal de visualização
      document.getElementById('modalTitle').innerText = item.dataset.title;
      document.getElementById('modalDescription').innerText = item.dataset.description || 'Sem descrição';
      document.getElementById('modalDueDate').innerText = item.dataset.dueDate || 'Sem data limite';
      document.getElementById('taskModal').dataset.id = item.dataset.id;
      document.getElementById('deleteTaskButton').setAttribute('data-id', item.dataset.id);

      // Abre modal de detalhes
      new bootstrap.Modal(document.getElementById('taskModal')).show();

      // Configura botão "Editar"
      document.getElementById('openEditModal').onclick = function () {
        document.getElementById('editTaskId').value = item.dataset.id;
        document.getElementById('editTitle').value = item.dataset.title;
        document.getElementById('editDescription').value = item.dataset.description || '';
        document.getElementById('editDueDate').value = item.dataset.dueDate || '';

        forceCloseModals();
        document.getElementById('editTaskForm').action = `/tasks/${item.dataset.id}`;
        new bootstrap.Modal(document.getElementById('editTaskModal')).show();
      };
    });
  });

  // 🗑️ Exclusão: abre modal de confirmação
  let taskIdToDelete = null;
  document.getElementById('deleteTaskButton').addEventListener('click', function () {
    taskIdToDelete = this.getAttribute('data-id');
    bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
    forceCloseModals();
    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
  });

  // 🗑️ Exclusão: confirmação
  document.getElementById('confirmDeleteButton').addEventListener('click', function () {
    if (taskIdToDelete) {
      deleteTask(taskIdToDelete);
    }
  });

  // 🔁 Função de deletar via AJAX
  function deleteTask(taskId) {
    fetch(`/tasks/${taskId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
      .then(response => {
        if (response.ok) {
          // Remove visualmente
          document.querySelector(`[data-id="${taskId}"]`)?.remove();
          
          forceCloseModals();

          // Atualiza o iframe do Google Calendar
          document.querySelector('iframe')?.setAttribute('src', document.querySelector('iframe')?.getAttribute('src'));

        } else {
          alert('Erro ao deletar tarefa.');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
      });
  }

  // 💥 Remove backdrop e estado de modal travado
  function forceCloseModals() {
    document.querySelectorAll('.modal.show').forEach(modal => {
      bootstrap.Modal.getInstance(modal)?.hide();
    });

    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
      backdrop.remove();
    });

    document.body.classList.remove('modal-open');
    document.body.style = '';
  }

  // ⛑️ Fallback: se o ESC for pressionado e sobrar backdrop, remove
  window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      forceCloseModals();
    }
  });
});
</script>

@endsection
