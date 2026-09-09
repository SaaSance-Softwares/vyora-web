<!DOCTYPE html>
<html>
<head>
    <title>Recovering your cart...</title>
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; font-family:sans-serif; background:#f9f9f9;">
    <div>Recovering your cart, please wait...</div>
    <script>
        try {
            const newState = {
                state: {
                    items: @json($items),
                    appliedCoupon: null,
                    cartToken: "{{ $cartToken }}",
                    guestEmail: "{{ $guestEmail }}"
                },
                version: 0
            };
            localStorage.setItem('dope-cart-storage', JSON.stringify(newState));
        } catch (e) {
            console.error(e);
        }
        window.location.href = '/checkout';
    </script>
</body>
</html>
