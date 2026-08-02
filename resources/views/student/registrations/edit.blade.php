@extends('layouts.student')

@section('title', 'Modifier l’inscription')
@section('page-title', 'Modifier l’inscription')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold">
            Modifier {{ $registration->reference }}
        </h2>

        <form
            method="POST"
            action="{{ route(
                'student.registrations.update',
                $registration
            ) }}"
            class="mt-6"
        >
            @include('student.registrations._form', [
                'profile' => null,
            ])
        </form>
    </section>
@endsection