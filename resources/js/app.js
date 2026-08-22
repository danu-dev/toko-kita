import Chart from 'chart.js/auto';

window.Chart = Chart;

// Global helpers (format rupiah)
window.formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// Haversine formula for distance calculation in meters / kilometers
window.calculateDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return 'Menghitung...';
    const R = 6371e3; // Earth radius in meters
    const φ1 = parseFloat(lat1) * Math.PI / 180;
    const φ2 = parseFloat(lat2) * Math.PI / 180;
    const Δφ = (parseFloat(lat2) - parseFloat(lat1)) * Math.PI / 180;
    const Δλ = (parseFloat(lon2) - parseFloat(lon1)) * Math.PI / 180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distanceMeters = Math.round(R * c);

    if (distanceMeters < 1000) {
        return `${distanceMeters} m`;
    }
    return `${(distanceMeters / 1000).toFixed(1)} km`;
};

// User Location Object definition
const createUserLocationStore = () => {
    let defaultLat = -7.946714;
    let defaultLng = 112.615668;
    let defaultLabel = 'Lowokwaru, Kota Malang';

    const saved = localStorage.getItem('tokokita_user_coords');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            if (parsed.lat) defaultLat = parsed.lat;
            if (parsed.lng) defaultLng = parsed.lng;
            if (parsed.label) defaultLabel = parsed.label;
        } catch(e){}
    }

    return {
        lat: defaultLat,
        lng: defaultLng,
        label: defaultLabel,
        isLoaded: false,
        modalOpen: false,

        init() {
            if ('geolocation' in navigator && !saved) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.lat = pos.coords.latitude;
                        this.lng = pos.coords.longitude;
                        this.label = 'Lokasi Saya (GPS Terdeteksi)';
                        this.isLoaded = true;
                        this.persist();
                    },
                    () => {
                        this.isLoaded = true;
                    },
                    { timeout: 5000 }
                );
            } else {
                this.isLoaded = true;
            }
        },

        setLocation(lat, lng, label) {
            this.lat = parseFloat(lat);
            this.lng = parseFloat(lng);
            this.label = label;
            this.persist();
            this.modalOpen = false;
            window.location.reload();
        },

        persist() {
            localStorage.setItem('tokokita_user_coords', JSON.stringify({
                lat: this.lat,
                lng: this.lng,
                label: this.label
            }));
        },

        getDistanceTo(targetLat, targetLng) {
            return window.calculateDistance(this.lat, this.lng, targetLat, targetLng);
        }
    };
};

// Register store cleanly across Alpine and Livewire instances
function registerLocationStore(alpineInstance) {
    if (alpineInstance && !alpineInstance.store('userLocation')) {
        alpineInstance.store('userLocation', createUserLocationStore());
    }
}

if (window.Alpine) {
    registerLocationStore(window.Alpine);
}

document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        registerLocationStore(window.Alpine);
    }
});

document.addEventListener('livewire:init', () => {
    if (window.Alpine) {
        registerLocationStore(window.Alpine);
    } else if (window.Livewire && window.Livewire.Alpine) {
        registerLocationStore(window.Livewire.Alpine);
    }
});
