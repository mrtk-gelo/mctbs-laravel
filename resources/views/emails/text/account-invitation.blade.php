Hello,

{{ $inviterName ?: 'A MCTBS administrator' }} invited you to create an account{{ $roleName ? ' as '.$roleName : '' }}.
@if ($houseName)
Assigned house: {{ $houseName }}
@endif

Create your account: {{ $invitationUrl }}

This invitation expires {{ $invitation->expires_at->format('M j, Y \a\t g:i A') }}.
For your security, do not share this invitation link. If you were not expecting this invitation, you can ignore this email.
