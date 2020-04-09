function getPoi(){
    var results = new XMLHttpRequest();
    results.addEventListener('load', displayPoints);
    results.open('GET', '/~assign225/get_poi');
    results.send();
}

// used inside displayPoints()
function recommend(id){
    var request = new XMLHttpRequest();
    request.addEventListener ('load', displayPoints);
    request.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            displayPoints;
        }
    };
    request.open('POST', '/~assign225/recommend', true);
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
    regions.open('GET', '/~assign225/region/' + region);

    // Send the request.
    regions.send();
}

// used inside displayPoints()
function poiRequest(id){
    var poi = new XMLHttpRequest();
    poi.addEventListener ('load', displayPoints);
    poi.open('GET','/~assign225/view/' + id);
    poi.send();
    getReview(id);
}

function getReview(id){
    var review = new XMLHttpRequest();
    review.addEventListener('load', displayReviews.bind(this,id));
    review.open('GET', '/~assign225/review/' + id);
    review.send();
}

// used inside displayReviews()
function submitReview(id){
    var xhr2 = new XMLHttpRequest();
    var review = document.getElementById('review').value;
    xhr2.addEventListener('load', displayReviews.bind(this,id));
    xhr2.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            displayPoints;
        }
    };
    xhr2.open('POST', '/~assign225/add_review/' + id, true);
    xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var params = 'review=' + review;
    xhr2.send(params);
    alert('Review are only accpeted from registered users.\nAll reviews must be approved by an administrator before being shown to the public.');
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

function displayReviews(id,f) {
    var reviewData = JSON.parse(f.target.responseText);
    var results = '<br/><nav></nav><br/><table><tr><th>Reviews</th></tr><tr><td><textarea id="review" placeholder="Enter a review" required></textarea></td></tr><tr><td class="center"><input type="submit" id="reviewButton" value="Submit " onclick="submitReview(' + id + ')"/></td></tr>';
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
