<x-mail::message>
# Mise à jour de votre inscription

Bonjour,

Le statut de votre inscription **#{{ $registration->id }}** a été modifié.

**Nouveau statut : {{ $statusLabel }}**

@if ($newStatus === 'under_review')
Votre dossier est actuellement examiné par l’administration.
@elseif ($newStatus === 'incomplete')
Votre dossier est incomplet. Veuillez consulter votre espace étudiant pour vérifier les documents demandés.
@elseif ($newStatus === 'accepted')
Félicitations, votre inscription a été acceptée.
@elseif ($newStatus === 'rejected')
Après examen, votre inscription n’a pas été acceptée.
@endif

<x-mail::button :url="route('student.registrations.show', ['registration' => $registration->id])">
Consulter mon inscription
</x-mail::button>

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>