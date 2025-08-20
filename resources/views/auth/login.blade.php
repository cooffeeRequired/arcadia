@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                @i18('auth.login-desc')
            </h2>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="/login">
            @csrf

            <div class="space-y-4">
                @include('auth.comps.simple-input', [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'placeholder' => 'any@email.com',
                    'required' => true,
                    'autocomplete' => 'email'
                ])

                @include('auth.comps.simple-input', [
                    'name' => 'password',
                    'label' => i18('password'),
                    'type' => 'password',
                    'placeholder' => '*******',
                    'required' => true,
                    'autocomplete' => 'current-password'
                ])
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    @i18('auth.login')
                </button>
            </div>

            <div class="text-center">
                <a href="/register" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                    @i18('auth.doenst-have-account')
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
