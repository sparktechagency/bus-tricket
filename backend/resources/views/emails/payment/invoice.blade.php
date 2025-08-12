@component('mail::message')
# Your Top-up Was Successful!

Hello {{ $user->name }},

Thank you for your payment. Here are the details of your transaction:

@component('mail::panel')
Amount: {{ $amount }} {{ $currency }}<br>
Transaction ID: {{ $charge_id }}<br>
Date: {{ $transaction_date }}
@endcomponent

Your new balance has been updated in your wallet.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
