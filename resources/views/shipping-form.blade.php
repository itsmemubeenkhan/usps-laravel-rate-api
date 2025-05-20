<!DOCTYPE html>
<html>
<head>
    <title>USPS Shipping Rates</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        label { display: block; margin-top: 10px; }
        .rate-option { margin: 5px 0; }
    </style>
</head>
<body>
    <h2>Get USPS Shipping Rates</h2>

    <form id="shipping-form">
        <label>Origin Zip:
            <input type="text" name="origin_zip" required>
        </label>
        <label>Destination Zip:
            <input type="text" name="destination_zip" required>
        </label>
        <label>Weight (lbs):
            <input type="text" name="weight" required>
        </label>

        <button type="submit">Get Rates</button>
    </form>

    <div id="rate-results"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#shipping-form').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('usps.getRates') }}",
                method: "POST",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#rate-results').html('<p>Loading rates...</p>');
                },
                success: function(data) {
                    const pricingOptions = data.pricingOptions || [];
                    let html = '<h3>Select a Shipping Option:</h3>';

                    if (pricingOptions.length > 0) {
                        pricingOptions.forEach(function(pricing) {
                            (pricing.shippingOptions || []).forEach(function(option, index) {
                                const rateOptions = option.rateOptions || [];

                                rateOptions.forEach(function(rateOption) {
                                    const commitment = rateOption.commitment || {};
                                    const deliveryDate = commitment.scheduleDeliveryDate || '';
                                    const deliveryText = commitment.name ? ` (Est. Delivery: ${commitment.name})` : '';

                                    (rateOption.rates || []).forEach(function(rate, rIndex) {
                                        html += `
                                            <div class="rate-option">
                                                <input type="radio" name="shipping_option" id="option${index}_${rIndex}" value="${rate.description}|${rate.price}">
                                                <label for="option${index}_${rIndex}">
                                                    ${rate.description} - $${rate.price.toFixed(2)} ${deliveryText}
                                                </label>
                                            </div>
                                        `;
                                    });
                                });
                            });
                        });
                        $('#rate-results').html(html);
                    } else {
                        $('#rate-results').html('<p>No rates found.</p>');
                    }
                },
                error: function() {
                    $('#rate-results').html('<p>Error getting rates.</p>');
                }
            });
        });
    </script>
</body>
</html>
