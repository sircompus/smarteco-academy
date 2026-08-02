@extends('layouts.student')

@section('title', 'Nouvelle inscription')
@section('page-title', 'Nouvelle inscription')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold">
            Créer une demande d’inscription
        </h2>

        <form
            method="POST"
            action="{{ route('student.registrations.store') }}"
            class="mt-6"
        >
            @include('student.registrations._form')
        </form>
    </section>
@endsection