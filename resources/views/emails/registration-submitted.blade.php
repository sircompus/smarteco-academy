<x-mail::message>
# Inscription envoyée

Bonjour,

Votre inscription a bien été soumise à l’administration de {{ config('app.name') }}.

**Référence de l’inscription :** #{{ $registration->id }}

Vous recevrez un nouvel e-mail lorsque le statut de votre dossier changera.

<x-mail::button :url="route('student.registrations.show', ['registration' => $registration->id])">
Consulter mon inscription
</x-mail::button>

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>