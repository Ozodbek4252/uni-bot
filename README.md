## API Request Parameters

The following parameters can be used for making requests to the API:

| **Key** | **Value**     | **Description**                        |
| ------- | ------------- | -------------------------------------- |
| project | jom-marble    | required, like: jom-marble, vodiy, ... |
| phone   | +998944424252 | required                               |
| name    | Ozodbek       | optional                               |
| message | Hello there.  | optional                               |

### Example

Here is an example of a GET request with these parameters:

GET http://localhost:9005/api/request-to-bot?project=jom-marble&phone=+998944424252&name=Ozodbek&message=Hello there.

### Parameters Description

-   **project**: The project identifier (required). Examples: jom-marble, vodiy.
-   **phone**: The phone number to be included in the request (required).
-   **name**: The name of the person (optional).
-   **message**: A message to be sent (optional).
