@component('mail::message')
# Rappel de paiement

Bonjour {{ $studentName }},

Nous vous rappelons qu'un solde reste dû sur votre inscription à la formation **{{ $trainingTitle }}** chez SmartEco Academy.

@component('mail::table')
| | Montant |
|:---|---:|
| Montant total dû | {{ number_format($amountDue, 2) }} DH |
| Déjà versé | {{ number_format($amountPaid, 2) }} DH |
| **Restant à payer** | **{{ number_format($amountRemaining, 2) }} DH** |
@endcomponent

Merci de régulariser votre situation dans les meilleurs délais afin de conserver l'accès à la formation.

Pour toute question, n'hésitez pas à contacter l'administration.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
