@extends('layouts.mainAdmin')

@section('content')

    <div class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="w-full max-w-sm p-6 bg-white rounded-xl shadow-md">
            <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

            @if ($errors->any())
                <div class="mb-4 text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required class="mt-1 block w-full p-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" required class="mt-1 block w-full p-2 border rounded-lg">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700">
                    Login
                </button>
            </form>
        </div>
    </div>

@endsection
