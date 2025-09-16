fetch('/analytics/monthly-assessments')
    .then(res => res.json())
    .then(data => {
        const labels = data.map(item => item.month);
        const counts = data.map(item => item.count);

        new Chart(document.getElementById('assessmentChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Assessments per Month',
                    data: counts,
                    backgroundColor: '#8B5CF6', // purple
                    borderRadius: 5,
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Assessment Creation',
                        font: {
                            size: 18
                        }
                    },
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 35
                        }
                    }
                }
            }
        });
    });