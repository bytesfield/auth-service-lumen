<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use App\User;
use App\Http\Controllers\Api\AuthenticationController;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserAuthTest extends TestCase
{


     /**
     * A basic unit test example.
     *
     * @test
     * @return void
     */

    public function user_can_register()
    {

        $password = $this->faker->password;
        $data = [
            'firstname' => $this->faker->name,
            'lastname' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $password, // password
            'password_confirmation' => $password,
        ];

        $response = $this->json('POST', '/api/register', $data);
        $this->assertEquals(200, $this->response->status());
        $response->seeStatusCode(200);

    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {

        $password = '123456';

        $user = factory(User::class)->create([

            'password' => Hash::make($password),
        ]);

        $credentials = [

            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->json('POST', '/api/login', $credentials);


        $response->seeStatusCode(200);
        $this->actingAs($user, 'api');

    }


     /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        $password = $this->faker->password;
        $invalid_password = $this->faker->password;

        $user = factory(User::class)->create([

            'password' => $password,
        ]);

        $credentials = [

            'email' => $user->email,
            'password' => $invalid_password,
        ];

        $response = $this->json('POST', '/api/login', $credentials);
        $this->assertEquals(422, $this->response->status());
        $response->seeStatusCode(422);
    }

     /** @test */
     public function user_cannot_login_with_incorrect_email()
     {

        $email = $this->faker->unique()->safeEmail;
        $wrong_email = $this->faker->unique()->safeEmail;
        $password = $this->faker->password;

         $user = factory(User::class)->create([

             'email' => $email,
         ]);

         $credentials = [

             'email' => $wrong_email,
             'password' => $password,

         ];

         $response = $this->json('POST', '/api/login', $credentials);
         $this->assertEquals(422, $this->response->status());
         $response->seeStatusCode(422);

     }

     /** @test */
    public function can_get_all_users_if_route_exist() {

        $response = $this->json('GET','/api/getUsers');

        $this->assertEquals(200, $this->response->status());

        $response->seeStatusCode(200);

    }

    /** @test */
    public function all_users_can_be_viewed() {


        $users = factory(User::class, 2)->create()->map(function ($users) {
            return $users->only(['id','firstname', 'lastname','phone','email','status']);
        });

        $response = $this->json('GET','/api/getUsers');

        $this->assertEquals(200, $this->response->status());
        $response->seeStatusCode(200);

    }

    /** @test */
    public function user_can_generate_new_token() {

        $user = factory(User::class)->create();

        $token = auth('api')->tokenById($user->id);

        $response = $this->get('/api/generateToken',  ['HTTP_Authorization' => 'Bearer '.$token]);
        $this->assertEquals(200, $this->response->status());

        $response->seeStatusCode(200);

    }
}
