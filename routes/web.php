<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/products', 'pages::products.index')->name('products.index');
Route::livewire('/products/create', 'pages::products.product-form')->name('products.create');
Route::livewire('/products/{product}/edit', 'pages::products.product-form')->name('products.edit');

Route::livewire('/commands', 'pages::command')->name('command');
Route::livewire('/clients', 'pages::client.index')->name('client');
