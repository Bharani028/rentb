{{-- resources/views/emails/contact.blade.php --}}
@component('mail::message')
# New Contact Message

**From:** {{ $name }} ({{ $email }})  
**Subject:** {{ $subject }}

---

{{ $body }}

@endcomponent
