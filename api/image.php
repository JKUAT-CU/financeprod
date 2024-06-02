<style>
/* Ensure the body and html elements do not add extra margin/padding */
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    width: 100%;
}

/* Ensure the row takes the full width and height */
.row {
    margin: 0;
    padding: 0;
}

/* Remove any margin/padding from parent elements */
.grid-margin {
    margin: 0 !important;
    padding: 0 !important;
}

.card.tale.mt-auto {
    margin-top: 0 !important;
}

.card-body {
    padding: 0;
}

.card-people {
    position: relative;
    overflow: hidden;
    height: auto; /* Make the height flexible */
}

.card-people img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card tale-bg" style="height: 50vh; width: 50vw; overflow: hidden;">
            <div id="randomNatureImage" class="card-body" style="height: 100%; width: 100%; background-size: cover; background-position: center;"></div>
            <div class="weather-info">
                <div class="d-flex">
                    <div>
                        <h2 class="mb-0 font-weight-normal"><i class="icon-sun me-2"></i><span id="temperature">31</span><sup>C</sup></h2>
                    </div>
                    <div class="ms-2">
                        <h4 class="location font-weight-normal" id="location">Kenya</h4>
                        <h6 class="font-weight-normal" id="dayOfWeek"></h6>
                        <h6 class="font-weight-normal" id="dateAndTime"></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6 grid-margin transparent">
        <div class="row">
            <div class="col-md-6 mb-4 stretch-card transparent">
                <div class="card card-tale">
                    <div class="card-body">
                        <p class="mb-4">Yearly Collection</p>
                        <?php
                        include "db.php";
                        $sql = "SELECT SUM(TransAmount) AS yearlyCollection FROM accounts WHERE YEAR(TransTime) = YEAR(CURDATE())";
                        $result = $conn->query($sql);
                        $yearlyCollection = 0;
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $yearlyCollection = $row["yearlyCollection"];
                        }
                        echo "<p class='fs-30 mb-2'>$yearlyCollection</p>";
                        ?>
                        <!-- <p>10.00% (30 days)</p> -->
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4 stretch-card transparent">
                <div class="card card-dark-blue">
                    <div class="card-body">
                        <p class="mb-4">Yearly Expense</p>
                        <?php
                        $sql = "SELECT SUM(CASE WHEN Amount < 0 THEN Amount ELSE 0 END) AS yearlyExpense FROM Expenses";
                        $result = $conn->query($sql);
                        $yearlyExpense = 0;
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $yearlyExpense = abs($row["yearlyExpense"]);
                        }
                        echo "<p class='fs-30 mb-2'>$yearlyExpense</p>";
                        ?>
                       
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
                <div class="card card-light-blue">
                    <div class="card-body">
                        <p class="mb-4">Semester Collection</p>
                        <?php
                        $current_month = date('m');
                        if ($current_month >= 9 && $current_month <= 12) {
                            $start_date = date('Y') . '0901';
                            $end_date = date('Y') . '1231';
                            $semester_name = "First";
                        } elseif ($current_month >= 1 && $current_month <= 4) {
                            $start_date = date('Y') . '0101';
                            $end_date = date('Y') . '0430';
                            $semester_name = "Second";
                        } else {
                            $start_date = date('Y') . '0501';
                            $end_date = date('Y') . '0831';
                            $semester_name = "Third";
                        }
                        $sql = "SELECT SUM(TransAmount) AS semesterCollection FROM accounts WHERE TransTime BETWEEN '$start_date' AND '$end_date'";
                        $result = $conn->query($sql);
                        $semesterCollection = 0;
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $semesterCollection = $row["semesterCollection"];
                        }
                        echo "<p class='fs-30 mb-2'>$semesterCollection</p>";
                        ?>

                    </div>
                </div>
            </div>
            <div class="col-md-6 stretch-card transparent">
                <div class="card card-light-danger">
                    <div class="card-body">
                        <p class="mb-4">Semester Expense</p>
                        <?php
                        $sql = "SELECT SUM(Amount) AS semesterExpense FROM Expenses WHERE TransactionDate BETWEEN '$start_date' AND '$end_date'";
                        $result = $conn->query($sql);
                        $semesterExpense = 0;
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $semesterExpense = $row["semesterExpense"];
                        }
                        echo "<p class='fs-30 mb-2'>" . ($semesterExpense != "" ? $semesterExpense : 0) . "</p>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
// Function to update date, time, and day of the week
function updateDateTime() {
    const now = new Date();
    const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayOfWeek = daysOfWeek[now.getDay()];
    const date = now.toLocaleDateString();
    const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('dayOfWeek').textContent = dayOfWeek;
    document.getElementById('dateAndTime').textContent = `${date}, ${time}`;
}
// Function to update text color based on image brightness
function updateTextColor() {
    const randomNatureImage = document.getElementById('randomNatureImage');
    const imageUrl = randomNatureImage.style.backgroundImage.replace('url("', '').replace('")', '');

    const img = new Image();
    img.src = imageUrl;

    img.onload = function() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0, img.width, img.height);
        const data = ctx.getImageData(0, 0, img.width, img.height).data;
        let brightness = 0;
        for (let i = 0; i < data.length; i += 4) {
            brightness += (0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);
        }
        brightness = brightness / (img.width * img.height);
        const textColor = brightness < 127.5 ? 'white' : 'black';
        const elementsToColor = document.querySelectorAll('.font-weight-normal');
        elementsToColor.forEach(element => {
            element.style.color = textColor;
        });
    };
}



// Function to fetch a random nature image from Unsplash and update the page
function fetchRandomNatureImage() {
    fetch('https://source.unsplash.com/featured/?nature')
        .then(response => {
            const imageUrl = response.url;
            document.getElementById('randomNatureImage').style.backgroundImage = `url('${imageUrl}')`;
            updateTextColor();
        })
        .catch(error => {
            console.error('Error fetching random nature image:', error);
        });
}



// Update date, time, and day of the week every second
setInterval(updateDateTime, 1000);

// Initial fetch of random nature image
fetchRandomNatureImage();

// Update text color when the image is loaded
document.getElementById('randomNatureImage').addEventListener('load', updateTextColor);

// Adjust the height of the card-people element
window.addEventListener('load', adjustCardPeopleHeight);
window.addEventListener('resize', adjustCardPeopleHeight);
</script>