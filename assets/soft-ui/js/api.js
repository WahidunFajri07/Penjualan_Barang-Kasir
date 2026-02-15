// assets/js/api.js

// Fungsi helper untuk kirim request dengan token
function apiRequest(url, method = 'GET', data = null) {
    const token = localStorage.getItem('auth_token');
    
    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    };

    const config = {
        method: method,
        headers: headers
    };

    if (data) {
        config.body = JSON.stringify(data);
    }

    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// Contoh penggunaan
// apiRequest('/kasir_app/api/produk?page=1&limit=10')
//     .then(data => console.log(data))
//     .catch(err => console.error(err));