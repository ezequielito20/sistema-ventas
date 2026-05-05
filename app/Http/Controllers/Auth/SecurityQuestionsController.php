<?php

namespace App\Http\Controllers\Auth;

use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SecurityQuestionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function setup()
    {
        $user = Auth::user();

        if ($user->security_questions_setup) {
            return redirect()->intended('/');
        }

        return view('auth.v2.security-questions.setup');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'questions' => ['required', 'array', 'size:3'],
            'questions.*' => ['required', 'string', 'min:5', 'max:255'],
            'answers' => ['required', 'array', 'size:3'],
            'answers.*' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        // Delete existing questions if re-setting up
        SecurityQuestion::where('user_id', $user->id)->delete();

        foreach ($request->questions as $i => $question) {
            SecurityQuestion::create([
                'user_id' => $user->id,
                'question' => $question,
                'answer' => $request->answers[$i],
            ]);
        }

        $user->update(['security_questions_setup' => true]);

        return redirect()->intended('/');
    }
}
