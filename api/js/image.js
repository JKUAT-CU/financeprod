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