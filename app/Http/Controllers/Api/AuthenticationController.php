<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){

        Auth::shouldUse('api');
    }

     /** Create a new user to the system
      *
     */
     public function register(Request $request, JWTAuth $JWTAuth){

        $validate = $this->validateUserRegistration($request->all()); // Process Validation rules

        if($validate->fails()){

            return $this->validationFailed('Failed Validation', $validate->errors()); // Check if Validation fails
        }


        $user = new User;  //Create User Account
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password = Hash::make($request->input('password'));


        if ($user->save()) {

            return $this->actionSuccess('Registration is Successful');

        } else {

            return $this->actionFailure('Something went wrong');
        }

    }

    /** User login to the system
      *
     */
    public function login(Request $request, JWTAuth $JWTAuth)
    {

        $validate = $this->loginCredentialsValidator($request->all()); // Process Validation rules

        if($validate->fails()){
            return $this->validationFailed('Failed Validation', $validate->errors()); // Check if validation fails
        }

        $user = User::where('email', $request->email)->first();

        if(!$user){

            return $this->validationFailed('Failed Validation', ['message' => ['User email not found']]); // Checks if user exist
        }

            if(Hash::check($request->password, $user->password)){ // compare password

                if ($user->status == '0') { //Checks if user is active

                    return $this->validationFailed('Failed Validation', ['message' => ['Access Denied']]);

                } else {

                    $token = $JWTAuth->fromUser($user); // Generate token

                     return response()->json([
                        'data' => $user,
                        'status' => 'success',
                        'token' => $token,
                        'status_code' => 200
                    ], 200);
                }

            }else{
                return $this->validationFailed('Failed Validation', ['data' => ['Password is Incorrect ']]);
            }
    }

     /** Get or Fetch All Registered Users data
      *
     */
    public function getUsers(){

        $users = User::orderBy('firstname')->get(); // Gets all users data

        return response()->json([
            'data' => $users->makeHidden(['email_verified_at']),
            'status' => 'success',
            'message' => 'All Registered Users',
            'status_code' => 200
        ], 200);

    }


    /** Authenticated user Generates new token for further action
      *
     */
    public function generateToken(JWTAuth $JWTAuth){

        $JWTAuth->setToken(request()->bearerToken());
        $user = $JWTAuth->toUser();

        $token = $JWTAuth->fromUser($user); // Generate new token

        return response()->json([
            'token' => $token,
            'message' => 'New Token Generated',
            'status_code' => 200
        ], 200);

    }


    /**
     * Get a validator for User Registration .
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateUserRegistration(array $data)
    {
        return Validator::make($data,
        [
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'firstname' => 'required',
            'lastname' => 'required',
        ],
        [
            'email.required' => 'Email is required',
            'email.unique' => 'Email already taken',
            'password' => 'Password is required',
            'password.min' => 'Password needs to have at least 6 characters',
            'password_confirmation.required' => 'Passwords do not match',
            'firstname.required' => 'Firstname is required',
            'lastname.required' => 'Lastname is required',
        ]);
    }

                    /**
     * Get a validator for an incoming Login request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginCredentialsValidator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|string',
            'password' => 'required|string',
        ],
        [
            'email.required' => 'Email is required',
            'password.required' => 'Password is required'
        ]
    );
    }
}
