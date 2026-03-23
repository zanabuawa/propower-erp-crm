<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Empresas
    Route::get('/empresas', \App\Livewire\Companies\CompanyIndex::class)->name('companies.index');
    Route::get('/empresas/crear', \App\Livewire\Companies\CompanyForm::class)->name('companies.create');
    Route::get('/empresas/{company}/editar', \App\Livewire\Companies\CompanyForm::class)->name('companies.edit');

    // Usuarios
    Route::get('/usuarios', \App\Livewire\Users\UserIndex::class)->name('users.index');
    Route::get('/usuarios/crear', \App\Livewire\Users\UserForm::class)->name('users.create');
    Route::get('/usuarios/{user}/editar', \App\Livewire\Users\UserForm::class)->name('users.edit');

    // Sucursales
    Route::get('/sucursales', \App\Livewire\Branches\BranchIndex::class)->name('branches.index');
    Route::get('/sucursales/crear', \App\Livewire\Branches\BranchForm::class)->name('branches.create');
    Route::get('/sucursales/{branch}/editar', \App\Livewire\Branches\BranchForm::class)->name('branches.edit');

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

});

require __DIR__ . '/auth.php';
