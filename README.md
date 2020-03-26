# points\_of\_interest

This documentation is written from scratch with the knowledge of Task H and as such, tasks prior to this point may seem more advanced than they should.
Where necessary, notes are made to redirect to the relevant entry to explain said design choices.

## Prerequisites
### index.php
This file contains all the necessary code to function using the Slim framework (explained in further detail in task H).

### points\_of\_interest.phtml
Acting as the main page users land to when browsing for the site, `points_of_interest` acts as a traditional index page, hosting a table that contains the fields of select columns within the database entries (`.phtml` format explained in further detail in Task H).
It connects to the `pointsofinterest` database, which contains the fields: `ID`, `name`, `type`, `country`, `region`, `lon`, `lat`, `description`, `recommended`, and `username`.
Both `ID` and `username` are entries that do not need to be known to the user, so should be excluded from the table entries within the page.

The results will be grabbed from the `pointsofinterest` database using the query: `SELECT * FROM pointsofinterest` ~~ORDER BY recommended DESC~~ via `index.php` (latter half explained in further detail in Task C).

## A) Allow a registered user to add a new point of interest (POI)
The user should provide the name, type (e.g. hotel, city, historical site, bar, restaurant, beach, mountain, etc), and a description. This should add a record to the pointsofinterest table, containing the information the user entered together with the username of the currently logged-in user.

### account.phtml
This page will dynamically change to match the operation needed for a user account, but for this situation acts as the login/signup page for the user.
Contained within will be a form, prompting the user for a `username` and `password`, as contained within the `poi_users` database, along with `id` and `isadmin` which cannot be entered by the user.

### account.php
### add\_poi.phtml
.phtml format appears to have line-limit of 3000 characters. Have to move to .php
### database.php

## B) Allow a user to search for a POI by region
A user should be able to enter a region (e.g. Hampshire, Normandy or California); once they have entered the region, all POIs in that region should appear.
Each search result should contain a hyperlink labelled "Recommend", which should link to task c) (see below).

## C) Allow a user to recommend a POI
For a basic pass, this should simply add one to the recommended column for that POI.
[EXPLAIN REFERENCE FROM TASK A]

## D) Allow a user to view all reviews for a given POI
A user should be able to select a place of interest from their search results; this will display all reviews for that place of interest. If you are not intending to complete task e), you may test this by adding reviews to the database via phpMyAdmin.

## E) Allow a user to review a POI
This must add an appropriate record to the reviews table. Ignore the approved column for now.
To achieve a Grade C, you must, in addition, ensure that you guard against SQL injection and cross-site scripting, must have no broken links, and implement a basic CSS stylesheet including custom layout and colour scheme (see below).

Please note that you should use standards-compliant HTML and CSS for this assignment, and ensure that you include basic custom CSS (a basic custom layout and custom colour scheme). Your CSS must be written as an external stylesheet. You must do this to achieve a C or above. Also to achieve a C or above, there must be no broken links.

Your site must also be user-friendly (easy to navigate and use to the end-user). One example of maximising user-friendliness would be using drop-down lists rather than form fields where appropriate, and another would be to not require users to enter IDs or other quantities that might be unknown to the end-user. Navigation around the site should also be intuitive.. You must take these steps to achieve a B or above.
For a Grade B, it is necessary in addition to…

## F) Allow administrator to approve reviews
As well as approving a review, an administrator must be able to see a list of all pending reviews.
To achieve a Grade B, you must, in addition, ensure that your site is user-friendly (see below).

## G) Implement the majority of your scripts using object-oriented PHP
You should include some use of Data Access Objects (DAOs) in your code. It is not necessary to use Slim to complete this task.
Task h) - must be implemented in full for an A2. For an A3, it must be mostly functional but there may be a small number of omissions.

## H) Implement the search and review functionality using Slim and AJAX.
The "search for a POI by region" functionality must be implemented as a Slim endpoint, and you must include an AJAX front end which reads the user's search term, sends it to your Slim endpoint, and displays the search results to the user in a user-friendly, readable, well-formatted way.
The "review" functionality must also be implemented as a Slim endpoint.
It should receive the POI ID and the review as POST data. It should check that the ID and the review are valid.

Implement an AJAX review facility as follows. Each search result from your AJAX search - task h) - should include a text box (to allow the user to enter a review) and a "Review" button. When the user clicks the Review button, an AJAX POST request should be sent to your Slim "review" endpoint. When the review has been added, a confirmation message must be displayed to the user, or an error message if the ID and/or review were not valid.
[EXPLAIN REFERENCE FROM TASK A]

## I) Implement your search facility as a JSON web service and alter your AJAX front end to connect to this JSON web service
Search results must continue to be displayed in a user-friendly, readable, well-formatted way.
