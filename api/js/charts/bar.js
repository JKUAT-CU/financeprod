fetch('https://api.finance.admin.jkuatcu.org/api2.php')
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Check if monthlyData is an array
        if (Array.isArray(data)) {
            // Extract monthly data from the response
            const monthlyData = data;

            // Map numeric month representations to month names
            const monthNames = [
                "September", "October", "November", "December",
                "January", "February", "March", "April", "May", "June", "July", "August"
            ];

            // Extract months, budgets, and collections from the monthly data
            const sortedData = monthlyData.sort((a, b) => new Date('01 ' + a.month) - new Date('01 ' + b.month)); // Sort data by month
            const months = sortedData.map(entry => monthNames[parseInt(entry.month.substr(4)) - 1]); // Convert numeric month to name
            const budgets = sortedData.map(entry => entry.budget_amount);
            const collections = sortedData.map(entry => entry.total_amount);

            // Create Chart.js chart
            const ctx = document.getElementById('budgetCollectionChart').getContext('2d');
            const budgetCollectionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Budget',
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1,
                            data: budgets
                        },
                        {
                            label: 'Collection',
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            data: collections
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        } else {
            console.error('Monthly data is not an array:', data);
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
    });
