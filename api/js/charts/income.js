function showView(view) {
    var views = document.getElementsByClassName('view');
    for (var i = 0; i < views.length; i++) {
        views[i].style.display = 'none';
    }
    document.getElementById(view).style.display = 'block';
}

function fetchData() {
    fetch('https://api.finance.admin.jkuatcu.org/api1.php')
        .then(response => response.json())
        .then(data => {
            renderTable('yearsTable', 'Year', data.data.years, data.billRefs);
            renderTable('monthsTable', 'Month', data.data.months, data.billRefs);
            renderTable('semestersTable', 'Semester', data.data.semesters, data.billRefs);
            renderTable('weeksTable', 'Week', data.data.weeks, data.billRefs);
            showView('years');
        });
}

function renderTable(elementId, timeframe, data, billRefs) {
    let tableHtml = '<table class="table table-bordered">';
    tableHtml += '<thead><tr><th>' + timeframe + '</th>';
    billRefs.forEach(billRef => {
        tableHtml += '<th>' + billRef + '</th>';
    });
    tableHtml += '<th>Total</th></tr></thead><tbody>';
    
    for (const [period, amounts] of Object.entries(data)) {
        let rowTotal = 0;
        tableHtml += '<tr><td>' + period + '</td>';
        billRefs.forEach(billRef => {
            const amount = amounts[billRef] ? amounts[billRef] : 0;
            tableHtml += '<td>' + amount + '</td>';
            rowTotal += amount;
        });
        tableHtml += '<td>' + rowTotal + '</td></tr>';
    }
    
    tableHtml += '</tbody></table>';
    document.getElementById(elementId).innerHTML = tableHtml;
}

// Fetch data and render tables on page load
fetchData();

