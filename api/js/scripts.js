fetch('https://api.finance.admin.jkuatcu.org/api.php')
    .then(response => response.text())
    .then(data => {
        
        const jsonData = JSON.parse(data); // Parse the response as JSON
        document.getElementById('yearlyCollection').innerText = jsonData.yearlyCollection;
        document.getElementById('yearlyExpense').innerText = jsonData.yearlyExpense;
        document.getElementById('semesterCollection').innerText = jsonData.semesterCollection;
        document.getElementById('semesterExpense').innerText = jsonData.semesterExpense;
    })
    .catch(error => console.error('Error fetching data:', error));
