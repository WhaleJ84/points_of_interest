function getPoi(){
    var results = new XMLHttpRequest();
    results.addEventListener('load', displayPoints);
    results.open('GET', '/pointsofinterest/get_poi');
    results.send();
}

function recommend(id){
    var request = new XMLHttpRequest();
    request.addEventListener ('load', displayPoints);
    request.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            displayPoints;
        }
    };
    request.open('POST', '/pointsofinterest/recommend', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var params = 'ID=' + id;
    request.send(params);
}

function regionRequest(){
    // Create the XMLHttpRequest variable.
    // This variable represents the AJAX communication between client and server.
    var regions = new XMLHttpRequest();

    // Read the data from the form fields.
    var region = document.getElementById('region').value;

    // Specify the CALLBACK function.
    // When we get a response from the server, the callback function will run
    regions.addEventListener('load', displayPoints);

    // Open the connection to the server
    // We are sending a request to "flights.php" in the same folder and passing in the destination and
    // date as a query string.
    regions.open('GET', '/pointsofinterest/region/' + region);

    // Send the request.
    regions.send();
}


function poiRequest(id){
    var poi = new XMLHttpRequest();
    poi.addEventListener ('load', displayPoints);
    poi.open('GET','/pointsofinterest/view/' + id);
    poi.send();
    getReview(id);
}

function getReview(id){
    var review = new XMLHttpRequest();
    review.addEventListener('load', displayReviews);
    review.open('GET', '/pointsofinterest/review/' + id);
    review.send();
}

// The callback function simply places the response from the server in the div with the ID of 'response'.
// The parameter "e" contains the original XMLHttpRequest variable as "e.target".
// We get the actual response from the server as "e.target.responseText"
function displayPoints(e){
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
    '<th>Recommendations</th>' +
    '<th>Username</th></tr>';

    // Use a 'for' loop to loop through each result
    // poiData will be an array of all the records inside the JSON, so
    // poiData[i] will be the current result.
    // We can then extract the individual fields with poiData[i].depart,
    // poiData[i].arrive, etc.
    for (var i = 0; i < poiData.length; i++) {
        results = results + '<tr><td>' + poiData[i].ID + '</td>' +
        '<td><input type="submit" id="link" value="' + poiData[i].name + ' " onclick="poiRequest(' + poiData[i].ID + ')"></td>' +
        '<td>' + poiData[i].type + '</td>' +
        '<td>' + poiData[i].country + '</td>' +
        '<td>' + poiData[i].region + '</td>' +
        '<td>' + poiData[i].lon + '</td>' +
        '<td>' + poiData[i].lat + '</td>' +
        '<td>' + poiData[i].description + '</td>' +
        '<td><input type="submit" id="link" class="center" onclick="recommend(' + poiData[i].ID + ')" value="👍 ' + poiData[i].recommended + ' "/></td>' +
        '<td>' + poiData[i].username + '</td></tr>';
    }

    results = results + '</table>';

    document.getElementById('searchresults').innerHTML = results;
}

function displayReviews(f) {
    var reviewData = JSON.parse(f.target.responseText);
    var results = '<table><tr><th>Review</th></tr>';
    for (var i = 0; i < reviewData.length; i++) {
        results = results + '<tr><td>' + reviewData[i].review + '</td></tr>';
    }
    results = results + '</table>';
    document.getElementById('reviews').innerHTML = results;
}

window.onload = function () {
    getPoi();
    document.getElementById('regionButton').addEventListener('click', regionRequest);
};
