# api-template

This is just a basic template for building a simple PHP REST API that leverages stored procedures and an API Schema table to allow your database to manage all possible API endpoints. No PHP code should ever really need to be written to add or modify API endpoints. Just create a new stored procedure and add it to the API schema table with a given endpoint name. That endpoint will then automatically be available to the API.

Security is also accounted for using Public and Private keys to generate auth tokens. So every endpoint will require an auth token to be generated before the endpoint can be reached.
