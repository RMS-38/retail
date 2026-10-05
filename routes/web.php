<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/products', 'pages::products.index')->name('products');
Route::livewire('/commands', 'pages::command')->name('command');
Route::livewire('/clients', 'pages::client.index')->name('client');
