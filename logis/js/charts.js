// js/charts.js

// Chart Manager Class
class ChartManager {
    constructor() {
        this.charts = {};
    }

    // Create Order Progress Chart
    createOrderProgressChart(elementId, data) {
        const ctx = document.getElementById(elementId).getContext('2d');
        this.charts[elementId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Orders',
                    data: data.values || [12, 19, 15, 17, 14, 13],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#4e73df',
                    pointHoverBorderColor: '#fff',
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#5a5c69',
                        bodyColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: '#f3f3f3',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 5,
                            callback: function(value) {
                                return value + ' orders';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Create Shipment Status Chart (Doughnut)
    createShipmentStatusChart(elementId, data) {
        const ctx = document.getElementById(elementId).getContext('2d');
        this.charts[elementId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels || ['In Transit', 'Delivered', 'Delayed', 'Preparing'],
                datasets: [{
                    data: data.values || [45, 30, 10, 15],
                    backgroundColor: ['#36b9cc', '#1cc88a', '#e74a3b', '#f6c23e'],
                    hoverBackgroundColor: ['#2c9faf', '#17a673', '#be3e31', '#dda20a'],
                    hoverBorderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Create Bar Chart
    createBarChart(elementId, data) {
        const ctx = document.getElementById(elementId).getContext('2d');
        this.charts[elementId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Shipments',
                    data: data.values || [12, 19, 15, 17, 14, 13, 18],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(133, 135, 150, 0.8)',
                        'rgba(111, 66, 193, 0.8)'
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f3f3'
                        }
                    }
                }
            }
        });
    }

    // Update Chart Data
    updateChart(elementId, newData) {
        if (this.charts[elementId]) {
            const chart = this.charts[elementId];
            if (newData.labels) {
                chart.data.labels = newData.labels;
            }
            if (newData.values) {
                chart.data.datasets[0].data = newData.values;
            }
            chart.update();
        }
    }

    // Destroy Chart
    destroyChart(elementId) {
        if (this.charts[elementId]) {
            this.charts[elementId].destroy();
            delete this.charts[elementId];
        }
    }
}

// Initialize Chart Manager
const chartManager = new ChartManager();

// Auto-initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Order Progress Chart
    if (document.getElementById('orderProgressChart')) {
        chartManager.createOrderProgressChart('orderProgressChart', {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            values: [12, 19, 15, 17, 14, 13]
        });
    }

    // Shipment Status Chart
    if (document.getElementById('shipmentStatusChart')) {
        chartManager.createShipmentStatusChart('shipmentStatusChart', {
            labels: ['In Transit', 'Delivered', 'Delayed', 'Preparing'],
            values: [45, 30, 10, 15]
        });
    }

    // Weekly Shipments Chart
    if (document.getElementById('weeklyShipmentsChart')) {
        chartManager.createBarChart('weeklyShipmentsChart', {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            values: [12, 19, 15, 17, 14, 13, 18]
        });
    }
});

// Real-time chart updates
function refreshCharts() {
    showLoader('spinner');
    
    // Simulate API call
    setTimeout(() => {
        // Update with new random data
        chartManager.updateChart('orderProgressChart', {
            values: Array.from({length: 6}, () => Math.floor(Math.random() * 30))
        });
        
        chartManager.updateChart('shipmentStatusChart', {
            values: Array.from({length: 4}, () => Math.floor(Math.random() * 50))
        });
        
        hideLoader();
        showNotification('Charts updated successfully!', 'success');
    }, 1500);
}