<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/products', 'pages::products.index')->name('products.index');
Route::livewire('/products/create', 'pages::products.product-form')->name('products.create');
Route::livewire('/products/{product}/edit', 'pages::products.product-form')->name('products.edit');

Route::livewire('/commands', 'pages::commands.index')->name('commands.index');
Route::livewire('/commands/create', 'pages::commands.command-form')->name('commands.create');
Route::livewire('/commands/{command}/edit', 'pages::commands.command-form')->name('commands.edit');

Route::livewire('/clients', 'pages::client.index')->name('clients.index');
Route::livewire('/clients/{client}', 'pages::client.show')->name('clients.show');
