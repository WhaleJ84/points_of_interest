# points\_of\_interest

This documentation is written from scratch with the knowledge of Task I and as such, tasks prior to this point may seem more advanced than they should.
Where necessary, notes are made to redirect to the relevant entry to explain said design choices.

Each section includes the files required to achieve a goal and describes how the relevant code inside achieves said goal.

## Prerequisites

### index.php

This file contains all the necessary code to function using the Slim framework (explained in further detail in task H).

### views/points\_of\_interest.phtml

At the top of the page is a snippet of PHP code: `include('functions.php')`, which imports custom functions defined within the file to be used within this page.

Within the html head of the page is a link to the css stylesheet and a favicon for the browser tab - both of which are explained in further detail within Task E.
Also contained within is the page title `PointsOfInterest` and a link to the `ajax.js` JavaScript file (AJAX explained in further detail in task I).

Within the html body is the website title and a custom PHP function, `navbar` (explained in further detail in `functions.php`).
The above mentioned additions are included in all `view/*.phtml` files, and as such will be omitted from any further discussion of included files.

Finally, the page contains two HTML divs: `searchresults` and `reviews` that will be populated with AJAX responses to display results to the user.

### functions.php

This file contains two similar functions that are used within the Slim PHTML views; `navbar` and `poi_options`.
`navbar` makes a decision based on whether the variable `$_SESSION['gatekeeper']` is set and provides links to either login and signup or logout and reset password based on that.
In either case, a third option is always present to redirect the user back to the homepage.

Similarly, `poi_options` provides the user an option to add a new POI (explained in further detail below) and a back button that refreshes the AJAX response if `$_SESSION['gatekeeper']` is set and prompts the user to login if not.

In both functions, they loop through an array to set values that will be used within HTML links that are used within nav tags to generate the navbar menus.

## A) Allow a registered user to add a new point of interest (POI)

*The user should provide the name, type (e.g. hotel, city, historical site, bar, restaurant, beach, mountain, etc), and a description. This should add a record to the pointsofinterest table, containing the information the user entered together with the username of the currently logged-in user.*

### views/accounts.phtml

As each POI requires the `username` value to be set within the database to denote who created it, the website requires a login system.
To prevent having to create functions/pages to signup, login (and additionally change password), we can create one generic page that takes in the values all three functions require (username and password) within a form and dynamically send it send it to the correct location due to Slim's passed `index.php` argument `$action`.
The `$action` argument is determined by the navbar options generated by `functions.php` and will be explained in further detail below.

### index.php

When either login, signup or reset password are selected via the `navbar` function they link to the relative location in `/accounts/{(login|signup|reset)}` which will pass through the action as a variable `$value` to the `accounts.phtml` view where it will display the correct information based on that variable.

When the submit button is clicked on `accounts.phtml`, it sends the POST data to the correct routes within within `index.php`.
Logging in runs the prepared statement: `SELECT * FROM poi_users WHERE username=$post['username'] AND password=$post['password']` (prepared statements explained in further detail in Task E) followed by checking if `$_SESSION['isadmin']` is set and redirects to the admin page if set (explained in further detail in Task F) and back to the homepage if not.
If the query is successful, it creates a session variable `$_SESSION['gatekeeper']=$row['username']` to track who is logged in.
Similarly, signing up redirects back to the homepage after running `INSERT INTO poi_users (username,password) VALUES ($post['username'],$post['password'])`.

When 'Add' is selected from `poi_options`, it routes to `/add` where it checks if the user is logged in, presenting the `add_poi.phtml` page if they are and redirecting back to the homepage if not.
Once the user has submitted their new POI, the POST data gets routed to `/add_poi` where it once again checks if the user is logged in and runs `INSERT INTO pointsofinterest (name, type, country, region, lon, lat, description, username) VALUES ($post['name'], $post['type'], $post['country'], $post['region'], $post['lon'], $post['lat'], $post['description'], $_SESSION['gatekeeper'])`.
Logged in or not, the user will be redirected back to the homepage after.

### views/add\_poi.phtml

The page displays a form with input fields for all required values in the `pointsofinterest` table in the database, with `required` HTML tags on all fields as the database fails without complete input.
`name`, `region` and `description` are text input fields with the former two having regex patterns of `^[a-zA-Z,.-]+$` which only allows alphabetical characters and special characters commonly used within names.
Similarly, the `lon` and `lat` number input fields contain regex patterns that allow values to and from -180 > 180 and -90 > 90 respectively, with placeholder values to inform the user of valid input.
Using patterns within the HTML form fields prevents invalid data being sent to the back-end.

Both the `type` and `country` fields are drop-down menus that present the user with valid options to prevent invalid input.
`type` entries are decided based on existing entries within the database and `country` reads from a file (explained in further detail in `countries` below).
Ideally, `region` would be a drop-down menu also, but to dynamically fetch every region available within a selected country would be far more work than the text input field above.

As `poi_options` and `index.php` prevent and redirect the user from this page without being logged in, this ensures that the hidden input field containing the username value will always be set.

### countries

A simple file that contains every country available on a single line to be read in by `views/add_poi.phtml` into a variable.
As there are only a certain number of countries available in the world and new ones don't tend to pop up often, this seems like a better option than letting the user manually input a country.

## B) Allow a user to search for a POI by region

*A user should be able to enter a region (e.g. Hampshire, Normandy or California); once they have entered the region, all POIs in that region should appear.
Each search result should contain a hyperlink labelled "Recommend", which should link to task c) (see below).*

### views/points\_of\_interest.phtml

Contains a drop-down menu with a CSS id of `region`, dynamically generated using a PHP while loop from a JSON array named `$regions` sent from `index.php` that contains all of the unique entries of regions within the `pointsofinterest` table.
Once the user has selected their region from the menu, the submit button has a JavaScript function listening for the submission to run `regionRequest` in `ajax.json`
After the query results have returned from `index.php`, the JSON output is parsed by `ajax.json` and display within the `searchresults` div within `points_of_interest.phtml` as it is for searches.

### script/ajax.js

In short `regionRequest` assigns the value from the drop-down menu to the variable `region` via `document.getElementById('region').value` and then sends the value to `index.php` with a GET request to `/region/{region}` and displays the returned JSON results inside the `searchresults` HTML div in `points_of_interest.phtml` in a formatted way via the function `displayPoints`.

`displayPoints` stores the parsed JSON data in the variable `poiData` and creates a variable `results` that contains a default HTML table with the rows and headers required for the `pointsofinterest` table and appends the data inside `poiData` to the `results` variable by adding new rows and data entries containing the relevant data provided via a for loop iterating through the `poiData` array.
The `name` entry is displayed as an submit button with the JavaScript running `poiRequest(ID)` on click to allow the user to view individual points and their reviews (explained in further detail in Task D).

The `recommend` entry is also a submit button that runs `recommend(ID)` on click (explained in further detail in Task C) and is displayed with a 'thumbs up' emoji, which in modern web design is associated with approving something.
The ID is passed into the `recommend(ID)` function during the AJAX function `displayPoints`, which inserts `recommend(' + poiData[i].ID + ')` to grab the ID value for the POI of that row.

### index.php

To prevent a user from trying to search for a region that doesn't exist within the database, before the `points_of_interest.phtml` view is loaded, the query `SELECT DISTINCT region FROM pointsofinterest` is saved to `$regions` and passed into the view to be loaded into the drop-down menu.

Once the user has chose their desired region, it is submitted to `/region/{region}` where the argument is their choice where it runs `SELECT * FROM pointsofinterest WHERE region=$args['region'] ORDER BY recommended DESC` (order by query explained in further detail in Task C).
It saves the statement to the variable `$results` and returns it in JSON format, as all search result and reviews are (explained in further detail in Task I).

## C) Allow a user to recommend a POI

*For a basic pass, this should simply add one to the recommended column for that POI.*

### script/ajax.js

Once the user has clicked the recommend button displayed via `displayPoints` (explained in Task B), it runs `recommend(id)` which adds a listener to `displayPoints` to display the results once returned and checks it can make a connection to `index.php` via `onreadystatechange` before proceeding.
On success, the function saves the id that was passed into the function to a variable `params` in a POST data format and sends it to `/recommend`.


### index.php

`/recommend` checks if the user is logged in, returning with the query to display the same page they were on without updating the recommendations if they're not.
If they are logged in, it updates the POI with the query `UPDATE pointsofinterest SET recommended=recommended+1 WHERE ID=$post['ID']`.
It then checks if `$_SESSION['pageID']` is set (which is set when the user has selected an individual POI to view, and unset everywhere else relevant within `index.php`) and runs `SELECT * FROM pointsofinterest WHERE ID=$post['ID']` if it is.
If `$_SESSION['pageID']` is not set, it runs `SELECT * FROM pointsofinterest ORDER BY recommended DESC`, using the order by feature to make higher recommended POIs appear higher in the search results, giving recommendations some meaning.

## D) Allow a user to view all reviews for a given POI

*A user should be able to select a place of interest from their search results; this will display all reviews for that place of interest. If you are not intending to complete task e), you may test this by adding reviews to the database via phpMyAdmin.*

### script/ajax.js

As explained in Task B, all POIs displayed to the user have their named displayed as a submit button that runs `poiRequest(id)` on click.
This sends a request to `/view/{id}` and displays the results in the `searchresults` HTML div but then runs `getReview(id)`.

`getReview(id)` works similarly to `poiRequest` but adds `displayReviews` as it's event listener instead and sends the results from `/review/{id}` to it.

`displayReviews` does the same as `displayPoints` but instead creates a table to display the reviews available in the database for the POI and entries for the user to submit their own review (explained in further detail in Task E) and displays it within the `reviews` HTML div.

### index.php

`/view/{id}` sets the variable `$_SESSION['pageID']` to `$args['id']` which is used for checks within other functions to determine if a user is viewing a particular POI or not within the AJAX responses.
It then runs `SELECT * FROM pointsofinterest WHERE ID=$args['id']` and sends it back to `ajax.js`.

`/review/{id}` checks if `$_SESSION['isadmin']` is set and displays every relevant review with `SELECT * FROM poi_reviews WHERE poi_id=$args['id'] AND approved=1` if the user is not an admin, displaying every review if they are (explained in further detail in Task F).

## E) Allow a user to review a POI

*This must add an appropriate record to the reviews table. Ignore the approved column for now.*

### script/ajax.js

As stated briefly in Task D, `displayReviews` appends a textarea with a CSS id of `review` and submit button above the reviews in the database, with the submit button running `submitReview(id)` upon click.
The id is passed into the `submitReview(id)` function during the AJAX function `displayReviews`, which inserts `submitReview(' + id + ')` to grab the ID value for the POI of that entry.

`submitReview` saves the content from the textarea to a variable `review` with `document.getElementById('review').value` and sends it as a POST request to `/add_review/{id}` similar to `recommend(id)` (explained in Task C) before informing the user that all reviews must be submitted by logged in users and approved by an administrator via a JavaScript alert.
After the review has been submitted the page is essentially refreshed with the same AJAX functions as stated before to prevent the entire page from being reloaded.

### index.php

`/add_review/{id}` checks if the user is logged in via `$_SESSION['gatekeeper']` and processes their request if they are via `INSERT INTO poi_reviews (poi_id, review) VALUES ($args['id'], $post['review'])`.
Regardless if the user is logged in or not, it then runs `SELECT * FROM poi_reviews WHERE poi_id=$args['id'] AND approved=1` before sending it back to `ajax.js` as explained above.

*To achieve a Grade C, you must, in addition, ensure that you guard against SQL injection and cross-site scripting, must have no broken links, and implement a basic CSS stylesheet including custom layout and colour scheme (see below).*
*Please note that you should use standards-compliant HTML and CSS for this assignment, and ensure that you include basic custom CSS (a basic custom layout and custom colour scheme). Your CSS must be written as an external stylesheet. You must do this to achieve a C or above. Also to achieve a C or above, there must be no broken links.*

To prevent against SQL injection, all SQL queries are executed as prepared statements within `index.php`.
`views/accounts.phtml` HTML encodes the value passed to it with `htmlentities("$value")` to prevent the user from passing bad values to it via an XSS attack and HTML forms are provided regex patterns to prevent them from being submitted.

All links are dynamically generated via `functions.php` and `ajax.js` to prevent having to manually find and update links every time a page changes.
As all pages are displayed via Slim views, there are only 4 views in total, each containing a css link to `style/main.css` and a favicon at `images/favicon.png` to make the pages clearer to see and more appealing to users.
The CSS file includes a set of colours across the entire site to add consistency.

*Your site must also be user-friendly (easy to navigate and use to the end-user). One example of maximising user-friendliness would be using drop-down lists rather than form fields where appropriate, and another would be to not require users to enter IDs or other quantities that might be unknown to the end-user. Navigation around the site should also be intuitive.. You must take these steps to achieve a B or above.*

The site also provides drop-down menus where possible (selecting region, submitting a new POI) and names all inputs with placeholders where appropriate (longitude and latitude).
All POI viewing related actions are performed using AJAX requests, meaning the user doesn't have to navigate round the site much if at all and all links are clearly defined via their CSS styling.

## F) Allow administrator to approve reviews

*As well as approving a review, an administrator must be able to see a list of all pending reviews.
To achieve a Grade B, you must, in addition, ensure that your site is user-friendly (see below).*

### views/admin.phtml

Working similar to other views, `admin.phtml` differs by receiving the variable `$reviews` and running a while loop on each review to be approved.
Upon each loop, another SQL query is ran `SELECT * FROM pointsofinterest WHERE ID=$row['poi_id']` before dynamically populating a table with data from both queries.
`$reviews` contains the review itself while the other query displays the name of the poi\_id rather than just displaying a number, which is more informative.
Each entry contains a hidden input field containing the current id and is given a form button that upon click submits to `/admin/approve` where it changes its status to allow normal users to view it.

### index.php

Logging in, `/login` checks if `$_SESSION['isadmin']` is set and routes the player to `/admin` if true.
`/admin` once again checks if the user actually is an admin, redirecting them back if not and grabbing all the reviews needing approval with `SELECT * FROM poi_reviews WHERE approved=0 ORDER BY poi_id ASC`, sending them as a variable `reviews` to `admin.phtml`.

If logged in, `/admin/approve` runs `UPDATE poi_reviews SET approved=1 WHERE id=$post['id']` before sending the results back to `admin.phtml`.

## G) Implement the majority of your scripts using object-oriented PHP

*You should include some use of Data Access Objects (DAOs) in your code. It is not necessary to use Slim to complete this task.
Task h) - must be implemented in full for an A2. For an A3, it must be mostly functional but there may be a small number of omissions.*

As the site implements Slim/AJAX features, DAOs have been skipped.

## H) Implement the search and review functionality using Slim and AJAX.

*The "search for a POI by region" functionality must be implemented as a Slim endpoint, and you must include an AJAX front end which reads the user's search term, sends it to your Slim endpoint, and displays the search results to the user in a user-friendly, readable, well-formatted way.
The "review" functionality must also be implemented as a Slim endpoint.
It should receive the POI ID and the review as POST data. It should check that the ID and the review are valid.*

*Implement an AJAX review facility as follows. Each search result from your AJAX search - task h) - should include a text box (to allow the user to enter a review) and a "Review" button. When the user clicks the Review button, an AJAX POST request should be sent to your Slim "review" endpoint. When the review has been added, a confirmation message must be displayed to the user, or an error message if the ID and/or review were not valid.*

### index.php

As explained in Task B, the Slim framework is already in place, loading the drop-down menu within the `/` (root) homepage, using the `SELECT DISTINCT region FROM pointsofinterest` query to present the user with all unique options.
Upon submission `ajax.js` sends the response to `/region/{region}` with `regionRequest` where it runs `SELECT * FROM pointsofinterest WHERE region=$args['region'] ORDER BY recommended DESC` and returns it back to `ajax.js` where it's displayed with `displayPoints`.

When an individual POI is viewed, the reviews are displayed in a table below an entry form that upon submission sends the POST data to `/add_review/{id}` from `submitReview` where it runs `INSERT INTO poi_reviews (poi_id,review) VALUES ($args['id'],$post['review'])` if the user is logged in.
Following that, it will grab the reviews again with `SELECT * FROM poi_reviews WHERE poi_id=$args['id'] AND approved=1` to allow the page to be refreshed with an AJAX response with `getReview` and `displayReviews`.

### scripts/ajax.js

As explained in Task B, `regionRequest` grabs the value specified by the user and sends it as a GET request to `index.php` before displaying it as a AJAX response via `displayPoints`.

As explained in Tasks D and E, `getReview(id)` grabs the reviews available for a POI by sending a GET request to `index.php` before displaying it as a AJAX response via `displayReviews` similar to above.
`displayReviews` prefixes a textarea and submit button that links to `submitReview(id)` so the user can create a review.
The id is determined via a cookie that is created in `displayPoints` by appending `poiData[0].ID` after expiring the previous implementation with `; expires=Thu, 01 Jan 1970 00:00:00 UTC` which is the beginning of UNIX Epoch time.
This means that if an individual POI was selected, the last cookie would get expired and a new one created from the for loop.
As there is only one entry in the for loop, the ID of the current page will be stored in a cookie and can be used within `displayReviews` to create the `id` var.

## I) Implement your search facility as a JSON web service and alter your AJAX front end to connect to this JSON web service

*Search results must continue to be displayed in a user-friendly, readable, well-formatted way.*

### index.php

Implementing JSON responses is simple with Slim, requiring the usual `return $res;` to be changed to `return $res->withJson($variable)` on the `/get_poi`,`/review/{id}`,`/region/{region}`,`/view/{id}`,`/recommend` and `/add_review/{id}` routes, where `ajax.js` will parse the responses.

### scripts/ajax.js

All JSON responses are handled by either `displayPoints` or `displayReviews`, saving the responses to an array with `var varName = JSON.parse(?.target.responseText)`.
From there, the content can be iterated through using a for loop and calling the variable `varName[#].value` to get the specific data we require, which in in contrast to how it would be achieved with a typical SQL query `$row['value']`.
All responses are displayed within the `searchresults` and `reviews` divs within `points_of_interest.phtml`.
