@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Vytvořit nový účet
            </h2>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="/register">
            @csrf
            
            <div class="space-y-4">
                @include('auth.comps.simple-input', [
                    'name' => 'name',
                    'label' => 'Jméno',
                    'type' => 'text',
                    'placeholder' => 'Vaše jméno',
                    'required' => true,
                    'autocomplete' => 'name'
                ])
                
                @include('auth.comps.simple-input', [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'placeholder' => 'vas@email.cz',
                    'required' => true,
                    'autocomplete' => 'email'
                ])
                
                @include('auth.comps.simple-input', [
                    'name' => 'password',
                    'label' => 'Heslo',
                    'type' => 'password',
                    'placeholder' => 'Minimálně 6 znaků',
                    'required' => true,
                    'autocomplete' => 'new-password'
                ])
                
                @include('auth.comps.simple-input', [
                    'name' => 'password_confirm',
                    'label' => 'Potvrzení hesla',
                    'type' => 'password',
                    'placeholder' => 'Zopakujte heslo',
                    'required' => true,
                    'autocomplete' => 'new-password'
                ])
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-green-500 group-hover:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Vytvořit účet
                </button>
            </div>

            <div class="text-center">
                <a href="/login" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                    Už máte účet? Přihlaste se
                </a>
            </div>
        </form>
    </div>
</div>
@endsection