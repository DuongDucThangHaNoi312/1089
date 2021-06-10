const $ = require('jquery');

function isSelectedPricePlanFree() {
    let chosenSubscriptionPlanRadioButton = document.querySelector('#stripe_payment_subscriptionPlans input[type=radio]:checked');
    let chosenSubscriptionPlanRadioButtonId = chosenSubscriptionPlanRadioButton.getAttribute("id");
    let chosenSubscriptionPlanLabel = document.querySelector("div[for='" + chosenSubscriptionPlanRadioButtonId + "']");
    let planPrice = chosenSubscriptionPlanLabel.querySelector('.plan-price');
    if (planPrice.length !== 0) {
        let planPriceFloat =  parseFloat(planPrice.innerText);
        return planPriceFloat <= 0;
    }
    return null;
}

function initStripePayment(formId = 'stripe-payment-form') {
    // Create a Stripe client.
    // Handle form submission.
    var form = document.getElementById(formId);
    var stripe = Stripe(form.dataset["attribute"]);

    // Create an instance of Elements.
    var elements = stripe.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Avenir-Medium", sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#9c9a9a'
            }
        },
        invalid: {
            color: '#C1272D',
            iconColor: '#C1272D'
        }
    };

    // Create an instance of the card Element.
    var emptyDiv = "card";
    if (formId === 'update-payment-form') {
        emptyDiv = "update-card"
    } else if (formId === 'pay-inactive-payment-form') {
        emptyDiv = "inactive-card"
    }
    var card = elements.create('card', {style: style});

    // Add an instance of the card Element into the `card-element` <div>.
    var cardElementId = '#'+ formId + ' #' + emptyDiv;
    var cardElement = $(cardElementId);
    if (cardElement.length != 0) {
        card.mount(cardElementId);

        // Handle real-time validation errors from the card Element.
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        $(form).off('submit').on('submit', function(event) {
            event.preventDefault();

            let shouldCreateToken = false;
            if (event.target.name === 'update_payment_method' || event.target.name === 'pay_inactive_subscription') {
                shouldCreateToken = true;
            } else {
                shouldCreateToken = isSelectedPricePlanFree() === false;
            }
            if (shouldCreateToken) {
                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        // Inform the user if there was an error.
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        $('.card-error-message').text(result.error.message);
                        $('.btn-confirm-subscription-plan').attr("disabled", false);
                    } else {
                        $('.card-error-message').text('');
                        stripeTokenHandler(result.token, event.target);
                    }
                });
            } else {
                event.target.submit();
            }
        });

        // Submit the form with the token ID.
        function stripeTokenHandler(token, form) {
            // Insert the token ID into the form so it gets submitted to the server
            // var form = document.getElementById('stripe-payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden') ;
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);

            // Submit the form
            form.submit();
        }
    }
}

$(document).ready(function(){

    if ($('#update-payment-form').length !== 0) {
        initStripePayment();
    }

    if ($('#update-payment-form').length !== 0) {
        initStripePayment('update-payment-form');
    }

    if ($('#pay-inactive-payment-form').length !== 0) {
        initStripePayment('pay-inactive-payment-form');
    }

    $("#chooseSubscription").click(function() {
        initStripePayment();
    });

    $("#payInactiveSubscription").click(function() {
        initStripePayment('pay-inactive-payment-form');
    });

    $("#updatePayment").click(function() {
        initStripePayment('update-payment-form');
    });
});