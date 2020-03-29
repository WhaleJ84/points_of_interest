window.onload = function(){
    getPoi();
    document.getElementById('regionButton').addEventListener('click', regionRequest);
}

function getPoi(){
    var xhr2 = new XMLHttpRequest();
    xhr2.addEventListener ('load', responseReceived);
    xhr2.open('GET', '/pointsofinterest/get_poi');
    xhr2.send();
}

function getReview(){
    var review = new XMLHttpRequest();
    review.addEventListener ('load', displayReviews);
    review.open('GET', '/pointsofinterest/get_review');
    review.send();
}

function recommend(id){
    var recommend = new XMLHttpRequest();
    recommend.addEventListener ('load', responseReceived);
    recommend.open('POST', '/pointsofinterest/recommend');
    recommend.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    recommend.send(ID=id);
}

function regionRequest(){
    // Create the XMLHttpRequest variable.
    // This variable represents the AJAX communication between client and server.
    var xhr2 = new XMLHttpRequest();

    // Read the data from the form fields.
    var region = document.getElementById('region').value;

    // Specify the CALLBACK function.
    // When we get a response from the server, the callback function will run
    xhr2.addEventListener ('load', responseReceived);

    // Open the connection to the server
    // We are sending a request to "flights.php" in the same folder and passing in the destination and date as a query string.
    xhr2.open('GET','/pointsofinterest/region/' + region);

    // Send the request.
    xhr2.send();
}

function poiRequest(id){
    // Create the XMLHttpRequest variable.
    // This variable represents the AJAX communication between client and server.
    var poi = new XMLHttpRequest();

    // Read the data from the form fields.
    //var id = document.getElementById('link').value;

    // Specify the CALLBACK function.
    // When we get a response from the server, the callback function will run
    poi.addEventListener ('load', responseReceived);

    // Open the connection to the server
    // We are sending a request to "flights.php" in the same folder and passing in the destination and date as a query string.
    poi.open('GET','/pointsofinterest/view/' + id);

    // Send the request.
    poi.send();
}

// The callback function simply places the response from the server in the div with the ID of 'response'.
// The parameter "e" contains the original XMLHttpRequest variable as "e.target".
// We get the actual response from the server as "e.target.responseText"
function responseReceived(e){
    document.getElementById('searchresults').innerHTML = e.target.responseText;
    getReview();
}

function displayReviews(f){
    document.getElementById('reviews').innerHTML = f.target.responseText;
}
