@extends('front.layouts.master')

@section('title', 'Donate')
@section('description', "Help keep us online by donating")

@push('head')
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        const stripe = Stripe('{{ config('services.stripe.key') }}');
        const sessionStartEndpoint = '{{ route('donations.create') }}';

        async function startOneTimeCheckout() {
            try {
                const amountInDollars = document.getElementById('donation-amount').value;
                        @auth
                const endpoint = `${sessionStartEndpoint}?amount=${amountInDollars}&account_id={{ Auth::user()->getKey() }}`
                        @else
                const endpoint = `${sessionStartEndpoint}?amount=${amountInDollars}`
                        @endauth

                const response = await fetch(endpoint, {
                        method: 'GET',
                        mode: 'same-origin',
                        cache: 'no-cache',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });
                const json = await response.json();

                await redirectToCheckout(json.data.session_id)

            } catch (error) {
                console.error(error);
            }
        }

        async function startRecurringCheckout() {
            try {
                const amountInDollars = document.getElementById('donation-amount').value;
                        @auth
                const endpoint = `${sessionStartEndpoint}?is_recurring=true&amount=${amountInDollars}&account_id={{ Auth::user()->getKey() }}`
                        @else
                const endpoint = `${sessionStartEndpoint}?is_recurring=true&amount=${amountInDollars}`
                        @endauth

                const response = await fetch(endpoint, {
                        method: 'GET',
                        mode: 'same-origin',
                        cache: 'no-cache',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });
                const json = await response.json();

                await redirectToCheckout(json.data.session_id)

            } catch (error) {
                console.error(error);
            }
        }

        async function redirectToCheckout(sessionId) {
            const {error} = await stripe.redirectToCheckout({
                sessionId: sessionId,
            })

            if (error) {
                console.error(error);

                // If `redirectToCheckout` fails due to a browser or network
                // error, display the localized error message to your customer
                // using `error.message`.
            }
        }

        window.onload = function() {
            document.getElementById('stripe-donate-button').onclick = startOneTimeCheckout;
            document.getElementById('stripe-donate-recurring-button').onclick = startRecurringCheckout;
        }
    </script>
@endpush

@section('contents')

    <div class="donate">
        <div class="donate__left">

            <div class="donate__section">
                <h1>Help Keep Us Online</h1>
                <h4 class="primary"><i class="fas fa-exclamation-circle"></i> Donations are the only way to keep our community running!</h4>

                <span class="text">
                    Thank you for considering a donation to PCB. All proceeds go towards the running expenses of our servers and website.
                    <p />
                    Whilst you can donate any amount you like, due to processing fees the minimum we would request you donate is $3 (USD).
                
                    <ul class="no-list">
                        <li><i class="fas fa-long-arrow-alt-right"></i> Donating at least <strong>$3 (USD)</strong> will provide you with 1 month worth of Donator perks</li>
                        <li><i class="fas fa-long-arrow-alt-right"></i> Donating at least <strong>$30 (USD)</strong> will provide you with Donator perks for life</li>
                    </ul>

                    Every $3 donated grants 1 month of Donator perks.
                </span>
            </div>
            <div class="donate__section">
                <h3>Donator perks</h3>

                <div class="donate__perks">
                    <div class="perks-table">
                        <div class="perks-table__header">
                            <i class="fas fa-handshake"></i> Gain Trusted Rank Perks
                        </div>
                        <div class="perks-table__body">
                            <ul class="perks-table__inner">
                                <li>Custom nicknames</li>
                                <li>Teleporting</li>
                                <li>Increased home limit</li>
                            </ul>
                        </div>
                    </div>

                    <div class="perks-table">
                        <div class="perks-table__header">
                        <i class="fas fa-money-check-alt"></i> Plus Donator-Only Perks
                        </div>
                        <div class="perks-table__body">
                            <ul class="perks-table__inner">
                                <li>Priority server slot</li>
                                <li>In-game [$] (Donator) tag</li>
                                <li>Coloured nickname</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
            <div class="donate__section">
            
                <h3>Terms and Conditions</h3>
                <ul>
                    <li>Please check for any applicable fees as Stripe/PayPal can change these at any time for any reason, beyond PCB’s control.</li>
                    <li>Please remember that donating does not exempt you from obeying the server rules. You will get no pardons or privileges regardless of how much you have donated as a donation is just that, a donation. <strong>You are not paying for a service</strong>.</li>
                </ul>

                <blockquote>
                    <strong>Community Rules</strong><br>
                    6. Nicknames - nicknames must be similar to your in-game name (IGN)<br>
                    Nicknames cannot be excessive in visible length (staff discretion applies).<br><br>

                    For Donators, or other players with coloured nickname permission, your nickname colour may not resemble that of a staff rank (colours that are associated with a staff rank) . If you are unsure then request approval from a staff member. You may use a mixture of staff and other colours<br><br>

                    Exceptions may be made if a player is more commonly known by another name
                </blockquote>
            </div>
        </div>
        
        <div class="donate__right">
            <h1>Donate now</h1>

            @guest
                <div class="alert alert--warning">
                    <strong>You are not currently logged in</strong>. If you do not login, you will not receive your donator perks!
                </div>
            @endguest

            <div>
                <input type="text" class="input-text" id="donation-amount" value="3.00" placeholder="3.00" />

                <button class="button button--large button--fill button--primary" type="button" id="stripe-donate-button">
                    <i class="fas fa-credit-card"></i> Donate
                </button>

                <button class="button button--large button--fill button--primary" type="button" id="stripe-donate-recurring-button">
                    <i class="fas fa-credit-card"></i> Donate Recurring
                </button>
            </div>

            <small>
                Your payment details will be processed by <a href="https://stripe.com/" rel="noopener" target="_blank">Stripe</a>, and only a record of your donation will be stored by PCB.
                In accordance with data retention laws, <strong>we do not store any credit card or bank account information</strong>.
            </small>
        </div>

    </div>

@endsection
