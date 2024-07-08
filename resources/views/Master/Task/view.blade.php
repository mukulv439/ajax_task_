<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    <div class="container">
    <div class="logout-btn">
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
        <div>
            <h5>Todo List</h5>
        </div>
        <div class="add-task d-flex justify-content-between">
            <input type="text" id="taskInput" placeholder="Enter task...">
            <input type="text" id="descriptionInput" placeholder="Enter description..."> <!-- New input for description -->
            <input type="submit" value="Add Task" onclick="addTask()">
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Sno</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="taskList">
                <!-- Tasks will be added dynamically here -->
            </tbody>
        </table>

        <div id="confirmationModal" class="confirmation-modal">
            <p>Are you sure you want to delete this task?</p>
            <button onclick="deleteTaskConfirmed()" id="confirmDeleteBtn">Delete</button>
            <button class="cancel-btn" onclick="closeConfirmationModal()">Cancel</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let tasks = [];
        let taskToDelete = null;

        // Function to fetch tasks from backend
        function fetchTasks() {
            $.ajax({
                url: '/tasks',
                method: 'GET',
                success: function(response) {
                    tasks = response.tasks;
                    renderTasks(tasks);
                },
                error: function(error) {
                    console.error('Error fetching tasks:', error);
                }
            });
        }

        // Function to render tasks dynamically
        function renderTasks(tasksToRender) {
            const taskList = $('#taskList');
            taskList.empty();

            tasksToRender.forEach((task, index) => {
                const taskRow = $(`
                    <tr class="${task.status === 'completed' ? 'task-completed' : ''}">
                        <td>${index + 1}</td>
                        <td>${task.title}</td>
                        <td>${task.description}</td>
                        <td>${task.status}</td>
                        <td>
                            <input type="checkbox" onchange="toggleCompletion(${task.id}, this.checked)" ${task.status === 'completed' ? 'checked' : ''}>
                            <button class="delete-btn" onclick="deleteTask(${task.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                taskList.append(taskRow);
            });
        }

        // Function to add a new task
        function addTask() {
            const taskInput = $('#taskInput');
            const descriptionInput = $('#descriptionInput');

            const taskText = taskInput.val().trim();
            const descriptionText = descriptionInput.val().trim();

            if (taskText === '') return alert("Task title cannot be empty");

            $.ajax({
                url: '/tasks',
                method: 'POST',
                data: {
                    title: taskText,
                    description: descriptionText,
                },
                success: function(response) {
                    tasks.push(response);
                    renderTasks(tasks);
                    taskInput.val('');
                    descriptionInput.val('');
                },
                error: function(error) {
                    alert(error.responseJSON.message);
                    console.error('Error adding task:', error);
                }
            });
        }

        // Function to toggle task completion status
        function toggleCompletion(taskId, completed) {
            $.ajax({
                url: `/tasks/${taskId}`,
                method: 'PUT',
                data: {
                    status: completed
                },
                success: function(updatedTask) {
                    const taskIndex = tasks.findIndex(task => task.id === updatedTask.id);
                    if (taskIndex !== -1) {
                        tasks[taskIndex] = updatedTask;
                        renderTasks(tasks);
                    }
                },
                error: function(error) {
                    console.error('Error updating task:', error);
                }
            });
        }

        // Function to delete a task
        function deleteTask(taskId) {
            taskToDelete = taskId;
            $('#confirmationModal').show();
        }

        // Function to confirm deletion of a task
        function deleteTaskConfirmed() {
            $.ajax({
                url: `/tasks/${taskToDelete}`,
                method: 'DELETE',
                success: function() {
                    tasks = tasks.filter(task => task.id !== taskToDelete);
                    renderTasks(tasks);
                    closeConfirmationModal();
                },
                error: function(error) {
                    console.error('Error deleting task:', error);
                }
            });
        }

        // Function to close the delete confirmation modal
        function closeConfirmationModal() {
            $('#confirmationModal').hide();
            taskToDelete = null;
        }

        // Initialize on document ready
        $(document).ready(function() {
            // Setup CSRF token for Laravel
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Fetch tasks initially
            fetchTasks();
        });
    </script>

</body>

</html>
