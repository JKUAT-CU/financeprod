fetch('https://api.finance.admin.jkuatcu.org/api.php')
    .then(response => response.json())
    .then(data => {
        console.log(data); // Log the response to the console

        // Parse total_amount as numbers for each entry in monthlyData
        data.monthlyData.forEach(entry => {
            entry.total_amount = parseFloat(entry.total_amount);
        });

        // Create an array of all months in order from September to August
        const monthsInOrder = ['September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August'];

        // Initialize an object to store transaction amounts for each month
        const transactionData = {};

        // Fill in zero values for all months
        monthsInOrder.forEach(month => {
            transactionData[month] = 0;
        });

        // Fill in actual transaction amounts
        data.monthlyData.forEach(entry => {
            const month = entry.month;
            transactionData[month] = entry.total_amount;
        });

        // Extract the ordered data for labels and amounts
        const months = Object.keys(transactionData);
        const amounts = Object.values(transactionData);

        // Create Chart.js chart
        const ctx = document.getElementById('transactionChart').getContext('2d');
        const transactionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Transaction Amount',
                    data: amounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
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
    })
    .catch(error => {
        console.error('Error fetching data:', error);
    });
