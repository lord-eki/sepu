<?php


test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test' . uniqid() . '@example.com',
        'phone' => '0700000000',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);


    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
