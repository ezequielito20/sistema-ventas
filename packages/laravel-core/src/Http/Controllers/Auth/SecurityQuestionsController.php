<?php

namespace MiEmpresa\Core\Http\Controllers\Auth;

use MiEmpresa\Core\Models\SecurityQuestion;
use Illuminate\Http\Request;
use MiEmpresa\Core\Http\Controllers\Controller;
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
            return redirect('/');
        }

        return view('core::auth.security-questions.setup');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'questions' => ['required', 'array', 'size:3'],
            'questions.*' => ['required', 'string', 'min:4', 'max:255'],
            'answers' => ['required', 'array', 'size:3'],
            'answers.*' => ['required', 'string', 'min:1', 'max:255'],
        ], [
            'questions.*.min' => __('La pregunta debe tener al menos :min caracteres.'),
            'answers.*.min' => __('La respuesta debe tener al menos :min caracter.'),
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

        // Refrescar la instancia del usuario en Auth para que el middleware
        // vea security_questions_setup = true y no redirija de vuelta acá.
        Auth::setUser($user->fresh());

        return redirect('/')->with([
            'message' => __('¡Preguntas de seguridad guardadas correctamente! Ya puedes usar el sistema.'),
            'icons' => 'success',
        ]);
    }
}
