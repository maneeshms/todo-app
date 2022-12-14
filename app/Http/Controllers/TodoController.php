<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List of todos of any user.
     *
     * @return \Illuminate\Http\Response
     */
    public function findAll(Request $request)
    {
        $req_body = $request->all();
        $todo = $this->fetchTodo($req_body);

        return response()->json(['status' => 'success', 'result' => $todo]);
    }

    /**
     * Display the specified resource.
     *
     * @param object $request
     *
     * @return App\Models\Todo
     */
    private function fetchTodo($request)
    {
        $query = Todo::query();
        if (isset($request['user_id'])) {
            $query->where('user_id', (int) $request['user_id']);
        }
        if (isset($request['completed'])) {
            if ((bool) $request['completed']) {
                $query->whereNotNull('completed_at');
            } else {
                $query->whereNull('completed_at');
            }
        }
        $todo = $query->get();

        return $todo;
    }

    /**
     * List of todos of logged in user.
     *
     * @return \Illuminate\Http\Response
     */
    public function findMine(Request $request)
    {
        $req_body = $request->all();
        $req_body['user_id'] = Auth::user()->id;
        $todo = $this->fetchTodo($req_body);

        return response()->json(['status' => 'success', 'result' => $todo]);
    }

    /**
     * Create a todo.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->validate($request, [
        'content' => 'required',
         ]);
        if (Auth::user()->todo()->Create($request->all())) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'])
                        ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function findOne($id)
    {
        $todo = Todo::where('id', $id)->get();

        return response()->json($todo);
    }

    /**
     * Mark the todo as completed or incompleted.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function markAsCompleted(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
          'completed' => 'required|boolean',
      ]);
        if (!$validator->fails()) {
            if (Gate::allows('has-authorization', $todo)) {
                $todo = Todo::find($id);
                if ($request->completed) {
                    $todo->completed_at = Carbon::now();
                } else {
                    $todo->completed_at = null;
                }
                if ($todo->save()) {
                    return response()->json(['status' => 'success']);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong'])
                        ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]);
                }
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'])
                    ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
            }
        }

        return response()->json(['status' => 'failed', 'message' => 'Bad request'])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST, Response::$statusTexts[Response::HTTP_BAD_REQUEST]);
    }

    /**
     * Remove the todo.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function remove($id)
    {
        $todo = Todo::find($id);
        if ($todo) {
            if (Gate::allows('has-authorization', $todo)) {
                if (Todo::destroy($id)) {
                    return response()->json(['status' => 'success']);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong'])
                        ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]);
                }
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'])
                ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
            }
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Todo not found'])
                ->setStatusCode(Response::HTTP_NOT_FOUND, Response::$statusTexts[Response::HTTP_NOT_FOUND]);
        }
    }
}
