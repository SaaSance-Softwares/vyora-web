<!DOCTYPE html>
<html>
<head>
    <title>Authenticating...</title>
</head>
<body>
    <p>Authenticating, please wait...</p>
    <script>
        try {
            const token = "{{ $token }}";
            const user = {!! json_encode($user) !!};
            
            const authData = {
                state: {
                    token: token,
                    user: user
                },
                version: 0
            };
            
            localStorage.setItem('auth-storage', JSON.stringify(authData));
        } catch (e) {
            console.error('Failed to set auth storage', e);
        }
        
        window.location.href = '/';
    </script>
</body>
</html>
