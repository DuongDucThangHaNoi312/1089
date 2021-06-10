const $ = require('jquery');

function toggleSubmitButton() {
    let form = $("#stripe-payment-form");
    let submitButton = form.find(':submit');
    if(currentPlanIsChecked() && currentPlanIsCancelled() === false) {
        submitButton.attr("disabled", true);
    } else {
        submitButton.attr("disabled", false);
    }
}

function currentPlanIsCancelled() {
    let chosenSubscriptionPlanRadioButton = $('#stripe_payment_subscriptionPlans input[type=radio]:checked', '#stripe-payment-form');
    if (chosenSubscriptionPlanRadioButton.length === 0) {
        return false;
    }
    let chosenSubscriptionPlanLabel = $("div[for='"+ $(chosenSubscriptionPlanRadioButton).attr("id") + "']");
    return chosenSubscriptionPlanLabel.find('.cancelled').length !== 0;
}

function currentPlanIsChecked() {
    $('#stripe_payment_subscriptionPlans input[type=radio]').each(function () {
        if ($('.choose-subscription-plan').length) {
            if ($(this).val() == $('.choose-subscription-plan').data('plan-id')) {
                $(this).attr('checked', true);
            }
        }
    });
    let chosenSubscriptionPlanRadioButton = $('#stripe_payment_subscriptionPlans input[type=radio]:checked', '#stripe-payment-form');
    if (chosenSubscriptionPlanRadioButton.length === 0) {
        return false;
    }
    let chosenSubscriptionPlanLabel = $("div[for='"+ $(chosenSubscriptionPlanRadioButton).attr("id") + "']");
    return chosenSubscriptionPlanLabel.find('.current').length !== 0;
}

function getSelectedPlanTitle() {
    let chosenSubscriptionPlanRadioButton = $('#stripe_payment_subscriptionPlans input[type=radio]:checked', '#stripe-payment-form');
    let chosenSubscriptionPlanLabel = $("div[for='"+ $(chosenSubscriptionPlanRadioButton ).attr("id") + "']");
    let planTitle = chosenSubscriptionPlanLabel.find('.plan-title');
    if (planTitle.length !== 0) {
        return planTitle[0].innerText;
    }
    return '';
}

function getSelectedSubscriptionPlanId() {
    return chosenSubscriptionPlanRadioButton = $('#stripe_payment_subscriptionPlans input[type=radio]:checked', '#stripe-payment-form').val();
}

function getCurrentPlanPrice() {
    let currentPlan = $('#stripe_payment_subscriptionPlans .current', '#stripe-payment-form');
    if (currentPlan.length === 0) {
        return null;
    }
    let planPrice = currentPlan.find('.plan-price');
    if (planPrice.length !== 0) {
        return parseFloat(planPrice[0].innerText);
    }

    return null;
}

function getSelectedPlanPrice() {
    let chosenSubscriptionPlanRadioButton = $('#stripe_payment_subscriptionPlans input[type=radio]:checked', '#stripe-payment-form');
    let chosenSubscriptionPlanLabel = $("div[for='"+ $(chosenSubscriptionPlanRadioButton ).attr("id") + "']");
    let planPrice = chosenSubscriptionPlanLabel.find('.plan-price');
    if (planPrice.length !== 0) {
        return parseFloat(planPrice[0].innerText);
    }
    return null;
}

function isJobSeeker() {
    let chosenSubscriptionPlanRadioButton = $('#stripe_payment_subscriptionPlans .subscription-plan', '#stripe-payment-form');
    return chosenSubscriptionPlanRadioButton.hasClass('job-seeker-plan') === true;
}

function isUpgrade() {
    let currentPlanPrice = getCurrentPlanPrice();
    let selectedPlanPrice = getSelectedPlanPrice();
    if (currentPlanPrice != null && selectedPlanPrice != null) {
        return currentPlanPrice < selectedPlanPrice;
    }

    return null;
}

function isSubscriptionCancelledOverall() {
    let currentPlanSummary = $("#stripe-payment-form .current-plan-summary");
    return currentPlanSummary.hasClass('cancelled-plan');
}

function isPaymentInformationDisplayed() {
    let paymentInformation = $("#stripe-payment-form .StripeElement");
    return paymentInformation.length !== 0;
}

function updatePlanSummary() {
    let title = getSelectedPlanTitle();
    let isJobSeekerPlan = isJobSeeker();
    let isUpgradable = isUpgrade();

    let text = '';
    let action = 'changing';
    if (isUpgradable === true) {
        action = 'upgrading';
        text = "Loading..."
        previewInvoiceAmount(action, title);
    } else if (isUpgradable === false) {
        action = 'downgrade';
        if (isJobSeekerPlan) {
            text = 'You’re requesting a ' + action +' from your plan to '+ title +' plan. Your downgrade will go into effect after the end of your current billing period.<br/><br/> If you have a downgrade request already pending, this change will replace the pending downgrade request.';
        } else {
            text = 'You’re requesting a ' + action +' from your plan to '+ title +' plan. Your downgrade will go into effect <b>immediately</b>, but <b>will not</b> be charged until the end of your current billing period.<br/><br/>';
        }
        if (isJobSeekerPlan === true) {
            text += '<br/><br/> <span class="text-danger">Downgrades will remove submitted interest to jobs outside the level of your downgraded plan.</span>';
        }
    }

    $(".summary-body").html(text);
}

function previewInvoiceAmount(action, title) {
    let id = getSelectedSubscriptionPlanId();
    $.ajax({
        type: "GET",
        url: '/preview/subscription/plan/' + id + "/change",
        async: true,
        success: function(data, status) {
            console.log(data);
            let text = 'You’re ' + action + ' your plan to ' + title + '. You’ll receive a prorated credit for the unused portion of your current plan. We’ll charge you a prorated amount today of $' + data.cost.toFixed(2) + ".";
            $(".summary-body").html(text);

        },
        error: function(xhr, desc, err) {
            let text = "Error loading amount due, please try again.";
            $(".summary-body").html(text);
        }
    });
}

function showPaymentInformation() {
    let price = getSelectedPlanPrice();
    return price > 0.0;
}

function togglePaymentInformation() {
    if (showPaymentInformation()) {
        $("#stripe-payment-form .payment-information").show();
    } else {
        $("#stripe-payment-form .payment-information").hide();
    }
}

function togglePlanSummary() {
    let isCurrentChecked = currentPlanIsChecked();
    let isCancelledChecked = currentPlanIsCancelled();
    let isSubscriptionCancelled = isSubscriptionCancelledOverall();
    if (!isPaymentInformationDisplayed()) {
        if (isCurrentChecked === false && isSubscriptionCancelled === false) {
            updatePlanSummary();
            $("#stripe-payment-form .plan-summary").show();
            $("#stripe-payment-form .current-plan-summary").hide();
        } else if (isCurrentChecked === true && isCancelledChecked === false) {
            $("#stripe-payment-form .current-plan-summary").show();
            $("#stripe-payment-form .plan-summary").hide();
        } else {
            $("#stripe-payment-form .current-plan-summary").hide();
            $("#stripe-payment-form .plan-summary").hide();
        }
    } else {
        $("#stripe-payment-form .current-plan-summary").hide();
        $("#stripe-payment-form .plan-summary").hide();
    }
}

jQuery(document).ready(function() {

    // On Initial Load
    toggleSubmitButton();
    togglePlanSummary();
    togglePaymentInformation();
    $("#stripe_payment_subscriptionPlans input[type=radio]").change(function() {
        toggleSubmitButton();
        togglePlanSummary();
        togglePaymentInformation();
    });

    $("#chooseSubscription").click(function() {
        toggleSubmitButton();
        togglePlanSummary();
        togglePaymentInformation();
    });

    $('.btn-confirm-subscription-plan').click(function(e) {
        e.preventDefault();

        $(this).attr("disabled", true);
        $(this).closest('form').submit();
    });
});