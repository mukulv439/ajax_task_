<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Retrieve all tasks for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve tasks for the authenticated user
        $tasks = Todo::where('user_id', Auth::id())->get();
        return response()->json(['tasks' => $tasks], 200);
    }
    public function show()
    {
        return view('Master.Task.view');
 } 
    /**
     * Store a newly created task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'title' => 'required|string|unique:todos|max:255',
            'description' => 'nullable|string',
        ]);

        // Create a new task instance
        $task = new Todo();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = 'pending'; // Default status is 'pending'
        $task->user_id = Auth::id(); // Assign the current authenticated user ID
        $task->save();

        return response()->json($task, 201); // Return the created task with status code 201 (created)
    }

    /**
     * Update the specified task's status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the task by ID
        $task = Todo::findOrFail($id);

        // Validate and update the task status based on the request input
        $request->validate([
            'status' => 'required', // Assuming status will be sent as true or false
        ]);

        // Update task status based on request input
        $task->status = $request->status ? 'completed' : 'pending';
        $task->save();

        return response()->json($task, 200); // Return updated task with status code 200 (OK)
    }

    /**
     * Remove the specified task from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the task by ID and delete it
        $task = Todo::findOrFail($id);
        $task->delete();

        return response()->json(null, 200); // Return null with status code 200 (OK) after deletion
    }
}
