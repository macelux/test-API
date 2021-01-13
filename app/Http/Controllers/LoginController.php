<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Http\UsersController;
use App\User;
use Illuminate\Notifications\Notification;
use App\Notifications\NewStudent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SignupEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class LoginController extends Controller
{

    public function show()
    {
        //redirect to previous page after login
        if (!session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }

        if (Auth::check()) {
            return redirect('/');
        }

        $title = 'Login';
        return view('auth.login', compact('title'));
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        $title = 'Register';
        return view('auth.register', compact('title'));
    }

    public function showForgotPassword()
    {
        $title = 'Forgot Password';
        return view('auth.forgetPassword', compact('title'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required | email'
        ]);

        if (!User::where('email', '=', $request->email)->count() > 0) {
            return redirect()->back()->with('error', 'This email does not exist in our database');
        }

        Password::sendResetLink($request->only('email')); // send reset password link
        return redirect()->back()->with('success', 'Reset password link sent to your email');
    }

    public function showResetPassword($token)
    { 
        return view('auth.reset-password',['token', $token]);
    }

    public function resetPassword()
    {
        $credentials = request()->validate([
            'email' => 'required | email',
            'token' => 'required',
            'password' => 'required | min:8 | confirmed',
        ]);

        $reset_password_status = Password::reset($credentials, function($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();

            $user->setRememberToken(Str::random(60));
            event(new PasswordReset($user));
        });

        if ($reset_password_status ==Password::INVALID_TOKEN){
            return redirect()->back()->with('success', 'Reset password link sent to your email');
        }

        return redirect()->route('login')->with('success', 'Password has been successfully changed');
    }

    public function postSignIn(Request $request)
    {
        $request->validate([
            'email' => 'required | email',
            'password' => 'required',
        ]);
        $data = ['email' => $request->input('email'), 'password' => $request->input('password')];

        $remember_me  = (!empty($request->remember_me)) ? TRUE : FALSE;


        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember_me)) { 
            return redirect('dashboard')->with('success', 'Welcome <strong>'. Auth::user()->first_name.' '.Auth::user()->last_name. '</strong>' );
        }
        return redirect()->back()->with('error', 'invalid login details');
    }

    public function postSignUp(Request $request)
    {
        // validate user input
        $request->validate([
            'first_name' => 'required | min: 3| max:50',
            'last_name' => 'required | min: 3| max:50',
            'email' => 'required | unique:users,email',
            'password' => 'required | min:6 | confirmed',
        ]);

        // create user
        $user = User::create([
            'last_name' => $request->first_name,
            'first_name' => $request->last_name,
            'email' => $request->email, 
            'password' => bcrypt($request->password),
            'status' => 1
        ]);  

        // save user role
        if($request->user_as == 'student'){
            $user->role()->sync(3); // 3 for student
        }else{
            $user->role()->sync(2); // 2 for teacher
        }

        //$user->notify( new NewStudent($password)); // notify new student
        //Mail::to($user->email)->send(new SignupEmail);

        // automatically login user
        Auth::login($user);
        // redirect user to dashboard
        return redirect()->route('user.dashboard')
            ->with('success',  `Hello <strong>{$request->first_name}</strong>, your registration was successful`);
    }


    public function Logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
