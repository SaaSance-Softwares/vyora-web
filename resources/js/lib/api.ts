import axios from 'axios';

// Laravel includes CSRF tokens automatically in axios if setup correctly.
// We configure axios to work with relative URLs now that it's on the same domain.
const api = axios.create({
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export default api;
