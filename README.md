# api-template

This is a basic template for building a simple PHP REST API that leverages stored procedures and their schema to allow a MySQL database to manage all possible API endpoints. No PHP code should ever really need to be written or modified to add or modify API endpoints. Just start creating new stored procedures and they will automatically become available as endpoints for your API.

Security is also accounted for using Public and Private keys to generate auth tokens. So every endpoint will require an auth token to be generated before the endpoint can be reached.

I recently added a docs.php file that will auto-generate a detailed documenation page based on the stored procedures and their necessary parameters.

NOTE: The auto-generate documenation page leverages the MySQL COMMENT option for stored procedures. In order to provide a description and an example JSON response this option should be used as described here:

COMMENT = '< API Description > ~ < Example JSON Response >'

Example:
COMMENT 'This method can be used to test the current status of the API ~ {"result":true,"msg":"success","data":[{"error":"0","message":"API Works"}]}'

More examples of how the COMMENT option is used exist in the API_Database_Setup.sql file.
