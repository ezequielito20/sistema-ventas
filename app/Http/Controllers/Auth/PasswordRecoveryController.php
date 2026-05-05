<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordRecoveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRecoveryForm()
    {
        return view('auth.v2.passwords.recover');
    }

    public function findUser(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->security_questions_setup) {
            throw ValidationException::withMessages([
                'email' => __('No se encontró un usuario con ese correo o no tiene preguntas de seguridad configuradas.'),
            ]);
        }

        $questions = SecurityQuestion::where('user_id', $user->id)
            ->inRandomOrder()
            ->limit(2)
            ->get(['id', 'question']);

        session([
            'recovery_user_id' => $user->id,
            'recovery_questions' => $questions->toArray(),
        ]);

        return redirect()->route('password.recovery.questions');
    }

    public function showQuestions()
    {
        if (!session('recovery_user_id')) {
            return redirect()->route('password.recovery');
        }

        return view('auth.v2.passwords.questions', [
            'questions' => session('recovery_questions', []),
        ]);
    }

    public function verifyQuestions(Request $request)
    {
        $request->validate([
            'answers' => ['required', 'array', 'size:2'],
            'answers.*' => ['required', 'string'],
        ]);

        $questions = SecurityQuestion::whereIn('id', array_keys($request->answers))->get();

        if ($questions->count() !== 2) {
            return back()->withErrors(['answers' => __('Error al verificar las preguntas.')]);
        }

        foreach ($questions as $question) {
            $givenAnswer = $request->answers[$question->id] ?? '';
            if (!$question->checkAnswer($givenAnswer)) {
                return back()->withErrors(['answers' => __('Una o más respuestas son incorrectas. Inténtalo de nuevo.')]);
            }
        }

        session(['recovery_verified' => true]);

        return redirect()->route('password.recovery.reset');
    }

    public function showResetForm()
    {
        if (!session('recovery_verified')) {
            return redirect()->route('password.recovery');
        }

        return view('auth.v2.passwords.new-password');
    }

    public function resetPassword(Request $request)
    {
        if (!session('recovery_verified') || !session('recovery_user_id')) {
            return redirect()->route('password.recovery');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::findOrFail(session('recovery_user_id'));
        $user->update(['password' => Hash::make($request->password)]);

        // Cleanup session
        session()->forget(['recovery_user_id', 'recovery_questions', 'recovery_verified']);

        return redirect()->route('login')
            ->with('status', __('Contraseña actualizada correctamente. Ya puedes iniciar sesión.'));
    }
}
