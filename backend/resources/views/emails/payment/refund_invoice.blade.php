@component('mail::message')
# Your Refund is Complete

Hello {{ $user->name }},

We have successfully processed your refund. The amount will be returned to your original payment method within 5-10 business days.

@component('mail::panel')
Refunded Amount: {{ $amount }} {{ $currency }}<br>
Original Transaction ID: {{ $charge_id }}<br>
Date: {{ $refund_date }}
@endcomponent

Your wallet balance has been updated accordingly.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
