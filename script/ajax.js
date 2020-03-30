function getPoi(){
    var results = new XMLHttpRequest();
    results.addEventListener('load', responseReceived);
    results.open('GET', '/pointsofinterest/get_poi');
    results.send();
}

function regionRequest(){
    // Create the XMLHttpRequest variable.
    // This variable represents the AJAX communication between client and server.
    var regions = new XMLHttpRequest();

    // Read the data from the form fields.
    var region = document.getElementById('region').value;

    // Specify the CALLBACK function.
    // When we get a response from the server, the callback function will run
    regions.addEventListener('load', responseReceived);

    // Open the connection to the server
    // We are sending a request to "flights.php" in the same folder and passing in the destination and
    // date as a query string.
    regions.open('GET', `/pointsofinterest/region/${region}`);

    // Send the request.
    regions.send();
}

function getReview(){
    var review = new XMLHttpRequest();
    review.addEventListener('load', displayReviews);
    review.open('GET', '/pointsofinterest/get_review');
    review.send();
}

// The callback function simply places the response from the server in the div with the ID of 'response'.
// The parameter "e" contains the original XMLHttpRequest variable as "e.target".
// We get the actual response from the server as "e.target.responseText"
function responseReceived(e){
    // Parse the JSON into an array of JavaScript objects
    var poiData = JSON.parse(e.target.responseText);

    // Create a string array to store the results in
    var results = '<table><tr><th>ID</th>' +
    '<th>Name</th>' +
    '<th>Type</th>' +
    '<th>Country</th>' +
    '<th>Region</th>' +
    '<th>Longitude</th>' +
    '<th>Latitude</th>' +
    '<th>Description</th>' +
    '<th>Recommended</th>' +
    '<th>Username</th></tr>';

    // Use a 'for' loop to loop through each result
    // poiData will be an array of all the records inside the JSON, so
    // poiData[i] will be the current result.
    // We can then extract the individual fields with poiData[i].depart,
    // poiData[i].arrive, etc.
    for (var i = 0; i < poiData.length; i++) {
        results = results + '<tr><td>' + poiData[i].ID + '</td>' +
        '<td>' + poiData[i].name + '</td>' +
        '<td>' + poiData[i].type + '</td>' +
        '<td>' + poiData[i].country + '</td>' +
        '<td>' + poiData[i].region + '</td>' +
        '<td>' + poiData[i].lon + '</td>' +
        '<td>' + poiData[i].lat + '</td>' +
        '<td>' + poiData[i].description + '</td>' +
        '<td>' + poiData[i].recommend + '</td>' +
        '<td>' + poiData[i].username + '</td></tr>';
    }

    results = results + '</table>';

    document.getElementById('searchresults').innerHTML = results;
    getReview();
}

function displayReviews(f) {
    document.getElementById('reviews').innerHTML = f.target.responseText;
}

window.onload = function () {
    getPoi();
    document.getElementById('regionButton').addEventListener('click', regionRequest);
};
