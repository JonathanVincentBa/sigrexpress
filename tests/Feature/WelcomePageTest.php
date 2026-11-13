<?php

use App\Models\User;

it('shows public tracking and login links to guests without an embedded login form', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Consultar Guía')
        ->assertSee(route('tracking.guia'))
        ->assertSee(route('login'))
        ->assertDontSee('name="password"', false)
        ->assertDontSee('name="email"', false);
});

it('shows system and logout actions to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Consultar Guía')
        ->assertSee('Ir al Sistema')
        ->assertSee('Cerrar Sesión')
        ->assertSee(route('tracking.guia'))
        ->assertSee(route('dashboard'))
        ->assertSee('action="'.route('logout').'"', false)
        ->assertDontSee('name="password"', false);
});
